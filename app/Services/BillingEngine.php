<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Adjustment;
use App\Models\Bill;
use App\Models\BillingCycle;
use App\Models\Meter;
use App\Models\MeterReadings;
use App\Models\RegionsAccountTypeCost;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * BillResult - A simple data class for billing calculation results.
 */
class BillResult
{
    public float $consumption;
    public float $tieredCharge;
    public float $fixedCostsTotal;
    public float $vatAmount;
    public float $totalAmount;
    public bool $isProvisional;
    public ?MeterReadings $openingReading;
    public ?MeterReadings $closingReading;
    public array $tierBreakdown;
    public array $fixedCostsBreakdown;

    public function __construct(
        float $consumption = 0,
        float $tieredCharge = 0,
        float $fixedCostsTotal = 0,
        float $vatAmount = 0,
        float $totalAmount = 0,
        bool $isProvisional = false,
        ?MeterReadings $openingReading = null,
        ?MeterReadings $closingReading = null,
        array $tierBreakdown = [],
        array $fixedCostsBreakdown = []
    ) {
        $this->consumption = $consumption;
        $this->tieredCharge = $tieredCharge;
        $this->fixedCostsTotal = $fixedCostsTotal;
        $this->vatAmount = $vatAmount;
        $this->totalAmount = $totalAmount;
        $this->isProvisional = $isProvisional;
        $this->openingReading = $openingReading;
        $this->closingReading = $closingReading;
        $this->tierBreakdown = $tierBreakdown;
        $this->fixedCostsBreakdown = $fixedCostsBreakdown;
    }

    public function toArray(): array
    {
        return [
            'consumption' => $this->consumption,
            'tiered_charge' => $this->tieredCharge,
            'fixed_costs_total' => $this->fixedCostsTotal,
            'vat_amount' => $this->vatAmount,
            'total_amount' => $this->totalAmount,
            'is_provisional' => $this->isProvisional,
            'opening_reading_id' => $this->openingReading?->id,
            'closing_reading_id' => $this->closingReading?->id,
            'tier_breakdown' => $this->tierBreakdown,
            'fixed_costs_breakdown' => $this->fixedCostsBreakdown,
        ];
    }
}

/**
 * BillingEngine - The unified billing calculation service for MyCities.
 * 
 * This service handles both Monthly and Date-to-Date billing calculations,
 * tiered rate calculations, estimations, and reconciliations.
 */
class BillingEngine
{
    /**
     * Default VAT rate (15%).
     */
    const DEFAULT_VAT_RATE = 15.0;

    /**
     * Default days before billing date for reading submission.
     */
    const DEFAULT_READ_DAYS_BEFORE = 5;

    /**
     * Default billing day of the month (20th).
     */
    const DEFAULT_BILLING_DAY = 20;

    /**
     * Conversion factor from liters to kiloliters.
     */
    const LITERS_TO_KILOLITERS = 1000;

    /**
     * Main entry point - routes to correct billing mode.
     *
     * @param Account $account
     * @param MeterReadings $from Opening reading
     * @param MeterReadings $to Closing reading
     * @return BillResult
     */
    public function calculateCharge(Account $account, MeterReadings $from, MeterReadings $to): BillResult
    {
        $tariff = $this->getTariffForAccount($account, Carbon::parse($to->reading_date));
        
        if (!$tariff) {
            return new BillResult();
        }

        if ($tariff->isDateToDateBilling()) {
            return $this->calculateDateToDate($tariff, $from, $to);
        }
        
        return $this->calculateMonthly($tariff, $account, $from, $to);
    }

