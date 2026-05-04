<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Traits\HandlesValidation;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class CronJobController extends Controller
{
    use HandlesValidation;

    /*
    `* Display Cron Job Settings
    * @return View
    */
    public function index(): View
    {
        $cronjobSettings = settings('cronjob') ?: (object) [];
        return view('admin.system.cronjob', compact('cronjobSettings'));
    }

    /*
    `* Generate Cron Job Key
     * @return JsonResponse
    */
    public function generateKey(): JsonResponse
    {
        Settings::updateSettings('cronjob', ['key' => hash_encode(time())]);

        return $this->successJson('Cron Job key generated successfully');
    }

    /*
    `* Remove Cron Job Key
     * @return JsonResponse
    */
    public function removeKey(): JsonResponse
    {
        if (@settings('cronjob')->key) {
            Settings::updateSettings('cronjob', ['key' => '']);
        }
        return $this->successJson('Cron Job key removed successfully');
    }
}


















