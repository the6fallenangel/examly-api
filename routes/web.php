<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'status' => true,
        'name' => config('app.name').' is running.',
        'version' => '1.0.0',
    ]);
});
