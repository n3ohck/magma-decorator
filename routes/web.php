<?php

use App\Http\Controllers\AIRenderController;
use App\Http\Controllers\Admin\SAMMaskController;
use App\Http\Controllers\DecoratorController;
use App\Http\Controllers\DesignLeadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('decorator.index');
});

// Backpack dashboard → redirect al builder del decorador
Route::get('/admin/dashboard', function () {
    return redirect('/admin/builder');
});

Route::get('/decorador', [DecoratorController::class, 'index'])
    ->name('decorator.index');

Route::get('/decorador/{environment:slug}', [DecoratorController::class, 'show'])
    ->name('decorator.show');

Route::post('/decorador/leads', [DesignLeadController::class, 'store'])
    ->name('decorator.leads.store');

// AI Render — async, client polls for status
Route::post('/decorador/ai-render', [AIRenderController::class, 'create'])
    ->name('ai-render.create');

Route::get('/decorador/ai-render/{id}/status', [AIRenderController::class, 'status'])
    ->name('ai-render.status');

// SAM automatic mask generation (admin)
Route::post('/admin/builder/sam-mask', [SAMMaskController::class, 'generate'])
    ->name('admin.sam-mask');
