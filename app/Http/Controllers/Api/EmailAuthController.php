<?php
// app/Http/Controllers/Api/EmailAuthController.php
// Handles: forgot password, reset password, email verification — all as JSON API

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class EmailAuthController extends BaseController
{
    // ── POST /api/v1/forgot-password ─────────────────────────────────
    // Sends a password reset link to the user's email
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Password reset link sent to your email.'
            ]);
        }

        return response()->json([
            'message' => 'Unable to send reset link. Please check the email address.',
            'errors'  => ['email' => [__($status)]]
        ], 422);
    }

    // ── POST /api/v1/reset-password ──────────────────────────────────
    // Resets the password using the token from the email link
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => ['required'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'confirmed', Rules\Password::min(8)],
            'password_confirmation' => ['required'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            // Log the user in and return a fresh token
            $user  = User::where('email', $request->email)->first();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Password reset successfully.',
                'user'    => $user,
                'token'   => $token,
            ]);
        }

        return response()->json([
            'message' => 'Invalid or expired reset token.',
            'errors'  => ['token' => [__($status)]]
        ], 422);
    }

    // ── POST /api/v1/email/verification-notification ─────────────────
    // Resends the verification email (requires auth)
    public function resendVerification(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email is already verified.'
            ]);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification link sent to your email.'
        ]);
    }

    // ── GET /api/v1/verify-email/{id}/{hash} ─────────────────────────
    // Called when user clicks the link in the verification email.
    // Redirects to frontend with result.
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');

        // Validate the hash
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect($frontendUrl . '/verify-email?error=invalid_link');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect($frontendUrl . '/?verified=already');
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return redirect($frontendUrl . '/?verified=1');
    }
}
