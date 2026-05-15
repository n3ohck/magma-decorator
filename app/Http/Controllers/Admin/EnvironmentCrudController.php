<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\EnvironmentRequest;
use App\Models\Environment;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class EnvironmentCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(Environment::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/environment');
        CRUD::setEntityNameStrings('ambiente', 'ambientes');
    }

    protected function setupListOperation()
    {
        CRUD::column('id')
            ->label('ID');

        CRUD::column('preview_image')
            ->label('Preview')
            ->type('image')
            ->disk('public')
            ->height('70px')
            ->width('100px');

        CRUD::column('name')
            ->label('Nombre');

        CRUD::column('slug')
            ->label('Slug');

        CRUD::column('type')
            ->label('Tipo');

        CRUD::column('canvas_width')
            ->label('Ancho');

        CRUD::column('canvas_height')
            ->label('Alto');

        CRUD::column('is_featured')
            ->label('Destacado')
            ->type('boolean');

        CRUD::column('is_active')
            ->label('Activo')
            ->type('boolean');

        CRUD::column('sort_order')
            ->label('Orden');
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(EnvironmentRequest::class);

        CRUD::field('name')
            ->label('Nombre')
            ->type('text');

        CRUD::field('slug')
            ->label('Slug')
            ->type('text')
            ->hint('Ejemplo: cocina-premium-01, bano-moderno-01.');

        CRUD::field('type')
            ->label('Tipo de ambiente')
            ->type('select_from_array')
            ->options([
                'kitchen' => 'Cocina',
                'bathroom' => 'Baño',
                'living_room' => 'Sala',
                'commercial' => 'Comercial',
                'exterior' => 'Exterior',
                'office' => 'Oficina',
                'other' => 'Otro',
            ])
            ->allows_null(true);

        CRUD::field('description')
            ->label('Descripción')
            ->type('textarea');

        CRUD::field('base_image')
            ->label('Imagen base del ambiente')
            ->type('upload')
            ->upload(true)
            ->disk('public');

        CRUD::field('preview_image')
            ->label('Imagen preview')
            ->type('upload')
            ->upload(true)
            ->disk('public');

        CRUD::field('shadow_overlay_image')
            ->label('Overlay de sombras')
            ->type('upload')
            ->upload(true)
            ->disk('public');

        CRUD::field('light_overlay_image')
            ->label('Overlay de luces')
            ->type('upload')
            ->upload(true)
            ->disk('public');

        CRUD::field('canvas_width')
            ->label('Ancho del canvas')
            ->type('number')
            ->default(1600);

        CRUD::field('canvas_height')
            ->label('Alto del canvas')
            ->type('number')
            ->default(1000);

        CRUD::field('is_featured')
            ->label('Ambiente destacado')
            ->type('checkbox')
            ->default(false);

        CRUD::field('is_active')
            ->label('Activo')
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
