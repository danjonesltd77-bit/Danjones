<?php

use Illuminate\Support\Number;

if (! function_exists('crypto_format')) {
    /**
     * Format a crypto or fiat amount with dynamic decimal precision and trailing zero removal.
     *
     * @param  mixed  $amount
     */
    function crypto_format($amount, int $decimals = 8): string
    {
        return Number::crypto($amount, $decimals);
    }
}

if (! function_exists('toast')) {
    /**
     * Trigger a global toast notification.
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

if (! function_exists('send_notification')) {
    /**
     * Send a general notification to a user or collection of users.
     *
     * @param  mixed  $recipients
     */
    function send_notification($recipients, string $title, string $message, string $type, array $metadata = []): void
    {
        app(\App\Domains\User\Services\NotificationService::class)->send($recipients, $title, $message, $type, $metadata);
    }
}
