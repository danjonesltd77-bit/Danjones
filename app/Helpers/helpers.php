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

if (!function_exists('toast')) {
    /**
     * Trigger a global toast notification.
     *
     * @param string $message
     * @param string $type
     * @return void
     */
    function toast(string $message, string $type = 'success'): void
    {
        if (app()->bound('livewire') && app('livewire')->current()) {
            // During a Livewire request, dispatch through the current component
            app('livewire')->current()->dispatch('toast', 
                message: $message,
                type: $type,
            );
        }
        
        session()->flash('toast', [
            'message' => $message,
            'type' => $type,
        ]);
    }
}
