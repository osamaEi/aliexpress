<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'full_name',
        'company_name',
        'country',
        'user_type',
        'main_activity',
        'sub_activity',
        'avatar',
        'otp_code',
        'otp_expires_at',
        'is_verified',
        'verified_at',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'otp_expires_at' => 'datetime',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Products assigned to this user/seller
     */
    public function assignedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_user')
            ->withPivot('aliexpress_product_id', 'status')
            ->withTimestamps();
    }

    /**
     * Get all user subscriptions
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Get user's wallet
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Get or create user's wallet
     */
    public function getOrCreateWallet(): Wallet
    {
        return $this->wallet()->firstOrCreate(
            ['user_id' => $this->id],
            [
                'balance' => 0.00,
                'pending_balance' => 0.00,
                'currency' => 'AED',
                'is_active' => true,
            ]
        );
    }

    /**
     * Get current active subscription
     */
    public function activeSubscription(): HasOne
    {
        return $this->hasOne(UserSubscription::class)
            ->where('status', 'active')
            ->where('end_date', '>=', now()->toDateString())
            ->latest();
    }

    /**
     * Check if user has active subscription
     */
    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription()->exists();
    }

    /**
     * Get user's current subscription plan
     */
    public function getCurrentSubscription()
    {
        return $this->activeSubscription;
    }

    /**
     * Get seller's subcategory profit settings
     */
    public function subcategoryProfits(): HasMany
    {
        return $this->hasMany(SellerSubcategoryProfit::class);
    }

    /**
     * Get profit setting for a specific subcategory
     */
    public function getProfitForSubcategory($categoryId)
    {
        return $this->subcategoryProfits()
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->first();
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            return $this->roles()->where('slug', $roles)->exists();
        }

        return $this->roles()->whereIn('slug', $roles)->exists();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles()->whereHas('permissions', function ($query) use ($permission) {
            $query->where('slug', $permission);
        })->exists();
    }

    public function assignRole(string|Role $role): void
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }

        $this->roles()->syncWithoutDetaching($role);
    }

    public function removeRole(string|Role $role): void
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }

        $this->roles()->detach($role);
    }
}
