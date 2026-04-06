<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class MakeAdminCommand extends Command
{
    protected $signature   = 'user:role {email} {role=admin}';
    protected $description = 'Set a user\'s role (admin, seller, user)';

    public function handle()
    {
        $email = $this->argument('email');
        $role  = $this->argument('role');

        if (!in_array($role, ['admin', 'seller', 'user'])) {
            $this->error("Invalid role. Use: admin, seller, or user");
            return 1;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("No user found with email: {$email}");
            return 1;
        }

        $user->update(['role' => $role]);
        $this->info("✓ {$user->name} ({$email}) is now a {$role}");
        return 0;
    }
}
