<?php

use Illuminate\Support\Facades\Route;

// Empty entrypoint — real routes are in master.php and tenant.php,
// registered by TenantServiceProvider.
Route::get('/up', fn () => response()->json(['status' => 'ok']));
