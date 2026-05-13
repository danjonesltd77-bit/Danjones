<x-mail::message>
    # Withdrawal Request

    Hello {{ $transaction->user->name }},

    We have received your request to withdraw **{{ number_format($transaction->amount, 8) }}
    {{ $transaction->currency->code }}**.

    **Request Details:**
    - **Amount:** {{ number_format($transaction->amount, 8) }} {{ $transaction->currency->code }}
    - **Destination:** {{ $transaction->metadata['address'] ?? 'N/A' }}
    - **Status:** Pending Review

    Your request is currently being processed. You will receive another email once the transaction is completed.

    If you did not authorize this request, please contact support immediately.

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
