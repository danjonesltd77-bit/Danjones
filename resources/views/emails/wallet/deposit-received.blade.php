<x-mail::message>
    # Deposit Confirmed

    Hello {{ $transaction->user->name }},

    Your deposit has been successfully confirmed and credited to your wallet.

    **Transaction Details:**
    - **Amount:** {{ number_format($transaction->amount, 8) }} {{ $transaction->currency->code }}
    - **Reference:** {{ $transaction->reference }}
    - **Date:** {{ $transaction->created_at->toDayDateTimeString() }}

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
