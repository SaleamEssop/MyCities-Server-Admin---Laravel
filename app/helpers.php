<?php

use App\Models\User;
use Illuminate\Support\Carbon;

if (!function_exists('validateData')) {

    function validateData($required, $data)
    {
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['status' => false, 'error' => "Required '$field' field is missing."];
            }
        }
        return ['status' => true, 'error' => ""];
    }
}

if (!function_exists('loggedUserDetails')) {

    function loggedUserDetails(): array
    {
        $user = User::query()->with(['sites.account'])->find(auth()->id())->toArray();
        // check if account exits or not
        $accountIds = [];
        $accounts = [];
        if ((isset($user['sites'][0]) && $user['sites'][0]['account'])) {
            foreach ($user['sites'] as $site) {
                foreach ($site['account'] as $account) {
                    $accounts[] = $account;
                    $accountIds[] = $account['id'];
                }
            }
        }
        return [
            'user' => collect($user),
            'account_ids' => $accountIds,
            'accounts' => collect($accounts)
        ];

    }
}
if (!function_exists('getDayWithSuffix')) {

    function getDayWithSuffix($day): string
    {
        if ($day >= 11 && $day <= 13) {
            return $day . 'th';
        }

        switch ($day % 10) {
            case 1: return $day . 'st';
            case 2: return $day . 'nd';
            case 3: return $day . 'rd';
            default: return $day . 'th';
        }
    }
}

if (!function_exists('getMonthCycle')) {

    function getMonthCycle($cycleDay = 15, $subtractMonths = 0): array
    {

        if ($subtractMonths === 0) {
            $subtractMonths = 1;
        }
        $today = Carbon::today();
        if ($subtractMonths !== 0) {
            $cycleStart = $today->copy()->subMonths(abs($subtractMonths))->day($cycleDay);
        } else {
            $cycleStart = $today->copy()->day($cycleDay);
        }

        if ($today->day >= $cycleDay && $subtractMonths === 0) {
            $cycleEnd = $cycleStart->copy()->addMonth()->day($cycleDay)->subDay();
        } else {
            $cycleEnd = $cycleStart->copy()->addMonths(1)->day($cycleDay)->subDay();
        }

        return [
            'start_date' => $cycleStart->format('Y-m-d'),
            'end_date' => $cycleEnd->format('Y-m-d'),
        ];
    }
}
if (!function_exists('getCurrentMonthCycle')) {

    function getCurrentMonthCycle($cycleDay = 15, $subtractMonths = 0): array
    {
        $today = Carbon::today();

        if ($subtractMonths !== 0) {
            $cycleStart = $today->copy()->subMonths(abs($subtractMonths))->day($cycleDay);
        } else {

            if ($today->day < $cycleDay) {
                $cycleStart = $today->copy()->subMonth()->day($cycleDay);
            } else {
                $cycleStart = $today->copy()->day($cycleDay);
            }
        }

        $cycleEnd = $cycleStart->copy()->addMonth()->day($cycleDay)->subDay();

        return [
            'start_date' => $cycleStart->format('Y-m-d'),
            'end_date' => $cycleEnd->format('Y-m-d'),
        ];
    }
}
if (!function_exists('getFutureCycle')) {


    function getFutureCycle($lastDate): array
    {
        $carbon = Carbon::parse($lastDate);
        $cycleStart = $carbon->copy();
        $cycleEnd = $cycleStart->copy()->addMonth();
        return [
            'start_date' => $cycleStart->format('Y-m-d'),
            'end_date' => $cycleEnd->format('Y-m-d'),
        ];
    }
}
if (!function_exists('getPerviousMonthCycle')) {


function getPerviousMonthCycle($cycleDay = 15, $subtractMonths = 0, $getPrevious = true): array
{
    $today = Carbon::today();

    // Determine the start of the current cycle
    if ($subtractMonths !== 0) {
        $cycleStart = $today->copy()->subMonths(abs($subtractMonths))->day($cycleDay);
    } else {
        if ($today->day < $cycleDay) {
            $cycleStart = $today->copy()->subMonth()->day($cycleDay);
        } else {
            $cycleStart = $today->copy()->day($cycleDay);
        }
    }

    $cycleEnd = $cycleStart->copy()->addMonth()->day($cycleDay)->subDay();

    // Initialize previous period dates
    $previousCycleStart = null;
    $previousCycleEnd = null;

    // Calculate previous period if requested
    if ($getPrevious) {
        $previousCycleStart = $cycleStart->copy()->subMonth()->day($cycleDay);
        $previousCycleEnd = $cycleStart->copy()->subDay()->format('Y-m-d'); // End of previous period is day before current start
    }

    return [

        'previous_start_date' => $previousCycleStart ? $previousCycleStart->format('Y-m-d') : null,
        'previous_end_date' => $previousCycleEnd ? $previousCycleEnd : null,
    ];
}
}
if (!function_exists('findBracketsBeforeAndIncluding')) {

    function findBracketsBeforeAndIncluding($data, $value): array
    {
        $bracketsBefore = [];

        foreach ($data as $item) {
            $min = intval($item['min']);
            $max = intval($item['max']);

            $bracketsBefore[] = $item;
            if ($value >= $min && $value <= $max) {
                break; // Stop the loop when the target bracket is found
            }

        }
        return $bracketsBefore;
    }
}
if (!function_exists('getDaysBetweenTwoDates')) {

    function getDaysBetweenTwoDates($lowestDate, $highestDate): string
    {
        $date1 = Carbon::parse($lowestDate);
        $date2 = Carbon::parse($highestDate);
        return $date1->diffInDays($date2);
    }
}


if (!function_exists('replaceFirstCharacter')) {

    function replaceFirstCharacter($from, $to, $content)
    {
        $from = '/' . preg_quote($from, '/') . '/';
        if (is_string($to)) {
            $to = "'$to'";
        }
        return preg_replace($from, $to, $content, 1);
    }
}

if (!function_exists('getSqlQuery')) {

    function getSqlQuery($query)
    {
        $data = $query->getBindings();
        $string = $query->toSql();
        foreach ($data as $datum) {
            $string = replaceFirstCharacter('?', $datum, $string);
        }
        return $string;
    }
}




if (!function_exists('addOrdinalSuffix')) {
    /**
     * Add ordinal suffix to a given day.
     *
     * @param int $day
     * @return string
     */
    function addOrdinalSuffix($day)
    {
        if (!in_array(($day % 100), [11, 12, 13])) {
            switch ($day % 10) {
                case 1:
                    return $day . 'st';
                case 2:
                    return $day . 'nd';
                case 3:
                    return $day . 'rd';
            }
        }
        return $day . 'th';
    }
}

