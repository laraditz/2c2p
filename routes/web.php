<?php

use Illuminate\Support\Facades\Route;
use Laraditz\Twoc2p\Http\Controllers\Twoc2pController;

Route::post('/backend', [Twoc2pController::class, 'backend'])->name('twoc2p.backend');
