<?php

namespace App\Domains\User\Services;

use App\Domains\User\Notifications\GeneralNotification;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Send a general notification to a user or a collection of users.
     */
    public function send(User|Collection|array $recipients, string $title, string $message, string $type, array $metadata = []): void
    {
        $notification = new GeneralNotification($title, $message, $type, $metadata);

        if ($recipients instanceof User) {
            $recipients->notify($notification);

            return;
        }

        if (is_array($recipients)) {
            $recipients = collect($recipients);
        }

        if ($recipients instanceof Collection) {
            $recipients->each(fn (User $user) => $user->notify($notification));
        }
    }
}
