<?php

namespace App\Http\Controllers\Api;

use App\Domains\Wallet\Actions\ProcessIncomingWebhookAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TatumSubscriptionController extends Controller
{
    public function recieveSubscriptionInfo(Request $request, ProcessIncomingWebhookAction $processWebhookAction)
    {
        $result = $processWebhookAction->execute($request->all());

        if (! $result['success']) {
            return response()->json($result, 400);
        }

        return response()->json($result, 200);
    }
}
