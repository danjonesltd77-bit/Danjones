<x-mail::message>
    # Trade Completed Successfully

    Hello {{ $trade->buyer->name }},

    The seller **{{ $trade->seller->name }}** has released the assets for trade **#{{ $trade->id }}**.

    **Transaction Summary:**
    - **Amount Received:** {{ number_format($trade->crypto_amount, 8) }} {{ $trade->currency->code }}
    - **Currency:** {{ $trade->currency->name }}

    The assets are now available in your wallet.

    Thanks for trading with us,<br>
    {{ config('app.name') }}
</x-mail::message>
