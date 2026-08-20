<?php

namespace App\Http\Requests;

use Auth;

class RequestUtils
{
    public static function havePermission($route_name, $permission = 'any')
    {
        $permission = $permission == 'any' ? 'list,create,edit,delete' : $permission;
        $list = explode(',', $permission);

        $permissions = '';

        foreach ($list as $p) {
            $permissions .= strlen($permissions) > 1 ? ',' : '';
            $permissions .= $route_name.'-'.$p;
        }

        return Auth::user()->ability('sysadmin', $permissions);
    }

    public static function isSysadmin()
    {
        return Auth::user()->hasRole('sysadmin');
    }
}
