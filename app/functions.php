<?php

use Illuminate\Support\Facades\Auth;

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
