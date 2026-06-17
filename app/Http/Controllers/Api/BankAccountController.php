<?php

namespace App\Http\Controllers\Api;

use App\Domains\Bank\Actions\ManageBankAccountAction;
use App\Domains\Bank\Models\Bank;
use App\Domains\Bank\Models\BankAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Bank\StoreBankAccountRequest;
use App\Http\Requests\Bank\VerifyBankAccountRequest;
use App\Http\Responses\ApiResponse;
use App\Services\FlutterwaveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function index(Request $request)
    {
        return ApiResponse::success(['bank_accounts' => $request->user()->bankAccounts()->with('bank')->get()]);
    }

    public function bankList()
    {
        return ApiResponse::success(['banks' => Bank::all()]);
    }

    public function store(StoreBankAccountRequest $request, ManageBankAccountAction $action)
    {
        // Check if account number has been saved
        if ($request->user()->bankAccounts()->where('bank_id', $request->bank_id)->where('account_number', $request->account_number)->exists()) {
            return ApiResponse::error('Bank account already exists', 400);
        }

        $account = $action->addAccount($request->user(), $request->validated());

        return ApiResponse::success(['message' => 'Bank account added successfully', 'data' => $account->load('bank')], 201);
    }

    public function destroy(Request $request, BankAccount $bankAccount, ManageBankAccountAction $action)
    {
        $action->deleteAccount($request->user(), $bankAccount);

        return ApiResponse::success(['message' => 'Bank account deleted successfully']);
    }

    public function verify(VerifyBankAccountRequest $request, FlutterwaveService $flutterwaveService): JsonResponse
    {
        try {
            $bank = Bank::findOrFail($request->bank_id);

            if (! $bank->code) {
                return ApiResponse::error('Bank code not found for the selected bank.', 400);
            }

            $data = $flutterwaveService->resolveAccountNumber($request->account_number, $bank->code);

            return ApiResponse::success([
                'account_name' => $data['account_name'],
                'account_number' => $data['account_number'],
                'bank_id' => $bank->id,
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }
}
