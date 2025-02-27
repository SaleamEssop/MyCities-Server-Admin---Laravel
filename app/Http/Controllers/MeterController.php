<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Meter;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\BillingPeriod;
use App\Models\MeterReadings;
use App\Http\Services\MeterService;
use Illuminate\Support\Facades\Session;

class MeterController extends Controller
{

    public function __construct(MeterService $service)
    {
        $this->service = $service;
    }

    public function showDetail($id)
    {
        $meter = Meter::with(['meterTypes', 'meterCategory', 'readings.addedBy', 'account', 'payments.billingPeriod'])->where('id', $id)->firstOrFail();
        $meterReadings = $meter->readings;

        $account = $meter->account;
        $property = $account->property;
        $propertyManager = $property->property_manager;


        $billingDate = $property->billing_day;

        $currentDate = now();

        // pervious billing cycles
        $perviousBillingCycles = [];
        for ($i = 0; $i < 6; $i++) {
            $endDate = Carbon::createFromDate($currentDate->year, $currentDate->month, $billingDate)
                ->subDay()
                ->subMonths($i);
            $startDate = Carbon::createFromDate($endDate->year, $endDate->month, $billingDate)
                ->subMonth();
    
            $perviousBillingCycles[] = [
                'start_date' => $startDate->format('d M Y'),
                'end_date' => $endDate->format('d M Y'),
                'value' => $startDate->toDateString() . ' ' . $endDate->toDateString(),
            ];
        }
    
        $perviousBillingCycles = array_reverse($perviousBillingCycles);


        //billing period
        if ($currentDate->day < $billingDate) {
            $startDate = $currentDate->copy()->subMonth()->setDay($billingDate);
            $endDate = $currentDate->copy()->setDay($billingDate - 1);
        } else {
            $startDate = $currentDate->copy()->setDay($billingDate);
            $endDate = $currentDate->copy()->addMonth()->setDay($billingDate - 1);
        }
        function getOrdinalSuffix($day) {
            if (!in_array(($day % 100), [11, 12, 13])) {
                switch ($day % 10) {
                    case 1: return 'st';
                    case 2: return 'nd';
                    case 3: return 'rd';
                }
            }
            return 'th';
        }
        $startDay = $startDate->day . getOrdinalSuffix($startDate->day);
        $endDay = $endDate->day . getOrdinalSuffix($endDate->day);
        $propertyBillingPeriod = "{$startDay} {$startDate->format('M')} to {$endDay} {$endDate->format('M')}";


        //get previous month readings
        $startDate = now()->subMonths(2)->setDay($billingDate)->startOfDay();
        $endDate = now()->subMonth()->setDay($billingDate)->startOfDay();

        $previousMonthReadings = $meterReadings
            ->filter(function ($reading) use ($startDate, $endDate) {
                $readingDate = Carbon::createFromFormat('m-d-Y', $reading->reading_date);
                return $readingDate >= $startDate && $readingDate <= $endDate;
            })
            ->sortBy('reading_date');

        $usage = [];
        $previousValue = null;
        foreach ($previousMonthReadings as $reading) {
            if ($previousValue !== null) {
                $usage[] = (int)$reading->reading_value - (int)$previousValue;
            }
            $previousValue = $reading->reading_value;
        }
        $previousCycleTotalUsage = array_sum($usage);


        $previousMonthLatestReading = $previousMonthReadings->last();


        $payments = $meter->payments;
    
        $meterType = strtolower($meter->meterTypes->title);
        $cycleDates = getMonthCycle($property->billing_day);
        $cycleStartDate = Carbon::parse($cycleDates['start_date']);
        $cycleEndDate = Carbon::parse($cycleDates['end_date'])->subDay();

        //current billing cycle payments
        $currentCyclePayments = $payments->filter(function ($payment) use ($cycleStartDate, $cycleEndDate) {
            $paymentDate = Carbon::parse($payment->payment_date);
            return $paymentDate >= $cycleStartDate && $paymentDate <= $cycleEndDate;
        });
        $currentPaymentsAmount = $currentCyclePayments->sum('amount');

        // overdue payments
        $pendingPayments = $meter->payments->where('status', 'pending');
        $pendingPaymentsAmount = $pendingPayments->sum('amount');
        $partiallyPaidPayments = $meter->payments->where('status', 'partially_paid');
        $partiallyPaidPaymentsAmount = $partiallyPaidPayments->sum('amount') - $partiallyPaidPayments->sum('paid_amount');
        $totalPendingAmount = $pendingPaymentsAmount + $partiallyPaidPaymentsAmount;

        //latest reading fot current billing cycle
        $latestReading = $meterReadings->filter(function ($reading) use ($cycleStartDate) {
            return Carbon::createFromFormat('m-d-Y', $reading->reading_date)->gte($cycleStartDate);
        })->sortByDesc('reading_date')->first();
        //get all readings of current billing cycle
        $currentCycleReadings = $meterReadings->filter(function ($reading) use ($cycleStartDate, $cycleEndDate) {
            $readingDate = Carbon::createFromFormat('m-d-Y', $reading->reading_date);
            return $readingDate >= $cycleStartDate && $readingDate <= $cycleEndDate;
        });


        $totalReadingDifference = 0;
        if ($latestReading && $previousMonthLatestReading) {
            $latestReadingValue = (int) ltrim($latestReading->reading_value, '0');
            $previousMonthLatestReadingValue = (int) ltrim($previousMonthLatestReading->reading_value, '0');
            $totalReadingDifference = $latestReadingValue - $previousMonthLatestReadingValue;
        }


        $meterId = $meter->id;
        $includeVAT = true;


        $waterInCost = json_decode($meter->account->property->cost->water_in, true) ?: [];
        $waterOutCost = json_decode($meter->account->property->cost->water_out, true) ?: [];
        $electricityCost = json_decode($meter->account->property->cost->electricity, true) ?: [];
        $waterInAdditional = json_decode($meter->account->property->cost->waterin_additional ?? '[]', true) ?: [];
        $waterOutAdditional = json_decode($meter->account->property->cost->waterout_additional ?? '[]', true) ?: [];
        $electricityAdditional = json_decode($meter->account->property->cost->electricity_additional ?? '[]', true) ?: [];
        $vatPercentage = (float) ($meter->account->property->cost->vat_percentage ?? 0);

        $billingPeriods = $this->calculateBillingPeriods(
            $meterId,
            $meterType,
            $sortedReadings = $meter->readings->sortBy('reading_date'),
            $cycleStartDate,
            $cycleEndDate,
            $currentDate,
            $includeVAT,
            $waterInCost,
            $waterOutCost,
            $electricityCost,
            $waterInAdditional,
            $waterOutAdditional,
            $electricityAdditional,
            $vatPercentage,
            $account->id
        );

        return view('admin.meter.show', compact(
            'payments',
            'perviousBillingCycles',
            'property',
            'propertyManager',
            'account',
            'meter',
            'meterReadings',
            'currentPaymentsAmount',
            'totalPendingAmount',
            'latestReading',
            'totalReadingDifference',
            'previousMonthLatestReading',
            'previousCycleTotalUsage',
            'propertyBillingPeriod',
            'billingPeriods'
        ));
    }

