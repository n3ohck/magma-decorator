<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\EnvironmentZoneRequest;
use App\Models\Environment;
use App\Models\EnvironmentZone;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class EnvironmentZoneCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(EnvironmentZone::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/environment-zone');
        CRUD::setEntityNameStrings('zona de ambiente', 'zonas de ambientes');
    }

    protected function setupListOperation()
    {
        CRUD::column('id')
            ->label('ID');

        CRUD::column('environment_id')
            ->label('Ambiente')
            ->type('select')
            ->entity('environment')
            ->model(Environment::class)
            ->attribute('name');

        CRUD::column('name')
            ->label('Nombre');

        CRUD::column('slug')
            ->label('Slug');

        CRUD::column('zone_type')
            ->label('Tipo');

        CRUD::column('mask_image')
            ->label('Máscara')
            ->type('image')
            ->disk('public')
            ->height('60px')
            ->width('80px');

        CRUD::column('is_active')
            ->label('Activa')
            ->type('boolean');

        CRUD::column('sort_order')
            ->label('Orden');
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(EnvironmentZoneRequest::class);

        CRUD::field('environment_id')
            ->label('Ambiente')
            ->type('select')
            ->entity('environment')
            ->model(Environment::class)
            ->attribute('name');

        CRUD::field('name')
            ->label('Nombre de la zona')
            ->type('text')
            ->hint('Ejemplo: Piso, Barra, Muro, Backsplash, Isla.');

        CRUD::field('slug')
            ->label('Slug')
            ->type('text')
            ->hint('Ejemplo: piso, barra, muro, backsplash.');

        CRUD::field('zone_type')
            ->label('Tipo de zona')
            ->type('select_from_array')
            ->options([
                'floor' => 'Piso',
                'wall' => 'Muro',
                'countertop' => 'Cubierta / Barra',
                'backsplash' => 'Backsplash',
                'island' => 'Isla',
                'facade' => 'Fachada',
                'shower_wall' => 'Muro de ducha',
                'other' => 'Otro',
            ])
            ->allows_null(true);

        CRUD::field('mask_image')
            ->label('Máscara PNG')
            ->type('upload')
            ->upload(true)
            ->disk('public')
            ->hint('PNG con la zona editable. Debe tener el mismo tamaño que la imagen base del ambiente.');

        CRUD::field('default_texture_scale')
            ->label('Escala de textura por defecto')
            ->type('number')
            ->default(1)
            ->attributes([
                'step' => '0.01',
                'min' => '0.1',
            ]);

        CRUD::field('default_texture_rotation')
            ->label('Rotación de textura por defecto')
            ->type('number')
            ->default(0)
            ->attributes([
                'step' => '0.01',
            ]);

        CRUD::field('default_opacity')
            ->label('Opacidad por defecto')
            ->type('number')
            ->default(1)
            ->attributes([
                'step' => '0.01',
                'min' => '0',
                'max' => '1',
            ]);

        CRUD::field('supports_perspective')
            ->label('Soporta perspectiva avanzada')
            ->type('checkbox')
            ->default(false);

        CRUD::field('polygon_points')
            ->label('Puntos del polígono')
            ->type('textarea')
            ->hint('Campo reservado para fase posterior. Puede quedar vacío.');

        CRUD::field('perspective_points')
            ->label('Puntos de perspectiva')
            ->type('textarea')
            ->hint('Campo reservado para fase posterior. Puede quedar vacío.');

        CRUD::field('is_active')
            ->label('Activa')
            ->type('checkbox')
            ->default(true);

        CRUD::field('sort_order')
            ->label('Orden')
            ->type('number')
            ->default(0);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
