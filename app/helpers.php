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
            $cycleEnd = $cycleStart->copy()->addMonth()->day($cycleDay);
        } else {
            $cycleEnd = $cycleStart->copy()->addMonths(1)->day($cycleDay);
        }

        return [
            'start_date' => $cycleStart->format('Y-m-d'),
            'end_date' => $cycleEnd->format('Y-m-d'),
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