    /**
     * Calculate billing periods and sync them to the database, generating payments for actual and final estimate periods.
     *
     * @return array
     */
    protected function calculateBillingPeriods(
        $meterId,
        $meterType,
        $sortedReadings,
        $cycleStartDate,
        $cycleEndDate,
        $currentDate,
        $includeVAT,
        $waterInCost,
        $waterOutCost,
        $electricityCost,
        $waterInAdditional,
        $waterOutAdditional,
        $electricityAdditional,
        $vatPercentage,
        $accountId
    ) {
        $billingPeriods = [];
        $prevReading = null;

        $getCost = function ($usage, $costArray) {
            $totalCost = 0;
            $remainingUsage = $usage;
            foreach ($costArray as $tier) {
                $tierMin = (int) $tier['min'];
                $tierMax = (int) $tier['max'];
                $tierCost = (float) $tier['cost'];
                if ($remainingUsage > 0) {
                    $tierUsage = min($remainingUsage, $tierMax - $tierMin);
                    $totalCost += $tierUsage * $tierCost;
                    $remainingUsage -= $tierUsage;
                    if ($remainingUsage <= 0) break;
                }
            }
            return $totalCost;
        };

        $getAdditionalCosts = function ($additionalCosts, $usage) {
            $finalAdditionalCosts = [];
            $total = 0;
            foreach ($additionalCosts as $additionalCost) {
                $percentage = isset($additionalCost['percentage']) && $additionalCost['percentage'] > 0 ? (float)$additionalCost['percentage'] : 100;
                $adjustedUsage = $percentage == 100 ? $usage : $usage * ($percentage / 100);
                if (isset($additionalCost['percentage']) && empty($additionalCost['percentage'])) $adjustedUsage = 1;
                $cost = round($adjustedUsage * (float)$additionalCost['cost'], 2);
                $finalAdditionalCosts[] = [
                    'title' => $additionalCost['title'],
                    'cost' => $cost,
                ];
                $total += $cost;
            }
            return ['additional_costs' => $finalAdditionalCosts, 'total' => round($total, 2)];
        };

        $isCurrentCycle = $currentDate->between($cycleStartDate, $cycleEndDate);

        foreach ($sortedReadings as $reading) {
            $readingDate = Carbon::createFromFormat('m-d-Y', $reading->reading_date);
            $readingValue = (int) ltrim($reading->reading_value, '0');
            $rawReadingValue = $reading->reading_value;
            $readingId = $reading->id;

            if ($prevReading) {
                $prevDate = Carbon::createFromFormat('m-d-Y', $prevReading->reading_date);
                $prevValue = (int) ltrim($prevReading->reading_value, '0');
                $rawPrevValue = $prevReading->reading_value;
                $prevReadingId = $prevReading->id;
                $usage = max(0, $readingValue - $prevValue);
                $daysInPeriod = $prevDate->diffInDays($readingDate);

                if ($meterType === 'water') {
                    $consumptionCharge = $getCost($usage, $waterInCost);
                    $dischargeCharge = $getCost($usage, $waterOutCost);
                    $waterInAdditionalCost = $getAdditionalCosts($waterInAdditional, $usage);
                    $waterOutAdditionalCost = $getAdditionalCosts($waterOutAdditional, $usage);
                    $baseCost = $consumptionCharge + $dischargeCharge + $waterInAdditionalCost['total'] + $waterOutAdditionalCost['total'];
                    $vat = ($vatPercentage / 100) * $baseCost;
                    $totalCost = $includeVAT ? $baseCost + $vat : $baseCost;
                } elseif ($meterType === 'electricity') {
                    $consumptionCharge = $getCost($usage, $electricityCost);
                    $dischargeCharge = 0;
                    $electricityAdditionalCost = $getAdditionalCosts($electricityAdditional, $usage);
                    $baseCost = $consumptionCharge + $electricityAdditionalCost['total'];
                    $vat = ($vatPercentage / 100) * $baseCost;
                    $totalCost = $includeVAT ? $baseCost + $vat : $baseCost;
                } else {
                    $consumptionCharge = 0;
                    $dischargeCharge = 0;
                    $electricityAdditionalCost = ['additional_costs' => [], 'total' => 0];
                    $baseCost = 0;
                    $vat = 0;
                    $totalCost = 0;
                }

                $dailyUsage = $daysInPeriod > 0 ? $usage / $daysInPeriod : 0;
                $dailyCost = $daysInPeriod > 0 ? $totalCost / $daysInPeriod : 0;

                $status = $readingDate->lte($cycleStartDate) ? "Final Estimate" : "Actual";

                if ($prevDate->lt($cycleStartDate) && $readingDate->gt($cycleStartDate)) {
                    $daysBeforeCycle = $prevDate->diffInDays($cycleStartDate);
                    $totalDays = $prevDate->diffInDays($readingDate);
                    $usageBeforeCycle = $daysBeforeCycle > 0 ? ($usage * $daysBeforeCycle / $totalDays) : 0;
                    $usageInCycle = $usage - $usageBeforeCycle;
                    $splitReadingValue = $prevValue + $usageBeforeCycle;
                    $rawSplitReadingValue = str_pad((int)$splitReadingValue, 6, '0', STR_PAD_LEFT);

                    if ($usageBeforeCycle > 0) {
                        if ($meterType === 'water') {
                            $beforeConsumptionCharge = $getCost($usageBeforeCycle, $waterInCost);
                            $beforeDischargeCharge = $getCost($usageBeforeCycle, $waterOutCost);
                            $beforeWaterInAdditional = $getAdditionalCosts($waterInAdditional, $usageBeforeCycle);
                            $beforeWaterOutAdditional = $getAdditionalCosts($waterOutAdditional, $usageBeforeCycle);
                            $beforeBaseCost = $beforeConsumptionCharge + $beforeDischargeCharge + $beforeWaterInAdditional['total'] + $beforeWaterOutAdditional['total'];
                        } elseif ($meterType === 'electricity') {
                            $beforeConsumptionCharge = $getCost($usageBeforeCycle, $electricityCost);
                            $beforeDischargeCharge = 0;
                            $beforeElectricityAdditional = $getAdditionalCosts($electricityAdditional, $usageBeforeCycle);
                            $beforeBaseCost = $beforeConsumptionCharge + $beforeElectricityAdditional['total'];
                        } else {
                            $beforeConsumptionCharge = 0;
                            $beforeDischargeCharge = 0;
                            $beforeElectricityAdditional = ['additional_costs' => [], 'total' => 0];
                            $beforeBaseCost = 0;
                        }
                        $beforeVat = ($vatPercentage / 100) * $beforeBaseCost;
                        $beforeTotalCost = $includeVAT ? $beforeBaseCost + $beforeVat : $beforeBaseCost;
                        $beforeDailyUsage = $daysBeforeCycle > 0 ? $usageBeforeCycle / $daysBeforeCycle : 0;
                        $beforeDailyCost = $daysBeforeCycle > 0 ? $beforeTotalCost / $daysBeforeCycle : 0;

                        $billingPeriods[] = [
                            'meter_id' => $meterId,
                            'start_date' => $prevDate->toDateString(),
                            'end_date' => $cycleStartDate->toDateString(),
                            'start_reading' => $rawPrevValue,
                            'end_reading' => $rawSplitReadingValue,
                            'start_reading_id' => $prevReadingId,
                            'end_reading_id' => null,
                            'usage_liters' => round($usageBeforeCycle, 2),
                            'cost' => $beforeTotalCost,
                            'consumption_charge' => $beforeConsumptionCharge,
                            'discharge_charge' => $beforeDischargeCharge,
                            'additional_costs' => $meterType === 'water' ? $beforeWaterInAdditional['additional_costs'] : ($meterType === 'electricity' ? $beforeElectricityAdditional['additional_costs'] : []),
                            'water_out_additional' => $meterType === 'water' ? $beforeWaterOutAdditional['additional_costs'] : [],
                            'vat' => round($beforeVat, 2),
                            'daily_usage' => round($beforeDailyUsage, 2),
                            'daily_cost' => round($beforeDailyCost, 2),
                            'status' => "Final Estimate"
                        ];
                    }

                    if ($usageInCycle > 0) {
                        $daysInCycle = $cycleStartDate->diffInDays($readingDate);
                        if ($meterType === 'water') {
                            $inCycleConsumptionCharge = $getCost($usageInCycle, $waterInCost);
                            $inCycleDischargeCharge = $getCost($usageInCycle, $waterOutCost);
                            $inCycleWaterInAdditional = $getAdditionalCosts($waterInAdditional, $usageInCycle);
                            $inCycleWaterOutAdditional = $getAdditionalCosts($waterOutAdditional, $usageInCycle);
                            $inCycleBaseCost = $inCycleConsumptionCharge + $inCycleDischargeCharge + $inCycleWaterInAdditional['total'] + $inCycleWaterOutAdditional['total'];
                        } elseif ($meterType === 'electricity') {
                            $inCycleConsumptionCharge = $getCost($usageInCycle, $electricityCost);
                            $inCycleDischargeCharge = 0;
                            $inCycleElectricityAdditional = $getAdditionalCosts($electricityAdditional, $usageInCycle);
                            $inCycleBaseCost = $inCycleConsumptionCharge + $inCycleElectricityAdditional['total'];
                        } else {
                            $inCycleConsumptionCharge = 0;
                            $inCycleDischargeCharge = 0;
                            $inCycleElectricityAdditional = ['additional_costs' => [], 'total' => 0];
                            $inCycleBaseCost = 0;
                        }
                        $inCycleVat = ($vatPercentage / 100) * $inCycleBaseCost;
                        $inCycleTotalCost = $includeVAT ? $inCycleBaseCost + $inCycleVat : $inCycleBaseCost;
                        $inCycleDailyUsage = $daysInCycle > 0 ? $usageInCycle / $daysInCycle : 0;
                        $inCycleDailyCost = $daysInCycle > 0 ? $inCycleTotalCost / $daysInCycle : 0;

                        $billingPeriods[] = [
                            'meter_id' => $meterId,
                            'start_date' => $cycleStartDate->toDateString(),
                            'end_date' => $readingDate->toDateString(),
                            'start_reading' => $rawSplitReadingValue,
                            'end_reading' => $rawReadingValue,
                            'start_reading_id' => $prevReadingId,
                            'end_reading_id' => $readingId,
                            'usage_liters' => round($usageInCycle, 2),
                            'cost' => $inCycleTotalCost,
                            'consumption_charge' => $inCycleConsumptionCharge,
                            'discharge_charge' => $inCycleDischargeCharge,
                            'additional_costs' => $meterType === 'water' ? $inCycleWaterInAdditional['additional_costs'] : ($meterType === 'electricity' ? $inCycleElectricityAdditional['additional_costs'] : []),
                            'water_out_additional' => $meterType === 'water' ? $inCycleWaterOutAdditional['additional_costs'] : [],
                            'vat' => round($inCycleVat, 2),
                            'daily_usage' => round($inCycleDailyUsage, 2),
                            'daily_cost' => round($inCycleDailyCost, 2),
                            'status' => "Actual"
                        ];
                    }
                } else {
                    $billingPeriods[] = [
                        'meter_id' => $meterId,
                        'start_date' => $prevDate->toDateString(),
                        'end_date' => $readingDate->toDateString(),
                        'start_reading' => $rawPrevValue,
                        'end_reading' => $rawReadingValue,
                        'start_reading_id' => $prevReadingId,
                        'end_reading_id' => $readingId,
                        'usage_liters' => $usage,
                        'cost' => $totalCost,
                        'consumption_charge' => $consumptionCharge,
                        'discharge_charge' => $dischargeCharge,
                        'additional_costs' => $meterType === 'water' ? $waterInAdditionalCost['additional_costs'] : ($meterType === 'electricity' ? $electricityAdditionalCost['additional_costs'] : []),
                        'water_out_additional' => $meterType === 'water' ? $waterOutAdditionalCost['additional_costs'] : [],
                        'vat' => round($vat, 2),
                        'daily_usage' => round($dailyUsage, 2),
                        'daily_cost' => round($dailyCost, 2),
                        'status' => $status
                    ];
                }
            }
            $prevReading = $reading;
        }

        $lastReadingDate = $sortedReadings->isEmpty() ? $cycleStartDate : Carbon::createFromFormat('m-d-Y', $sortedReadings->last()->reading_date);
        $lastReadingValue = $sortedReadings->isEmpty() ? 0 : (int) ltrim($sortedReadings->last()->reading_value, '0');
        $rawLastReadingValue = $sortedReadings->isEmpty() ? '000000' : $sortedReadings->last()->reading_value;
        $lastReadingId = $sortedReadings->isEmpty() ? null : $sortedReadings->last()->id;
        $prevLastReading = $sortedReadings->count() > 1 ? $sortedReadings->slice(-2, 1)->first() : null;

        if ($isCurrentCycle && $lastReadingDate->lt($cycleEndDate)) {
            $prevDate = $prevLastReading ? Carbon::createFromFormat('m-d-Y', $prevLastReading->reading_date) : $cycleStartDate;
            $daysSoFar = $prevDate->diffInDays($lastReadingDate);
            $lastPeriodUsage = $prevLastReading ? ($lastReadingValue - (int) ltrim($prevLastReading->reading_value, '0')) : 0;
            $dailyUsageRate = $daysSoFar > 0 ? $lastPeriodUsage / $daysSoFar : 0;
            $daysRemaining = $cycleEndDate->diffInDays($lastReadingDate);
            $estimatedUsage = $dailyUsageRate * $daysRemaining;
            $estimatedEndReading = $lastReadingValue + $estimatedUsage;
            $rawEstimatedEndReading = str_pad((int)$estimatedEndReading, 6, '0', STR_PAD_LEFT);

            if ($meterType === 'water') {
                $estimatedConsumptionCharge = $getCost($estimatedUsage, $waterInCost);
                $estimatedDischargeCharge = $getCost($estimatedUsage, $waterOutCost);
                $estimatedWaterInAdditional = $getAdditionalCosts($waterInAdditional, $estimatedUsage);
                $estimatedWaterOutAdditional = $getAdditionalCosts($waterOutAdditional, $estimatedUsage);
                $estimatedBaseCost = $estimatedConsumptionCharge + $estimatedDischargeCharge + $estimatedWaterInAdditional['total'] + $estimatedWaterOutAdditional['total'];
                $estimatedVat = ($vatPercentage / 100) * $estimatedBaseCost;
                $estimatedCost = $includeVAT ? $estimatedBaseCost + $estimatedVat : $estimatedBaseCost;
            } elseif ($meterType === 'electricity') {
                $estimatedConsumptionCharge = $getCost($estimatedUsage, $electricityCost);
                $estimatedDischargeCharge = 0;
                $estimatedElectricityAdditional = $getAdditionalCosts($electricityAdditional, $estimatedUsage);
                $estimatedBaseCost = $estimatedConsumptionCharge + $estimatedElectricityAdditional['total'];
                $estimatedVat = ($vatPercentage / 100) * $estimatedBaseCost;
                $estimatedCost = $includeVAT ? $estimatedBaseCost + $estimatedVat : $estimatedBaseCost;
            } else {
                $estimatedCost = 0;
                $estimatedConsumptionCharge = 0;
                $estimatedDischargeCharge = 0;
                $estimatedElectricityAdditional = ['additional_costs' => [], 'total' => 0];
                $estimatedVat = 0;
            }

            $estimatedDailyUsage = $daysRemaining > 0 ? $estimatedUsage / $daysRemaining : 0;
            $estimatedDailyCost = $daysRemaining > 0 ? $estimatedCost / $daysRemaining : 0;

            $billingPeriods[] = [
                'meter_id' => $meterId,
                'start_date' => $lastReadingDate->toDateString(),
                'end_date' => $cycleEndDate->toDateString(),
                'start_reading' => $rawLastReadingValue,
                'end_reading' => $rawEstimatedEndReading,
                'start_reading_id' => $lastReadingId,
                'end_reading_id' => null,
                'usage_liters' => round($estimatedUsage, 2),
                'cost' => $estimatedCost,
                'consumption_charge' => $estimatedConsumptionCharge,
                'discharge_charge' => $estimatedDischargeCharge,
                'additional_costs' => $meterType === 'water' ? $estimatedWaterInAdditional['additional_costs'] : ($meterType === 'electricity' ? $estimatedElectricityAdditional['additional_costs'] : []),
                'water_out_additional' => $meterType === 'water' ? $estimatedWaterOutAdditional['additional_costs'] : [],
                'vat' => round($estimatedVat, 2),
                'daily_usage' => round($estimatedDailyUsage, 2),
                'daily_cost' => round($estimatedDailyCost, 2),
                'status' => "Estimated"
            ];
        }

        // Remove overlapping estimated periods, use cycleStartDate if no readings exist
        $lastReadingDateCarbon = $sortedReadings->isEmpty() ? $cycleStartDate : Carbon::createFromFormat('m-d-Y', $sortedReadings->last()->reading_date);
        BillingPeriod::where('meter_id', $meterId)
            ->where('status', 'Estimated')
            ->where(function ($query) use ($lastReadingDateCarbon, $cycleEndDate) {
                $query->where('start_date', '<', $lastReadingDateCarbon->toDateString())
                    ->where('end_date', '>', $lastReadingDateCarbon->toDateString());
            })
            ->delete();

        // Save billing periods and generate payments for actual and final estimate periods
        foreach ($billingPeriods as $period) {
            $billingPeriod = BillingPeriod::updateOrCreate(
                [
                    'meter_id' => $meterId,
                    'start_date' => $period['start_date'],
                    'end_date' => $period['end_date'],
                ],
                $period
            );

            // Generate payments for "Actual" or "Final Estimate" periods with a valid end_reading_id
            if (($period['status'] === 'Actual' || $period['status'] === 'Final Estimate') && !is_null($period['end_reading_id'])) {
                $readingDate = Carbon::createFromFormat('Y-m-d', $period['end_date'])->toDateString();

                // Check for existing payment to avoid duplicates
                $existingPayment = Payment::where('meter_id', $meterId)
                    ->where('billing_period_id', $billingPeriod->id)
                    ->where('reading_id', $period['end_reading_id'])
                    ->exists();

                if (!$existingPayment) {
                    Payment::create([
                        'account_id' => $accountId,
                        'meter_id' => $meterId,
                        'reading_id' => $period['end_reading_id'],
                        'payment_date' => null,
                        'payment_method' => null,
                        'amount' => $period['cost'],
                        'status' => 'pending',
                        'billing_period_id' => $billingPeriod->id,
                    ]);
                }
            }
        }

        // Incremental sync if new readings exist
        $latestReadingTimestamp = $sortedReadings->max('updated_at') ?? Carbon::minValue();
        $lastBillingUpdate = BillingPeriod::where('meter_id', $meterId)->max('updated_at') ?? Carbon::minValue();
        $hasNewReading = $latestReadingTimestamp > $lastBillingUpdate;

        if ($hasNewReading) {
            $existingPeriods = BillingPeriod::where('meter_id', $meterId)
                ->where('end_date', '<', $sortedReadings->isEmpty() ? $cycleStartDate->toDateString() : $sortedReadings->last()->reading_date)
                ->get()
                ->toArray();

            $allPeriods = array_merge($existingPeriods, $billingPeriods);

            $uniquePeriods = [];
            $seen = [];
            foreach ($allPeriods as $period) {
                $key = $period['start_date'] . '|' . $period['end_date'];
                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $uniquePeriods[] = $period;
                }
            }
            usort($uniquePeriods, function ($a, $b) {
                return strcmp($a['start_date'], $b['start_date']);
            });

            foreach ($uniquePeriods as $period) {
                BillingPeriod::updateOrCreate(
                    [
                        'meter_id' => $meterId,
                        'start_date' => $period['start_date'],
                        'end_date' => $period['end_date'],
                    ],
                    $period
                );
            }
        }

