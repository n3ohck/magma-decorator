<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DecoratorController;
use App\Http\Controllers\DesignLeadController;

Route::get('/', function () {
    return redirect()->route('decorator.index');
});

Route::get('/decorador', [DecoratorController::class, 'index'])
    ->name('decorator.index');

Route::get('/decorador/{environment:slug}', [DecoratorController::class, 'show'])
    ->name('decorator.show');

Route::post('/decorador/leads', [DesignLeadController::class, 'store'])
    ->name('decorator.leads.store');
