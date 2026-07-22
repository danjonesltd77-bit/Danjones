<x-mail::message>
# {{ $status === 'approved' ? 'NIN Verification Successful' : 'NIN Verification Failed' }}

Hello {{ $user->name }},

@if($status === 'approved')
Great news! Your National Identity Number (NIN) verification was successful and your identity has been verified on {{ config('app.name') }}.
@else
Your NIN verification attempt was not successful.
@if($reason)

**Reason:** {{ $reason }}
@endif

You can review your details and attempt verification again from your account.
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
