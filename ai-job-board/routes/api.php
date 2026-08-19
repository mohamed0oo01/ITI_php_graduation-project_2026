<?php

use App\Http\Controllers\AiTestController;
use Illuminate\Support\Facades\Route;

Route::post('/ai/test', [AiTestController::class, 'test']);