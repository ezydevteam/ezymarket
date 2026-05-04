<?php

namespace App\Http\Controllers\Admin\System;

use App\Cache\CacheManager;
use App\Http\Controllers\Controller;

class InfoController extends Controller
{
    public function index()
    {
        $system['application']['name'] = config('system.product.alias');
        $system['application']['version'] = config('system.product.version');
        $system['application']['laravel'] = app()->version();
        $system['application']['timezone'] = config('app.timezone');
        $system['server'] = $_SERVER;
        $system['server']['php'] = phpversion();
        $system = json_decode(json_encode($system));
        return view('admin.system.info', ['system' => $system]);
    }

    public function cache()
    {
        CacheManager::clearAllCaches();
        removeFile(storage_path('logs/laravel.log'));
        toastr()->success(translate('All Cache Cleared Successfully'));
        return back();
    }
}


















