<?php

namespace IanRothmann\InertiaApp\Facades;

use Illuminate\Support\Facades\Facade;

class InertiaApp extends Facade
{
    public static function getFacadeAccessor(){
        return 'inertia-app';
    }
}