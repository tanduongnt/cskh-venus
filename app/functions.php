<?php

use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

if (!function_exists('can')) {
    /**
     * Check if a user has permission or not
     *
     * @param string|array $permission The string or array of permission to check
     * @return boolean Return true or false
     */
    function can($permission)
    {
        if (is_iterable($permission)) {
            return Auth::user()->hasAnyPermission($permission) || Auth::user()->can('do anything');
        }
        return Auth::user()->can($permission);
    }
}

if (!function_exists('notSuperAdmin')) {
    function notSuperAdmin()
    {
        return !Auth::user()->can('do anything');
    }
}

if (!function_exists('getBetweenDates')) {
    function getBetweenDates($startDate, $endDate)
    {
        $rangArray = [];
        $startDate = strtotime($startDate);
        $endDate = strtotime($endDate);
        for ($currentDate = $startDate; $currentDate <= $endDate; $currentDate += (86400)) {
            $date = date('Y-m-d', $currentDate);
            $rangArray[] = $date;
        }
        return $rangArray;
    }
}

if (!function_exists('groupDatesByMonth')) {
    function groupDatesByMonth($startDate, $endDate)
    {
        $months = collect();
        $dates = getBetweenDates($startDate, $endDate);
        foreach ($dates as $date) {
            $timestamp = strtotime($date);
            $month = date('m', $timestamp);
            if (!$months->has($month)) {
                $months->put($month, collect());
                //$months[$month] = collect();
            }
            $months->get($month)->push($date);
        }
        return $months;
    }
}

if (!function_exists('moneyFormat')) {
    function moneyFormat($number)
    {
        return number_format($number, 0, ',', '.');
    }
}
