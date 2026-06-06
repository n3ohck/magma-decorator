<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Builder\BuilderController;
use App\Http\Controllers\Admin\Builder\MaterialCategoryBuilderController;
use App\Http\Controllers\Admin\Builder\MaterialBuilderController;
use App\Http\Controllers\Admin\Builder\EnvironmentBuilderController;
use App\Http\Controllers\Admin\Builder\EnvironmentZoneBuilderController;
use App\Http\Controllers\Admin\Builder\EnvironmentZoneGroupBuilderController;
use App\Http\Controllers\Admin\Builder\LeadBuilderController;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\CRUD.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    /*
|--------------------------------------------------------------------------
| Builder del Decorador Virtual
|--------------------------------------------------------------------------
| Estas rutas viven dentro de Backpack y quedan protegidas por su login.
*/

    Route::get('builder', [BuilderController::class, 'index'])
        ->name('builder.index');

    /*
    |--------------------------------------------------------------------------
    | Categorías de materiales
    |--------------------------------------------------------------------------
    */

    Route::get('builder/material-categories', [MaterialCategoryBuilderController::class, 'index'])
        ->name('builder.material-categories.index');

    Route::post('builder/material-categories', [MaterialCategoryBuilderController::class, 'store'])
        ->name('builder.material-categories.store');

    Route::post('builder/material-categories/{materialCategory}', [MaterialCategoryBuilderController::class, 'update'])
        ->name('builder.material-categories.update');

    Route::delete('builder/material-categories/{materialCategory}', [MaterialCategoryBuilderController::class, 'destroy'])
        ->name('builder.material-categories.destroy');

    /*
    |--------------------------------------------------------------------------
    | Materiales
    |--------------------------------------------------------------------------
    */

    Route::get('builder/materials', [MaterialBuilderController::class, 'index'])
        ->name('builder.materials.index');

    Route::post('builder/materials', [MaterialBuilderController::class, 'store'])
        ->name('builder.materials.store');

    Route::post('builder/materials/{material}', [MaterialBuilderController::class, 'update'])
        ->name('builder.materials.update');

    Route::delete('builder/materials/{material}', [MaterialBuilderController::class, 'destroy'])
        ->name('builder.materials.destroy');

    /*
    |--------------------------------------------------------------------------
    | Ambientes
    |--------------------------------------------------------------------------
    */

    Route::get('builder/environments', [EnvironmentBuilderController::class, 'index'])
        ->name('builder.environments.index');

    Route::post('builder/environments', [EnvironmentBuilderController::class, 'store'])
        ->name('builder.environments.store');

    Route::post('builder/environments/{environment}', [EnvironmentBuilderController::class, 'update'])
        ->name('builder.environments.update');

    Route::delete('builder/environments/{environment}', [EnvironmentBuilderController::class, 'destroy'])
        ->name('builder.environments.destroy');

    /*
    |--------------------------------------------------------------------------
    | Zonas de ambientes
    |--------------------------------------------------------------------------
    */

    Route::get('builder/environment-zones', [EnvironmentZoneBuilderController::class, 'index'])
        ->name('builder.environment-zones.index');

    Route::post('builder/environment-zones', [EnvironmentZoneBuilderController::class, 'store'])
        ->name('builder.environment-zones.store');

    Route::post('builder/environment-zones/{environmentZone}', [EnvironmentZoneBuilderController::class, 'update'])
        ->name('builder.environment-zones.update');

    Route::delete('builder/environment-zones/{environmentZone}', [EnvironmentZoneBuilderController::class, 'destroy'])
        ->name('builder.environment-zones.destroy');

    /*
    |--------------------------------------------------------------------------
    | Grupos de zonas
    |--------------------------------------------------------------------------
    */

    Route::get('builder/environment-zone-groups', [EnvironmentZoneGroupBuilderController::class, 'index'])
        ->name('builder.environment-zone-groups.index');

    Route::post('builder/environment-zone-groups', [EnvironmentZoneGroupBuilderController::class, 'store'])
        ->name('builder.environment-zone-groups.store');

    Route::post('builder/environment-zone-groups/{environmentZoneGroup}', [EnvironmentZoneGroupBuilderController::class, 'update'])
        ->name('builder.environment-zone-groups.update');

    Route::delete('builder/environment-zone-groups/{environmentZoneGroup}', [EnvironmentZoneGroupBuilderController::class, 'destroy'])
        ->name('builder.environment-zone-groups.destroy');

    /*
    |--------------------------------------------------------------------------
    | Leads
    |--------------------------------------------------------------------------
    */

    Route::get('builder/leads', [LeadBuilderController::class, 'index'])
        ->name('builder.leads.index');

    Route::post('builder/leads/{lead}', [LeadBuilderController::class, 'update'])
        ->name('builder.leads.update');

    Route::delete('builder/leads/{lead}', [LeadBuilderController::class, 'destroy'])
        ->name('builder.leads.destroy');

}); // this should be the absolute last line of this file

/**
 * DO NOT ADD ANYTHING HERE.
 */
