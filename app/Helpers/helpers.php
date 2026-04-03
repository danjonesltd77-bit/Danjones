<?php

use Illuminate\Support\Number;

if (!function_exists('crypto_format')) {
    /**
     * Format a crypto or fiat amount with dynamic decimal precision and trailing zero removal.
     *
     * @param mixed $amount
     * @param int $decimals
     * @return string
     */
    function crypto_format($amount, int $decimals = 8): string
    {
        return Number::crypto($amount, $decimals);
    }
}
