<?php

namespace App\Domains\Bank\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccount extends Model
{
    /** @use HasFactory<\Database\Factories\Domains\Bank\Models\BankAccountFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bank_id',
        'account_name',
        'account_number',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }
}
