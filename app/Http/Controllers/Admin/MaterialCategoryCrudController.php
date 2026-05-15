<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\MaterialCategoryRequest;
use App\Models\MaterialCategory;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class MaterialCategoryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(MaterialCategory::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/material-category');
        CRUD::setEntityNameStrings('categoría de material', 'categorías de materiales');
    }

    protected function setupListOperation()
    {
        CRUD::column('id')
            ->label('ID');

        CRUD::column('name')
            ->label('Nombre');

        CRUD::column('slug')
            ->label('Slug');

        CRUD::column('cover_image')
            ->label('Imagen')
            ->type('image')
            ->disk('public')
            ->height('60px')
            ->width('80px');

        CRUD::column('is_active')
            ->label('Activa')
            ->type('boolean');

        CRUD::column('sort_order')
            ->label('Orden');

        CRUD::column('created_at')
            ->label('Creada')
            ->type('datetime');
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(MaterialCategoryRequest::class);

        CRUD::field('name')
            ->label('Nombre')
            ->type('text');

        CRUD::field('slug')
            ->label('Slug')
            ->type('text')
            ->hint('Ejemplo: marmol, cuarzo, cuarcita, granito.');

        CRUD::field('description')
            ->label('Descripción')
            ->type('textarea');

        CRUD::field('cover_image')
            ->label('Imagen de portada')
            ->type('upload')
            ->upload(true)
            ->disk('public');

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
