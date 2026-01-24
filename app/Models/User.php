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
        'phone',
        'phone_code',
        'company_name',
        'country',
        'user_type',
        'main_activity',
        'sub_activity',
        'withdrawal_method',
        'paypal_email',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'bank_iban',
        'bank_swift_code',
        'wallet_provider',
        'wallet_phone_number',
        'wallet_holder_name',
        'marketing_code',
        'marketing_code_approved',
        'avatar',
        'logo',
        'otp_code',
        'otp_expires_at',
        'is_verified',
        'verified_at',
        'email_verified_at',
        'is_blocked',
        'block_reason',
        // Distributor specific fields
        'store_name',
        'store_slug',
        'default_currency',
        'commercial_register',
        'freelance_document',
        'social_media_accounts',
        // Seller setup fields
        'setup_completed_at',
        'profit_settings_completed',
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
            'setup_completed_at' => 'datetime',
            'profit_settings_completed' => 'boolean',
            'is_blocked' => 'boolean',
            'social_media_accounts' => 'array',
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
            ->withPivot('aliexpress_product_id', 'status', 'is_choice', 'seller_amount', 'admin_amount', 'price')
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

    /**
     * Check if seller is in trial period (has active trial subscription)
     */
    public function isInTrialPeriod(): bool
    {
        if ($this->user_type !== 'seller') {
            return false;
        }

        // Check for active trial subscription
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('payment_method', 'trial')
            ->where('end_date', '>=', now()->toDateString())
            ->exists();
    }

    /**
     * Check if seller trial has expired
     */
    public function hasTrialExpired(): bool
    {
        if ($this->user_type !== 'seller') {
            return false;
        }

        // Check if there was a trial and it has expired
        $hadTrial = $this->subscriptions()
            ->where('payment_method', 'trial')
            ->exists();

        if (!$hadTrial) {
            return false;
        }

        return !$this->isInTrialPeriod() && !$this->hasActiveSubscription();
    }

    /**
     * Check if seller has completed all required profile fields
     * Required: phone, commercial_register, logo, store_name, store_slug, default_currency, withdrawal_method
     */
    public function hasCompletedSellerProfile(): bool
    {
        if ($this->user_type !== 'seller') {
            return true;
        }

        return !empty($this->phone)
            && !empty($this->commercial_register)
            && !empty($this->logo)
            && !empty($this->store_name)
            && !empty($this->store_slug)
            && !empty($this->default_currency)
            && !empty($this->withdrawal_method);
    }

    /**
     * Check if seller has completed payment method setup (backward compatibility)
     */
    public function hasPaymentMethodSetup(): bool
    {
        return $this->hasCompletedSellerProfile();
    }

    /**
     * Check if seller has completed initial setup (profile + profit settings)
     */
    public function hasCompletedSetup(): bool
    {
        return $this->hasCompletedSellerProfile() && $this->profit_settings_completed;
    }

    /**
     * Get missing required profile fields for seller
     */
    public function getMissingProfileFields(): array
    {
        if ($this->user_type !== 'seller') {
            return [];
        }

        $missing = [];

        if (empty($this->phone)) {
            $missing[] = 'phone';
        }
        if (empty($this->commercial_register)) {
            $missing[] = 'commercial_register';
        }
        if (empty($this->logo)) {
            $missing[] = 'logo';
        }
        if (empty($this->store_name)) {
            $missing[] = 'store_name';
        }
        if (empty($this->store_slug)) {
            $missing[] = 'store_slug';
        }
        if (empty($this->default_currency)) {
            $missing[] = 'default_currency';
        }
        if (empty($this->withdrawal_method)) {
            $missing[] = 'withdrawal_method';
        }

        return $missing;
    }

    /**
     * Check if seller can access full system
     */
    public function canAccessFullSystem(): bool
    {
        if ($this->user_type !== 'seller') {
            return true;
        }

        // Must have completed setup first
        if (!$this->hasCompletedSetup()) {
            return false;
        }

        // Either in trial period or has active subscription
        return $this->isInTrialPeriod() || $this->hasActiveSubscription();
    }

    /**
     * Get trial remaining time in hours
     */
    public function getTrialRemainingHours(): int
    {
        if (!$this->isInTrialPeriod()) {
            return 0;
        }

        $trialSubscription = $this->subscriptions()
            ->where('status', 'active')
            ->where('payment_method', 'trial')
            ->where('end_date', '>=', now()->toDateString())
            ->first();

        if (!$trialSubscription) {
            return 0;
        }

        return (int) now()->diffInHours($trialSubscription->end_date->endOfDay(), false);
    }

    /**
     * Get coupons for this distributor/store
     */
    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class, 'store_id');
    }

    /**
     * Get user's orders (as customer)
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
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
