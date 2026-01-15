<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

\Illuminate\Support\Facades\Schedule::command('pundit:sync-fpl')->everyThirtyMinutes();
\Illuminate\Support\Facades\Schedule::command('pundit:generate')->twiceDaily(9, 21);

