<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome'); // Ou return response()->json(['status' => 'API Online']);
});
