<x-mail::message>
# Password Changed Successfully

Hello {{ $user->name }},

This is a security notification to inform you that your account password has been successfully changed.

**If you performed this action:**
You can safely ignore this email.

**If you did NOT perform this action:**
Please contact our support team immediately and secure your account by resetting your password.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
