<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'name' => 'JobFlow API',
    'version' => 'v1',
    'authentication' => 'Bearer token (Sanctum)',
    'documentation' => 'https://github.com/davinovakoski11/jobflow#api',
]));
