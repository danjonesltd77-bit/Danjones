<x-layouts::app>
    <div class="row">
        <!-- Total Platform Balance USD Card -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card bg-white p-20 py-30 rounded-10 border border-white position-relative z-1 h-100">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <h3 class="mb-10 lh-1 fs-14 text-body">Platform Balance (USD)</h3>
                        <h2 class="fs-22 fw-bold mb-10 lh-1">$ {{ number_format($totalBalanceUsd, 2) }}</h2>
                        <p class="mb-0 fs-13 text-secondary text-truncate">Total value across all crypto & NGN wallets
                        </p>
                    </div>
                    <div class="flex-shrink-0 ms-2">
                        <div class="bg-primary-transparent wh-50 rounded-circle text-center" style="line-height: 50px;">
                            <i class="material-symbols-outlined text-primary fs-24">account_balance_wallet</i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Platform Balance NGN Card -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card bg-white p-20 py-30 rounded-10 border border-white position-relative z-1 h-100">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <h3 class="mb-10 lh-1 fs-14 text-body">Platform Balance (NGN)</h3>
                        <h2 class="fs-22 fw-bold mb-10 lh-1">₦ {{ number_format($totalBalanceNgn, 2) }}</h2>
                        <p class="mb-0 fs-13 text-secondary">Global Rate: 1 USD = ₦{{ number_format($usdNgnRate, 2) }}
                        </p>
                    </div>
                    <div class="flex-shrink-0 ms-2">
                        <div class="bg-success-transparent wh-50 rounded-circle text-center" style="line-height: 50px;">
                            <i class="material-symbols-outlined text-success fs-24">payments</i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Active Users Card -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card bg-white p-20 py-30 rounded-10 border border-white position-relative z-1 h-100">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <h3 class="mb-10 lh-1 fs-14 text-body">Total Platform Users</h3>
                        <h2 class="fs-22 fw-bold mb-10 lh-1">{{ number_format($totalUsers) }} Active</h2>
                        <p class="mb-0 fs-13 text-secondary">Total registered & verified profiles</p>
                    </div>
                    <div class="flex-shrink-0 ms-2">
                        <div class="bg-info-transparent wh-50 rounded-circle text-center" style="line-height: 50px;">
                            <i class="material-symbols-outlined text-info fs-24">group</i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Total Wallets Card -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card bg-white p-20 py-30 rounded-10 border border-white position-relative z-1 h-100">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <h3 class="mb-10 lh-1 fs-14 text-body">System Total Wallets</h3>
                        <h2 class="fs-22 fw-bold mb-10 lh-1">{{ number_format($totalWalletsCount) }} Created</h2>
                        <p class="mb-0 fs-13 text-secondary">Across both crypto & fiat assets</p>
                    </div>
                    <div class="flex-shrink-0 ms-2">
                        <div class="bg-warning-transparent wh-50 rounded-circle text-center" style="line-height: 50px;">
                            <i class="material-symbols-outlined text-warning fs-24">currency_exchange</i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest Platform-Wide Transactions -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card bg-white rounded-10 border border-white mb-4">
                <div
                    class="d-flex justify-content-between align-items-center flex-wrap gap-3 p-20 border-bottom border-color-50">
                    <h3 class="mb-0">Global Transaction History</h3>
                </div>
                <div class="default-table-area table-latest-transaction">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th scope="col" class="fw-normal text-body-color-40 fs-14">REF #</th>
                                    <th scope="col" class="fw-normal text-body-color-40 fs-14">USER</th>
                                    <th scope="col" class="fw-normal text-body-color-40 fs-14">TYPE/ACTION</th>
                                    <th scope="col" class="fw-normal text-body-color-40 fs-14">AMOUNT</th>
                                    <th scope="col" class="fw-normal text-body-color-40 fs-14">DATE</th>
                                    <th scope="col" class="fw-normal text-body-color-40 fs-14 text-center">STATUS
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $transaction)
                                    <tr>
                                        <td class="text-secondary">#{{ substr($transaction->id, 0, 8) }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <img src="{{ asset('assets/images/user1.jpg') }}"
                                                        class="wh-34 rounded-circle" alt="user">
                                                </div>
                                                <div class="flex-grow-1 ms-10">
                                                    <p class="fs-14 fw-medium text-secondary mb-0">
                                                        {{ $transaction->wallet->user->name ?? 'System' }}</p>
                                                    <span
                                                        class="text-body-color-40 fs-12">{{ $transaction->wallet->user->email ?? '' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div
                                                        class="wh-34 rounded-circle text-center line-height-34 bg-{{ $transaction->action == 'deposit' ? 'success' : 'danger' }}-transparent">
                                                        <i
                                                            class="material-symbols-outlined fs-18 text-{{ $transaction->action == 'deposit' ? 'success' : 'danger' }}">{{ $transaction->action == 'deposit' ? 'add' : 'remove' }}</i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-10">
                                                    <p class="fs-14 fw-medium text-secondary mb-0 text-capitalize">
                                                        {{ $transaction->action }}</p>
                                                    <span
                                                        class="text-body-color-40 fs-12">{{ $transaction->wallet->currency->name ?? 'Asset' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-secondary fw-semibold fs-15">
                                            {{ $transaction->action == 'deposit' ? '+' : '-' }}
                                            {{ number_format($transaction->amount, 8) }}
                                        </td>
                                        <td class="text-secondary fs-14">
                                            {{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                        <td class="text-center">
                                            <span
                                                class="text-success bg-success bg-opacity-10 fs-13 fw-normal d-inline-block default-badge border border-success">
                                                Completed
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-secondary">No recent platform
                                            activity found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
