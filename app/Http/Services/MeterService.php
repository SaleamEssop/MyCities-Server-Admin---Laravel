<?php

namespace App\Http\Services;

use App\Models\Meter;
use App\Models\MeterReadings;
use App\Models\RegionsAccountTypeCost;
use Illuminate\Support\Facades\DB;

class MeterService
{
    /**
     * To extract data for the meter reading
     * @param $meterId
     * @param $cycleStartDate
     * @param $cycleEndDate
     * @return array
     */
    public function getReadingInfo($meterId, $cycleStartDate, $cycleEndDate): array
    {
        $minDate = MeterReadings::query() // getting the oldest date of the cycle month
        ->select(DB::raw('MIN(reading_date) as min_date'))
            ->where('reading_date', '>=', $cycleStartDate)
            ->where('reading_date', '<=', $cycleEndDate)
            ->where('meter_id', $meterId)->value('min_date');
        $maxDate = MeterReadings::query() // getting the latest date of the cycle month
        ->select(DB::raw('MAX(reading_date) as max_date'))
            ->where('reading_date', '>=', $cycleStartDate)
            ->where('reading_date', '<=', $cycleEndDate)
            ->where('meter_id', $meterId)->value('max_date');

        $minReading = MeterReadings::query() // getting the latest reading of the cycle month
        ->where('reading_date', '=', $minDate)
            ->where('meter_id', $meterId)
            ->orderBy('reading_value')
            ->first();
        $maxReading = MeterReadings::query() // getting the oldest reading of the cycle month
        ->where('reading_date', '=', $maxDate)
            ->where('meter_id', $meterId)
            ->orderBy('reading_value', 'desc')
            ->first();
        return [
            'min_date' => $minDate,
            'max_date' => $maxDate,
            'min_reading' => $minReading,
            'max_reading' => $maxReading,
            'cycle_start_date' => $cycleStartDate,
            'cycle_end_date' => $cycleEndDate
        ];
    }

    public function getUsageInfo($meter, $meterReadingInfo): array
    {
        $totalDaysInBetween = getDaysBetweenTwoDates($meterReadingInfo['min_date'], $meterReadingInfo['max_date']);
        $totalDaysInBetweenCurrentCycle = getDaysBetweenTwoDates($meterReadingInfo['cycle_start_date'], $meterReadingInfo['cycle_end_date']);
        if ($meter->meter_type_id == config('constants.meter.type.water')) {
            $firstReading = (int)substr($meterReadingInfo['min_reading']->reading_value, 0, -4) ?? 0;
            $lastReading = (int)substr($meterReadingInfo['max_reading']->reading_value, 0, -4) ?? 0;
        } else if ($meter->meter_type_id == config('constants.meter.type.electricity')) {
            $firstReading = (int)substr($meterReadingInfo['min_reading']->reading_value, 0, -1) ?? 0;
            $lastReading = (int)substr($meterReadingInfo['max_reading']->reading_value, 0, -1) ?? 0;
        } else {
            $firstReading = 0;
            $lastReading = 0;
        }
        $totalUsage = $lastReading - $firstReading;
        $averageDailyUsage = round($totalUsage / $totalDaysInBetween, 2);
        return [
            'meter_type' => $meter->meter_type_id,
            'total_usage' => $totalUsage,
            'average_usage' => $averageDailyUsage,
            'predictive_monthly_usage' => $averageDailyUsage * $totalDaysInBetweenCurrentCycle,
        ];
    }


    public function getTotalCostByBrackets($moduleBrackets, $usage): float
    {
        $brackets = findBracketsBeforeAndIncluding($moduleBrackets, $usage);
        $usageCost = 0;
        foreach ($brackets as $bracket) {
            $min = (int)$bracket['min'];
            $max = (int)$bracket['max'];
            $cost = (float)$bracket['cost'];
            $total = $max - $min;
            if ($usage - $total < 0) {
                $total = $usage;
            }
            $usage -= $total;
            if (isset($bracket['percentage'])) {
                $percentage = (float)$bracket['percentage'];
                $total = $total - ($total * ($percentage / 100));
            }
            $totalCost = $total * $cost;
            $usageCost += $totalCost;
        }
        return round($usageCost, 2);
    }

    public function getAdditionalCosts($additionalCosts, $usage): array
    {
        $finalAdditionalCosts = [];
        $total = 0;
        foreach ($additionalCosts as $additionalCost) {
            if (isset($additionalCost['percentage']) && $additionalCost['percentage'] > 0) {
                $percentage = (float)$additionalCost['percentage'];
                if ($percentage != 100) {
                    $usage = $usage - ($usage * ($percentage / 100));
                }
            }
            if (isset($additionalCost['percentage']) && ($additionalCost['percentage'] == '' || $additionalCost['percentage'] == null)) {
                $usage = 1;
            }
            $additionalCostValue = (float)$additionalCost['cost'];
            $cost = round($usage * $additionalCostValue, 2);
            $finalAdditionalCosts[] = [
                'title' => $additionalCost['title'],
                'cost' => round($usage * $additionalCost['cost'], 2),
            ];
            $total += $cost;
        }
        return [
            'additional_costs' => $finalAdditionalCosts,
            'total' => round($total, 2)
        ];
    }


