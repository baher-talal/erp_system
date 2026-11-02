<?php

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! User::query()->exists()) {
        return redirect()->route('setup');
    }

    if (! Auth::check()) {
        return redirect()->route('login');
    }

    if (! Auth::user()->is_active) {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }

    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/setup', 'AuthController@showSetup')->name('setup');
    Route::post('/setup', 'AuthController@setup')->middleware('throttle:5,1');
    Route::get('/login', 'AuthController@showLogin')->name('login');
    Route::post('/login', 'AuthController@login')->middleware('throttle:5,1');
});

Route::post('/logout', 'AuthController@logout')->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('workspace')->group(function () {
    Route::get('/dashboard', 'ErpController@dashboard');
    Route::get('/reports', 'ErpController@report');
    Route::post('/employees/account', 'ErpController@createEmployeeAccount');
    Route::put('/employees/{id}/account', 'ErpController@updateEmployeeAccount');
    Route::post('/attendance/clock', 'ErpController@clock');
    Route::post('/inventory/{id}/adjust', 'ErpController@adjustInventory');
    Route::post('/files/upload', 'ErpController@upload');
    Route::get('/files/{id}/download', 'ErpController@download');
    Route::get('/{module}', 'ErpController@index');
    Route::post('/{module}', 'ErpController@store');
    Route::put('/{module}/{id}', 'ErpController@update');
    Route::delete('/{module}/{id}', 'ErpController@destroy');
});