    /**
     * Get the tariff template for an account valid for a specific date.
     *
     * @param Account $account
     * @param Carbon $date
     * @return RegionsAccountTypeCost|null
     */
    public function getTariffForAccount(Account $account, Carbon $date): ?RegionsAccountTypeCost
    {
        $tariff = $account->tariffTemplate;
        
        if (!$tariff) {
            return null;
        }

        // Check if the current tariff is effective for the given date
        if ($tariff->isEffectiveFor($date)) {
            return $tariff;
        }

        // If not, look for historical tariffs that were effective on that date
        return RegionsAccountTypeCost::where('region_id', $tariff->region_id)
            ->where('is_active', true)
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_from')
                      ->orWhere('effective_from', '<=', $date);
            })
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_to')
                      ->orWhere('effective_to', '>=', $date);
            })
            ->orderBy('effective_from', 'desc')
            ->first();
    }

    /**
     * Simple date-to-date calculation: (Reading2 - Reading1) × tiered rates.
     *
     * @param RegionsAccountTypeCost $tariff
     * @param MeterReadings $from
     * @param MeterReadings $to
     * @return BillResult
     */
    public function calculateDateToDate(
        RegionsAccountTypeCost $tariff, 
        MeterReadings $from, 
        MeterReadings $to
    ): BillResult {
        $consumption = $to->reading_value - $from->reading_value;
        
        if ($consumption < 0) {
            $consumption = 0;
        }

        $tieredResult = $this->applyTieredRates($tariff, $consumption);
        $fixedCostsResult = $this->calculateFixedCosts($tariff);
        
        $subtotal = $tieredResult['total'] + $fixedCostsResult['total'];
        $vatableAmount = $tieredResult['total'] + $fixedCostsResult['vatable_total'];
        $vatAmount = $this->calculateVat($vatableAmount, $tariff->getVatRate());
        $totalAmount = $subtotal + $vatAmount;

        return new BillResult(
            consumption: $consumption,
            tieredCharge: $tieredResult['total'],
            fixedCostsTotal: $fixedCostsResult['total'],
            vatAmount: $vatAmount,
            totalAmount: $totalAmount,
            isProvisional: false,
            openingReading: $from,
            closingReading: $to,
            tierBreakdown: $tieredResult['breakdown'],
            fixedCostsBreakdown: $fixedCostsResult['breakdown']
        );
    }

    /**
     * Complex monthly billing with estimation & reconciliation support.
     *
     * @param RegionsAccountTypeCost $tariff
     * @param Account $account
     * @param MeterReadings $from
     * @param MeterReadings $to
     * @return BillResult
     */
    public function calculateMonthly(
        RegionsAccountTypeCost $tariff, 
        Account $account, 
        MeterReadings $from, 
        MeterReadings $to
    ): BillResult {
        $consumption = $to->reading_value - $from->reading_value;
        
        if ($consumption < 0) {
            $consumption = 0;
        }

        $tieredResult = $this->applyTieredRates($tariff, $consumption);
        $fixedCostsResult = $this->calculateFixedCosts($tariff);
        
        $subtotal = $tieredResult['total'] + $fixedCostsResult['total'];
        $vatableAmount = $tieredResult['total'] + $fixedCostsResult['vatable_total'];
        $vatAmount = $this->calculateVat($vatableAmount, $tariff->getVatRate());
        $totalAmount = $subtotal + $vatAmount;

        // Determine if this is a provisional bill
        $isProvisional = $to->isEstimated() || !$to->isFinal();

        return new BillResult(
            consumption: $consumption,
            tieredCharge: $tieredResult['total'],
            fixedCostsTotal: $fixedCostsResult['total'],
            vatAmount: $vatAmount,
            totalAmount: $totalAmount,
            isProvisional: $isProvisional,
            openingReading: $from,
            closingReading: $to,
            tierBreakdown: $tieredResult['breakdown'],
            fixedCostsBreakdown: $fixedCostsResult['breakdown']
        );
    }

    /**
     * Apply tiered/block rates to consumption.
     * 
     * Tiers are cumulative - each tier covers a range of consumption.
     * For example, with tiers 0-6000, 6000-15000, 15000+:
     * - A consumption of 12500 uses 6000 units from tier 1 and 6500 from tier 2
     *
     * @param RegionsAccountTypeCost $tariff
     * @param float $consumption Consumption in liters (for water) or kWh (for electricity)
     * @return array ['total' => float, 'breakdown' => array]
     */
    public function applyTieredRates(RegionsAccountTypeCost $tariff, float $consumption): array
    {
        $tiers = $tariff->tiers()->orderBy('tier_number')->get();
        
        // If no tiers defined, fall back to legacy calculation
        if ($tiers->isEmpty()) {
            return $this->applyLegacyRates($tariff, $consumption);
        }

        $consumedSoFar = 0;
        $totalCharge = 0;
        $breakdown = [];

        foreach ($tiers as $tier) {
            if ($consumedSoFar >= $consumption) {
                break;
            }

            $tierMin = (float) $tier->min_units;
            $tierMax = $tier->max_units !== null ? (float) $tier->max_units : PHP_FLOAT_MAX;
            
            // Calculate how many units fall within this tier
            // The effective start is the max of (tier min, already consumed)
            $effectiveStart = max($tierMin, $consumedSoFar);
            // The effective end is the min of (tier max, total consumption)
            $effectiveEnd = min($tierMax, $consumption);
            
            // Units in this tier
            $unitsInTier = max(0, $effectiveEnd - $effectiveStart);
            
            if ($unitsInTier <= 0) {
                continue;
            }
            
            // For water, rates are typically per kL (1000 liters)
            // For electricity, rates might be per kWh (no conversion needed)
            // The rate_per_unit in the database should be configured appropriately
            // We apply the conversion factor for water meters
            $convertedUnits = $this->convertUnitsForRateCalculation($tariff, $unitsInTier);
            $tierCharge = $convertedUnits * (float) $tier->rate_per_unit;
            
            $totalCharge += $tierCharge;
            $breakdown[] = [
                'tier_number' => $tier->tier_number,
                'min_units' => $tierMin,
                'max_units' => $tierMax === PHP_FLOAT_MAX ? null : $tierMax,
                'units_in_tier' => $unitsInTier,
                'rate_per_unit' => (float) $tier->rate_per_unit,
                'charge' => round($tierCharge, 2),
            ];

            $consumedSoFar = $effectiveEnd;
        }

        return [
            'total' => round($totalCharge, 2),
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Convert units for rate calculation based on tariff type.
     * For water tariffs, converts liters to kiloliters.
     * For electricity tariffs, no conversion is needed (already in kWh).
     *
     * @param RegionsAccountTypeCost $tariff
     * @param float $units
     * @return float
     */
    protected function convertUnitsForRateCalculation(RegionsAccountTypeCost $tariff, float $units): float
    {
        // If this is a water tariff, convert liters to kiloliters
        if ($tariff->is_water) {
            return $units / self::LITERS_TO_KILOLITERS;
        }
        
        // For electricity or other utilities, use units directly
        return $units;
    }

    /**
     * Apply legacy rates from the existing tariff structure.
     * This is used when no tariff_tiers are defined.
     *
     * @param RegionsAccountTypeCost $tariff
     * @param float $consumption
     * @return array
     */
    protected function applyLegacyRates(RegionsAccountTypeCost $tariff, float $consumption): array
    {
        // Use the legacy water_in/water_out/electricity arrays for rate calculation
        // This maintains backward compatibility with existing tariff configurations
        $totalCharge = 0;
        $breakdown = [];

        // For now, return a simple calculation
        // This can be enhanced to read from legacy arrays as needed
        return [
            'total' => $totalCharge,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Calculate fixed costs for a tariff.
     *
     * @param RegionsAccountTypeCost $tariff
     * @return array
     */
    public function calculateFixedCosts(RegionsAccountTypeCost $tariff): array
    {
        $fixedCosts = $tariff->tariffFixedCosts;
        
        // If no fixed costs in the new table, try legacy fixed_costs array
        if ($fixedCosts->isEmpty() && !empty($tariff->fixed_costs)) {
            return $this->calculateLegacyFixedCosts($tariff);
        }

        $total = 0;
        $vatableTotal = 0;
        $breakdown = [];

        foreach ($fixedCosts as $cost) {
            $amount = (float) $cost->amount;
            $total += $amount;
            
            if ($cost->is_vatable) {
                $vatableTotal += $amount;
            }

            $breakdown[] = [
                'name' => $cost->name,
                'amount' => $amount,
                'is_vatable' => $cost->is_vatable,
            ];
        }

        return [
            'total' => round($total, 2),
            'vatable_total' => round($vatableTotal, 2),
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Calculate fixed costs from legacy fixed_costs JSON array.
     *
     * @param RegionsAccountTypeCost $tariff
     * @return array
     */
    protected function calculateLegacyFixedCosts(RegionsAccountTypeCost $tariff): array
    {
        $fixedCosts = $tariff->fixed_costs ?? [];
        $total = 0;
        $vatableTotal = 0;
        $breakdown = [];

        foreach ($fixedCosts as $cost) {
            $amount = (float) ($cost['value'] ?? $cost['amount'] ?? 0);
            $isVatable = $cost['is_vatable'] ?? true;
            
            $total += $amount;
            
            if ($isVatable) {
                $vatableTotal += $amount;
            }

            $breakdown[] = [
                'name' => $cost['name'] ?? 'Fixed Cost',
                'amount' => $amount,
                'is_vatable' => $isVatable,
            ];
        }

        return [
            'total' => round($total, 2),
            'vatable_total' => round($vatableTotal, 2),
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Calculate VAT amount.
     *
     * @param float $amount
     * @param float $vatRate
     * @return float
     */
    public function calculateVat(float $amount, float $vatRate): float
    {
        return round($amount * ($vatRate / 100), 2);
    }

    /**
     * Generate an estimated reading when user misses cycle end.
     *
     * @param Meter $meter
     * @param Carbon $cycleEnd
     * @return MeterReadings
     */
    public function generateEstimate(Meter $meter, Carbon $cycleEnd): MeterReadings
    {
        // Get the last two actual readings to calculate average daily consumption
        $lastReadings = $meter->readings()
            ->whereIn('reading_type', [MeterReadings::TYPE_ACTUAL, MeterReadings::TYPE_FINAL_ACTUAL])
            ->orderBy('reading_date', 'desc')
            ->limit(2)
            ->get();

        $estimatedValue = $this->estimateReadingValue($lastReadings, $cycleEnd);

        return MeterReadings::create([
            'meter_id' => $meter->id,
            'reading_value' => $estimatedValue,
            'reading_date' => $cycleEnd,
            'reading_type' => MeterReadings::TYPE_ESTIMATED,
            'is_locked' => false,
        ]);
    }

    /**
     * Estimate reading value based on historical consumption.
     *
     * @param \Illuminate\Support\Collection $lastReadings
     * @param Carbon $targetDate
     * @return float
     */
    protected function estimateReadingValue($lastReadings, Carbon $targetDate): float
    {
        if ($lastReadings->count() < 2) {
            // Not enough data to estimate, use last reading
            return $lastReadings->first()?->reading_value ?? 0;
        }

        $latestReading = $lastReadings->first();
        $previousReading = $lastReadings->last();

        $daysBetweenReadings = Carbon::parse($previousReading->reading_date)
            ->diffInDays(Carbon::parse($latestReading->reading_date));

        if ($daysBetweenReadings == 0) {
            return $latestReading->reading_value;
        }

        $consumptionBetweenReadings = $latestReading->reading_value - $previousReading->reading_value;
        $dailyConsumption = $consumptionBetweenReadings / $daysBetweenReadings;

        $daysToTarget = Carbon::parse($latestReading->reading_date)->diffInDays($targetDate);
        
        return round($latestReading->reading_value + ($dailyConsumption * $daysToTarget), 2);
    }

    /**
     * Reconcile when a late actual reading arrives.
     *
     * @param Bill $provisionalBill
     * @param MeterReadings $lateActual
     * @return Adjustment
     */
    public function reconcile(Bill $provisionalBill, MeterReadings $lateActual): Adjustment
    {
        $account = $provisionalBill->account;
        $tariff = $provisionalBill->tariffTemplate;
        $openingReading = $provisionalBill->openingReading;

        // Recalculate with actual reading
        $newResult = $this->calculateMonthly($tariff, $account, $openingReading, $lateActual);

        $adjustmentAmount = $newResult->totalAmount - $provisionalBill->total_amount;

        // Create adjustment record
        $adjustment = Adjustment::create([
            'bill_id' => $provisionalBill->id,
            'original_charge' => $provisionalBill->total_amount,
            'final_charge' => $newResult->totalAmount,
            'adjustment_amount' => $adjustmentAmount,
            'reason' => 'Reconciliation: Late actual reading received',
        ]);

        // Lock the estimated reading
        $provisionalBill->closingReading?->lock();

        // Update the closing reading type
        $lateActual->reading_type = MeterReadings::TYPE_FINAL_ACTUAL;
        $lateActual->save();

        return $adjustment;
    }

    /**
     * Calculate daily consumption for app display.
     *
     * @param Meter $meter
     * @return float
     */
    public function getDailyConsumption(Meter $meter): float
    {
        $lastReadings = $meter->readings()
            ->orderBy('reading_date', 'desc')
            ->limit(2)
            ->get();

        if ($lastReadings->count() < 2) {
            return 0;
        }

        $latestReading = $lastReadings->first();
        $previousReading = $lastReadings->last();

        $daysBetween = Carbon::parse($previousReading->reading_date)
            ->diffInDays(Carbon::parse($latestReading->reading_date));

        if ($daysBetween == 0) {
            return 0;
        }

        $consumption = $latestReading->reading_value - $previousReading->reading_value;
        
        return round($consumption / $daysBetween, 2);
    }

    /**
     * Get projected monthly bill for app display.
     *
     * @param Account $account
     * @return array
     */
    public function getProjectedMonthlyBill(Account $account): array
    {
        $tariff = $account->tariffTemplate;
        
        if (!$tariff) {
            return [
                'projected_consumption' => 0,
                'projected_amount' => 0,
                'breakdown' => [],
            ];
        }

        $meters = $account->meters;
        $totalProjectedConsumption = 0;
        $totalProjectedAmount = 0;
        $breakdown = [];

        foreach ($meters as $meter) {
            $dailyConsumption = $this->getDailyConsumption($meter);
            $daysInMonth = Carbon::now()->daysInMonth;
            $projectedMonthlyConsumption = $dailyConsumption * $daysInMonth;

            $tieredResult = $this->applyTieredRates($tariff, $projectedMonthlyConsumption);
            $fixedCostsResult = $this->calculateFixedCosts($tariff);
            
            $subtotal = $tieredResult['total'] + $fixedCostsResult['total'];
            $vatableAmount = $tieredResult['total'] + $fixedCostsResult['vatable_total'];
            $vatAmount = $this->calculateVat($vatableAmount, $tariff->getVatRate());
            $projectedAmount = $subtotal + $vatAmount;

            $totalProjectedConsumption += $projectedMonthlyConsumption;
            $totalProjectedAmount += $projectedAmount;

            $breakdown[] = [
                'meter_id' => $meter->id,
                'meter_title' => $meter->meter_title,
                'daily_consumption' => $dailyConsumption,
                'projected_consumption' => round($projectedMonthlyConsumption, 2),
                'projected_amount' => round($projectedAmount, 2),
            ];
        }

        return [
            'projected_consumption' => round($totalProjectedConsumption, 2),
            'projected_amount' => round($totalProjectedAmount, 2),
            'breakdown' => $breakdown,
            'tariff_name' => $tariff->template_name,
            'billing_type' => $tariff->billing_type ?? 'MONTHLY',
        ];
    }

    /**
     * Get the read date (billing date minus configured days).
     *
     * @param Account $account
     * @return Carbon
     */
    public function getReadDate(Account $account): Carbon
    {
        $billingDay = $account->bill_day ?? $account->tariffTemplate?->billing_day ?? self::DEFAULT_BILLING_DAY;
        $readDaysBefore = $account->read_day ?? $account->tariffTemplate?->read_day ?? self::DEFAULT_READ_DAYS_BEFORE;

        $billingDate = Carbon::now()->startOfMonth()->addDays($billingDay - 1);
        
        // If we've passed the billing date this month, move to next month
        if (Carbon::now()->gt($billingDate)) {
            $billingDate = $billingDate->addMonth();
        }

        return $billingDate->subDays($readDaysBefore);
    }

    /**
     * Get the billing date for an account.
     *
     * @param Account $account
     * @return Carbon
     */
    public function getBillingDate(Account $account): Carbon
    {
        $billingDay = $account->bill_day ?? $account->tariffTemplate?->billing_day ?? self::DEFAULT_BILLING_DAY;
        
        $billingDate = Carbon::now()->startOfMonth()->addDays($billingDay - 1);
        
        // If we've passed the billing date this month, move to next month
        if (Carbon::now()->gt($billingDate)) {
            $billingDate = $billingDate->addMonth();
        }

        return $billingDate;
    }

    /**
     * Get the cycle end date for an account.
     *
     * @param Account $account
     * @return Carbon
     */
    public function getCycleEndDate(Account $account): Carbon
    {
        return $this->getBillingDate($account);
    }

    /**
     * Process a reading submission and determine its type.
     *
     * @param Meter $meter
     * @param float $value
     * @param Carbon $date
     * @return MeterReadings
     */
    public function processReading(Meter $meter, float $value, Carbon $date): MeterReadings
    {
        $account = $meter->account;
        $cycleEndDate = $this->getCycleEndDate($account);
        
        $readingType = MeterReadings::TYPE_ACTUAL;
        
        if ($date->isSameDay($cycleEndDate)) {
            $readingType = MeterReadings::TYPE_FINAL_ACTUAL;
        }

        return MeterReadings::create([
            'meter_id' => $meter->id,
            'reading_value' => $value,
            'reading_date' => $date,
            'reading_type' => $readingType,
            'is_locked' => false,
        ]);
    }

    /**
     * Create a bill record from a BillResult.
     *
     * @param BillResult $result
     * @param Account $account
     * @param Meter $meter
     * @param BillingCycle|null $billingCycle
     * @return Bill
     */
    public function createBill(
        BillResult $result, 
        Account $account, 
        Meter $meter, 
        ?BillingCycle $billingCycle = null
    ): Bill {
        return Bill::create([
            'billing_cycle_id' => $billingCycle?->id,
            'account_id' => $account->id,
            'meter_id' => $meter->id,
            'tariff_template_id' => $account->tariff_template_id,
            'opening_reading_id' => $result->openingReading?->id,
            'closing_reading_id' => $result->closingReading?->id,
            'consumption' => $result->consumption,
            'tiered_charge' => $result->tieredCharge,
            'fixed_costs_total' => $result->fixedCostsTotal,
            'vat_amount' => $result->vatAmount,
            'total_amount' => $result->totalAmount,
            'is_provisional' => $result->isProvisional,
        ]);
    }

    /**
     * Get billing history for an account.
     *
     * @param Account $account
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBillingHistory(Account $account, int $limit = 12)
    {
        return Bill::where('account_id', $account->id)
            ->with(['meter', 'openingReading', 'closingReading', 'billingCycle'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