        return $billingPeriods;
    }

    //makePayment
    public function makePayment(Request $request)
    {
        
        $paymentId = $request->input('payment_id');
        if ($paymentId) {
            $payment = Payment::find($paymentId);

         
            if ($request->status == 'partially_paid') {
                $payment->paid_amount = $request->amount;
            }
        
            $payment->amount = $request->actual_amount;
            $payment->status = $request->status;
            $payment->payment_date = Carbon::createFromFormat('m/d/Y', $request->payment_date)->format('Y-m-d');
            $payment->save();
            if ($payment) {
                Session::flash('alert-class', 'alert-success');
                Session::flash('alert-message', 'Payment updated successfully!');
                return back();
            } else {
                Session::flash('alert-class', 'alert-danger');
                Session::flash('alert-message', 'Oops, something went wrong.');
                return redirect()->back();
            }
        } else {
            $reading = MeterReadings::find($request->reading_id);
            $payment = new Payment();
            $payment->account_id = $reading->meter->account_id;
            $payment->meter_id = $reading->meter_id;
            $payment->reading_id = $reading->id;
            $payment->paid_by = auth()->user()->id;
            $payment->payment_date = Carbon::createFromFormat('m/d/Y', $request->payment_date)->format('Y-m-d');
            $payment->amount = $request->amount;
            $payment->status = $request->status;
            $payment->save();
            if ($payment) {
                Session::flash('alert-class', 'alert-success');
                Session::flash('alert-message', 'Payment made successfully!');
                return back();
            } else {
                Session::flash('alert-class', 'alert-danger');
                Session::flash('alert-message', 'Oops, something went wrong.');
                return redirect()->back();
            }
        }
    }

    public function costEstimation($meterId, Request $request)
    {
        $response = $this->service->getCostEstimationByMeterId($meterId, $request->get('month', 0));
        if (isset($response['status']) && !$response['status']) {
            return response($response, $response['status_code']);
        }
        return response($response);
    }

    public function completeBill($accountId, Request $request)
    {
        $response = $this->service->getCompleteBillByAccount($accountId, $request->get('month', 0));
        if (isset($response['status']) && !$response['status']) {
            return response($response, $response['status_code']);
        }
        return response($response);
    }
}
