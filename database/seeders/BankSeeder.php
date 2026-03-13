<?php

namespace Database\Seeders;

use App\Domains\Bank\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banks = [
            ['name' => 'Access Bank', 'code' => '044'],
            ['name' => 'Citibank', 'code' => '023'],
            ['name' => 'Ecobank Nigeria', 'code' => '050'],
            ['name' => 'Fidelity Bank Nigeria', 'code' => '070'],
            ['name' => 'First Bank of Nigeria', 'code' => '011'],
            ['name' => 'First City Monument Bank', 'code' => '214'],
            ['name' => 'Guaranty Trust Bank', 'code' => '058'],
            ['name' => 'Heritage Bank Plc', 'code' => '030'],
            ['name' => 'Keystone Bank Limited', 'code' => '082'],
            ['name' => 'Kuda Bank', 'code' => '090267'],
            ['name' => 'Moniepoint MFB', 'code' => '50515'],
            ['name' => 'OPay (PayCom)', 'code' => '100004'],
            ['name' => 'Palmpay', 'code' => '100033'],
            ['name' => 'Polaris Bank', 'code' => '076'],
            ['name' => 'Providus Bank Limited', 'code' => '101'],
            ['name' => 'Stanbic IBTC Bank Nigeria Limited', 'code' => '221'],
            ['name' => 'Standard Chartered Bank', 'code' => '068'],
            ['name' => 'Sterling Bank', 'code' => '033'],
            ['name' => 'Suntrust Bank Nigeria Limited', 'code' => '100'],
            ['name' => 'Union Bank of Nigeria', 'code' => '032'],
            ['name' => 'United Bank for Africa', 'code' => '033'],
            ['name' => 'Unity Bank Plc', 'code' => '215'],
            ['name' => 'Wema Bank', 'code' => '035'],
            ['name' => 'Zenith Bank', 'code' => '057'],
        ];

        foreach ($banks as $bank) {
            Bank::updateOrCreate(['code' => $bank['code']], $bank);
        }
    }
}
