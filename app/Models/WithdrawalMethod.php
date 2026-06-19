<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalMethod extends Model
{
    use HasFactory;

    public const TYPES = ['bank_transfer', 'paypal', 'mobile_wallet'];

    protected $fillable = [
        'user_id',
        'type',
        'is_default',
        'label',
        'bank_name',
        'account_holder_name',
        'account_number',
        'iban',
        'swift_code',
        'paypal_email',
        'wallet_provider',
        'wallet_mobile_number',
        'wallet_holder_name',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Owning user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Localised, human-friendly type name.
     */
    public function getTypeName(?string $locale = null): string
    {
        $isAr = ($locale ?? app()->getLocale()) === 'ar';

        return match ($this->type) {
            'bank_transfer' => $isAr ? 'تحويل بنكي' : 'Bank Transfer',
            'paypal'        => 'PayPal',
            'mobile_wallet' => $isAr ? 'محفظة إلكترونية' : 'Mobile Wallet',
            default         => $this->type,
        };
    }

    /**
     * Short summary of the account details for display in lists/dropdowns.
     */
    public function getSummary(): string
    {
        return match ($this->type) {
            'bank_transfer' => trim(($this->bank_name ? $this->bank_name . ' — ' : '') . ($this->iban ?? $this->account_number ?? '')),
            'paypal'        => (string) $this->paypal_email,
            'mobile_wallet' => trim(($this->wallet_provider ? $this->wallet_provider . ' — ' : '') . ($this->wallet_mobile_number ?? '')),
            default         => '',
        };
    }

    /**
     * Label to show in a picker: custom label, else type + summary.
     */
    public function getDisplayLabel(): string
    {
        if (!empty($this->label)) {
            return $this->label . ' (' . $this->getTypeName() . ')';
        }

        $summary = $this->getSummary();

        return $this->getTypeName() . ($summary ? ' — ' . $summary : '');
    }
}
