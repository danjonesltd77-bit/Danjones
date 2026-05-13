<x-mail::message>
    # Payment Confirmed by Buyer

    Hello {{ $trade->seller->name }},

    The buyer **{{ $trade->buyer->name }}** has marked trade **#{{ $trade->id }}** as paid.

    **Trade Summary:**
    - **Amount:** {{ number_format($trade->crypto_amount, 8) }} {{ $trade->currency->code }}
    - **Fiat to Receive:** {{ number_format($trade->fiat_amount, 2) }}
    {{ $trade->advertisement->fiatCurrency->code ?? 'NGN' }}

    Please verify your bank account for the payment. Once confirmed, please release the crypto to the buyer.

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
