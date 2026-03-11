<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Domains\Wallet\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'pin',
        'otp',
        'two_factor_secret',
        'two_factor_recovery_codes',
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
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',

        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the user's wallets.
     */
    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }

    /**
     * Get the user's NGN wallet.
     */
    public function nairaWallet()
    {
        return $this->hasOne(Wallet::class)->where('currency_id', 1);
    }

    /**
     * Get the user's BTC wallet.
     */
    public function btcWallet()
    {
        return $this->hasOne(Wallet::class)->where('currency_id', 2);
    }

    /**
     * Get a wallet for a particular currency (ID or symbol).
     */
    public function wallet(int|string $currency): ?Wallet
    {
        return $this->wallets()
            ->when(is_int($currency), fn($q) => $q->where('currency_id', $currency))
            ->when(is_string($currency), fn($q) => $q->whereHas('currency', fn($c) => $c->where('symbol', $currency)))
            ->first();
    }
}
