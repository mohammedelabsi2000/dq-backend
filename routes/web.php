<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Public Routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
require __DIR__ . '/auth.php';

// Fallback Route
Route::fallback(function () {
    return view('errors.404');
});