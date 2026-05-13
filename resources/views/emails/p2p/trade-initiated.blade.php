<x-mail::message>
    # New Trade Request

    Hello {{ $trade->seller->name }},

    You have received a new trade request from **{{ $trade->buyer->name }}**.

    **Trade Details:**
    - **Amount:** {{ number_format($trade->crypto_amount, 8) }} {{ $trade->currency->code }}
    - **Fiat Amount:** {{ number_format($trade->fiat_amount, 2) }}
    {{ $trade->advertisement->fiatCurrency->code ?? 'NGN' }}
    - **Price:** {{ number_format($trade->fiat_amount / $trade->crypto_amount, 2) }} / {{ $trade->currency->code }}

    Please log in to your dashboard to confirm the payment and release the assets.


    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
