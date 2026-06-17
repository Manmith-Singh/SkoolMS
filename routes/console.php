<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment('Stay hungry. Stay foolish.');
})->purpose('Display an inspiring quote');

Schedule::command('tenants:auto-suspend')->dailyAt('00:00');
