<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('products:sync-from-sheets')->hourly();
Schedule::command('products:sync-images')->hourly();