    public function getUsageWaterMeterCost($regionAccountTypeCost, $usageInfo): array
    {
        // Water in Costs
        $waterInBrackets = json_decode($regionAccountTypeCost->water_in, true);
        $waterInPrediction = $this->getTotalCostByBrackets($waterInBrackets, $usageInfo['predictive_monthly_usage']);
        $waterInTotal = $this->getTotalCostByBrackets($waterInBrackets, $usageInfo['total_usage']);
        $waterInAdditionalPredictiveCosts = [
            'additional_costs' => [],
            'total' => 0
        ];
        $waterInAdditionalTotalCosts = [
            'additional_costs' => [],
            'total' => 0
        ];
        if ($regionAccountTypeCost->waterin_additional) {
            $waterInAdditionalCostsModule = json_decode($regionAccountTypeCost->waterin_additional, true);
            $waterInAdditionalPredictiveCosts = $this->getAdditionalCosts($waterInAdditionalCostsModule, $usageInfo['predictive_monthly_usage']);
            $waterInAdditionalTotalCosts = $this->getAdditionalCosts($waterInAdditionalCostsModule, $usageInfo['total_usage']);
        }

        // End Water in Costs

        // Water out Costs
        $waterOutBrackets = json_decode($regionAccountTypeCost->water_out, true);
        $waterOutPrediction = $this->getTotalCostByBrackets($waterOutBrackets, $usageInfo['predictive_monthly_usage']);
        $waterOutTotal = $this->getTotalCostByBrackets($waterOutBrackets, $usageInfo['total_usage']);
        $waterOutAdditionalPredictiveCosts = [
            'additional_costs' => [],
            'total' => 0
        ];
        $waterOutAdditionalTotalCosts = [
            'additional_costs' => [],
            'total' => 0
        ];
        if ($regionAccountTypeCost->waterout_additional) {
            $waterOutAdditionalCostsModule = json_decode($regionAccountTypeCost->waterout_additional, true);
            $waterOutAdditionalPredictiveCosts = $this->getAdditionalCosts($waterOutAdditionalCostsModule, $usageInfo['predictive_monthly_usage']);
            $waterOutAdditionalTotalCosts = $this->getAdditionalCosts($waterOutAdditionalCostsModule, $usageInfo['total_usage']);
        }
        // End Water out Costs

        // Summing up all costs
        $totalCost = $waterInTotal + $waterOutTotal + $waterInAdditionalTotalCosts['total'] + $waterOutAdditionalTotalCosts['total'];
        $predictiveCost = $waterInPrediction + $waterOutPrediction + $waterInAdditionalPredictiveCosts['total'] + $waterOutAdditionalTotalCosts['total'];
        // End Summing up all costs
        return [
            'water_in' => [
                'predictive' => [
                    'total' => $waterInPrediction,
                    'additional_costs' => $waterInAdditionalPredictiveCosts
                ],
                'real' => [
                    'total' => $waterInTotal,
                    'additional_costs' => $waterInAdditionalTotalCosts
                ],
            ],
            'water_out' => [
                'predictive' => [
                    'total' => $waterOutPrediction,
                    'additional_costs' => $waterOutAdditionalPredictiveCosts
                ],
                'real' => [
                    'total' => $waterOutTotal,
                    'additional_costs' => $waterOutAdditionalTotalCosts
                ],
            ],
            'total' => round($totalCost, 2),
            'predictive' => round($predictiveCost, 2)
        ];
    }

    public function getElectricityMeterCost($regionAccountTypeCost, $usageInfo): array
    {
        $waterInBrackets = json_decode($regionAccountTypeCost->water_in, true);
        $waterOutBrackets = json_decode($regionAccountTypeCost->water_out, true);
        return [
            'water_in' => [
                'prediction' => $this->getTotalCostModule($waterInBrackets, $usageInfo['predictive_monthly_usage']),
                'total' => $this->getTotalCostModule($waterInBrackets, $usageInfo['total_usage'])
            ],
            'water_out' => [
                'prediction' => $this->getTotalCostModule($waterOutBrackets, $usageInfo['predictive_monthly_usage']),
                'total' => $this->getTotalCostModule($waterOutBrackets, $usageInfo['total_usage'])
            ]
        ];
    }

    // main function to calculate cost estimation
    public function getCostEstimationByMeterId($meterId, $month = 0): array
    {
        $userDetails = loggedUserDetails();
        // check if account exits or not
        if (!$userDetails['account_ids']) {
            return ['status' => false, 'message' => 'No account found! Please add one', 'status_code' => 404];
        }
        $meter = Meter::query()->whereIn('account_id', $userDetails['account_ids']) // condition to check if meter belongs to the current user
        ->find($meterId);
        if (!$meter) {
            return ['status' => false, 'message' => 'Meter not found!', 'status_code' => 404];
        }
        $account = $userDetails['accounts']->where('id', $meter->account_id)->first();
        $cycleDates = getMonthCycle($account['read_day'], (int)$month); // get cycle date from and too of current or previous months
        $totalReadings = MeterReadings::query() // getting the oldest date of the cycle month
        ->select(DB::raw('MIN(reading_date) as min_date'))
            ->where('reading_date', '>=', $cycleDates['start_date'])
            ->where('reading_date', '<=', $cycleDates['end_date'])
            ->where('meter_id', $meterId)->count();
        if ($totalReadings <= 1) {
            return ['status' => false, 'message' => "There must be minimum of two readings for the cycle month! found readings: $totalReadings", 'status_code' => 422];
        }
        $regionAccountTypeCost = RegionsAccountTypeCost::query()
            ->where('region_id', $account['region_id'])
            ->where('account_type_id', $account['account_type_id'])
//            ->where('start_date', '<=', $cycleDates['start_date'])
//            ->where('end_date', '>=', $cycleDates['end_date'])
            ->first();
        if (!$regionAccountTypeCost) {
            return ['status' => false, 'message' => "No active cost module found. Please contact administrator!", 'status_code' => 404];
        }

        $meterReadingInfo = $this->getReadingInfo($meterId, $cycleDates['start_date'], $cycleDates['end_date']);
        $usageInfo = $this->getUsageInfo($meter, $meterReadingInfo);
        return $this->getUsageWaterMeterCost($regionAccountTypeCost, $usageInfo);
    }
}
