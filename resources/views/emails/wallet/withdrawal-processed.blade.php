<x-mail::message>
    # Withdrawal Successful

    Hello {{ $transaction->user->name }},

    Your withdrawal of **{{ number_format($transaction->amount, 8) }} {{ $transaction->currency->code }}** has been
    successfully processed and sent to the blockchain.

    **Transaction Summary:**
    - **Amount:** {{ number_format($transaction->amount, 8) }} {{ $transaction->currency->code }}
    - **Destination:** {{ $transaction->metadata['address'] ?? 'N/A' }}
    - **TXID:** {{ $transaction->metadata['txid'] ?? $transaction->reference }}

    You can track the status of this transaction on the blockchain explorer.

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
