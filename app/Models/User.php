<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
        'address', 'city', 'phone',
        'user_photo', 'role', 'status', 'post_code',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    public function products()    { return $this->hasMany(Product::class, 'owner_id'); }
    public function rentals()     { return $this->hasMany(Rental::class, 'renter_id'); }
    public function reviews()     { return $this->hasMany(Review::class); }
    public function orders()      { return $this->hasMany(Order::class); }
    public function cartItems()   { return $this->hasMany(CartItem::class); }
    public function payments()    { return $this->hasMany(Payment::class); }
    public function logs()        { return $this->hasMany(Log::class); }
    public function recommendations() { return $this->hasMany(AiRecommendation::class); }

    public function wishlist()
    {
        return $this->belongsToMany(Product::class, 'wishlist', 'user_id', 'product_id')
                    ->withTimestamps();
    }

    public function getUserPhotoAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }

    public function isAdmin()  { return $this->role === 'admin'; }
    public function isSeller() { return $this->role === 'seller'; }
    public function isActive() { return $this->status === 'active'; }

    public function scopeActive($query)        { return $query->where('status', 'active'); }
    public function scopeSellers($query)       { return $query->where('role', 'seller'); }
    public function scopeInCity($query, $city) { return $query->where('city', $city); }
}
