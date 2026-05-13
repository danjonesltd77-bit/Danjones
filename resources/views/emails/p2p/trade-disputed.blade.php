<x-mail::message>
    # Trade Dispute Raised

    Hello {{ $recipient->name }},

    A dispute has been raised for trade **#{{ $trade->id }}** by **{{ $disputer->name }}**.

    **Trade Details:**
    - **Amount:** {{ number_format($trade->crypto_amount, 8) }} {{ $trade->currency->code }}
    - **Reason:** {{ $trade->dispute_reason ?? 'No reason provided' }}

    Our support team has been notified and will review the case. Please provide any necessary evidence (payment proof,
    etc.) on the trade detail page.


    Thanks,<br>
    {{ config('app.name') }} Support Team
</x-mail::message>
