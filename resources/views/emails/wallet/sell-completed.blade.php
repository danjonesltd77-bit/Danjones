<x-mail::message>
# Sell Order Completed

Hello {{ $user->name }},

Your request to sell cryptocurrency for Naira has been successfully processed.

**Order Summary:**
- **Amount Sold:** {{ number_format($details['amount_sold'], 8) }} {{ $details['currency_code'] }}
- **Naira Received:** ₦{{ number_format($details['naira_received'], 2) }}
- **Service Fee:** {{ number_format($details['fee_amount'], 8) }} {{ $details['currency_code'] }}

The Naira has been credited to your local wallet and is available for withdrawal to your bank account.


Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
