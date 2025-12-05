<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Meter;
use App\Services\BillingEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    protected BillingEngine $billingEngine;

    public function __construct(BillingEngine $billingEngine)
    {
        $this->billingEngine = $billingEngine;
    }

    /**
     * Get projected bill for an account.
     *
     * @param Account $account
     * @return JsonResponse
     */
    public function getProjectedBill(Account $account): JsonResponse
    {
        $projection = $this->billingEngine->getProjectedMonthlyBill($account);

        return response()->json([
            'success' => true,
            'data' => $projection,
        ]);
    }

    /**
     * Get daily consumption for a meter.
     *
     * @param Meter $meter
     * @return JsonResponse
     */
    public function getDailyConsumption(Meter $meter): JsonResponse
    {
        $dailyConsumption = $this->billingEngine->getDailyConsumption($meter);

        return response()->json([
            'success' => true,
            'data' => [
                'meter_id' => $meter->id,
                'meter_title' => $meter->meter_title,
                'daily_consumption' => $dailyConsumption,
            ],
        ]);
    }

    /**
     * Get billing history for an account.
     *
     * @param Request $request
     * @param Account $account
     * @return JsonResponse
     */
    public function getBillingHistory(Request $request, Account $account): JsonResponse
    {
        $limit = $request->input('limit', 12);
        $history = $this->billingEngine->getBillingHistory($account, $limit);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Get the tariff template for an account.
     *
     * @param Account $account
     * @return JsonResponse
     */
    public function getAccountTariff(Account $account): JsonResponse
    {
        $tariff = $account->tariffTemplate;

        if (!$tariff) {
            return response()->json([
                'success' => false,
                'message' => 'No tariff template assigned to this account.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $tariff->id,
                'template_name' => $tariff->template_name,
                'billing_type' => $tariff->billing_type ?? 'MONTHLY',
                'vat_rate' => $tariff->getVatRate(),
                'billing_day' => $tariff->billing_day,
                'read_day' => $tariff->read_day,
                'is_active' => $tariff->is_active,
                'effective_from' => $tariff->effective_from,
                'effective_to' => $tariff->effective_to,
                'region' => $tariff->region ? [
                    'id' => $tariff->region->id,
                    'region_name' => $tariff->region->region_name,
                ] : null,
            ],
        ]);
    }

    /**
     * Get tariff tiers for an account's tariff template.
     *
     * @param Account $account
     * @return JsonResponse
     */
    public function getTariffTiers(Account $account): JsonResponse
    {
        $tariff = $account->tariffTemplate;

        if (!$tariff) {
            return response()->json([
                'success' => false,
                'message' => 'No tariff template assigned to this account.',
            ], 404);
        }

        $tiers = $tariff->tiers()->orderBy('tier_number')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'tariff_id' => $tariff->id,
                'tariff_name' => $tariff->template_name,
                'tiers' => $tiers->map(function ($tier) {
                    return [
                        'tier_number' => $tier->tier_number,
                        'min_units' => (float) $tier->min_units,
                        'max_units' => $tier->max_units ? (float) $tier->max_units : null,
                        'rate_per_unit' => (float) $tier->rate_per_unit,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Get read date and billing date for an account.
     *
     * @param Account $account
     * @return JsonResponse
     */
    public function getBillingDates(Account $account): JsonResponse
    {
        $readDate = $this->billingEngine->getReadDate($account);
        $billingDate = $this->billingEngine->getBillingDate($account);

        return response()->json([
            'success' => true,
            'data' => [
                'read_date' => $readDate->toDateString(),
                'billing_date' => $billingDate->toDateString(),
                'days_until_reading' => now()->diffInDays($readDate, false),
                'days_until_billing' => now()->diffInDays($billingDate, false),
            ],
        ]);
    }

    /**
     * Calculate a bill for given readings.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function calculateBill(Request $request): JsonResponse
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'opening_reading_id' => 'required|exists:meter_readings,id',
            'closing_reading_id' => 'required|exists:meter_readings,id',
        ]);

        $account = Account::findOrFail($request->account_id);
        $openingReading = \App\Models\MeterReadings::findOrFail($request->opening_reading_id);
        $closingReading = \App\Models\MeterReadings::findOrFail($request->closing_reading_id);

        $result = $this->billingEngine->calculateCharge($account, $openingReading, $closingReading);

        return response()->json([
            'success' => true,
            'data' => $result->toArray(),
        ]);
    }
}
