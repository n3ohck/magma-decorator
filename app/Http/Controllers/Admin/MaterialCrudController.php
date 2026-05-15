<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\MaterialRequest;
use App\Models\Material;
use App\Models\MaterialCategory;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class MaterialCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(Material::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/material');
        CRUD::setEntityNameStrings('material', 'materiales');
    }

    protected function setupListOperation()
    {
        CRUD::column('id')
            ->label('ID');

        CRUD::column('thumbnail_image')
            ->label('Miniatura')
            ->type('image')
            ->disk('public')
            ->height('60px')
            ->width('60px');

        CRUD::column('name')
            ->label('Nombre');

        CRUD::column('material_category_id')
            ->label('Categoría')
            ->type('select')
            ->entity('category')
            ->model(MaterialCategory::class)
            ->attribute('name');

        CRUD::column('sku')
            ->label('SKU');

        CRUD::column('finish')
            ->label('Acabado');

        CRUD::column('base_color')
            ->label('Color base');

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
        CRUD::setValidation(MaterialRequest::class);

        CRUD::field('material_category_id')
            ->label('Categoría')
            ->type('select')
            ->entity('category')
            ->model(MaterialCategory::class)
            ->attribute('name');

        CRUD::field('name')
            ->label('Nombre')
            ->type('text');

        CRUD::field('slug')
            ->label('Slug')
            ->type('text')
            ->hint('Ejemplo: calacatta-gold, granito-negro, cuarzo-blanco.');

        CRUD::field('sku')
            ->label('SKU')
            ->type('text');

        CRUD::field('origin_country')
            ->label('País de origen')
            ->type('text');

        CRUD::field('finish')
            ->label('Acabado')
            ->type('select_from_array')
            ->options([
                'pulido' => 'Pulido',
                'mate' => 'Mate',
                'satinado' => 'Satinado',
                'brillante' => 'Brillante',
                'texturizado' => 'Texturizado',
                'natural' => 'Natural',
            ])
            ->allows_null(true);

        CRUD::field('base_color')
            ->label('Color base')
            ->type('text')
            ->hint('Ejemplo: Blanco, Negro, Beige, Gris, Dorado.');

        CRUD::field('short_description')
            ->label('Descripción corta')
            ->type('textarea');

        CRUD::field('description')
            ->label('Descripción completa')
            ->type('ckeditor');

        CRUD::field('texture_image')
            ->label('Textura del material')
            ->type('upload')
            ->upload(true)
            ->disk('public');

        CRUD::field('thumbnail_image')
            ->label('Miniatura')
            ->type('upload')
            ->upload(true)
            ->disk('public');

        CRUD::field('default_scale')
            ->label('Escala por defecto')
            ->type('number')
            ->default(1)
            ->attributes([
                'step' => '0.01',
                'min' => '0.1',
            ]);

        CRUD::field('default_rotation')
            ->label('Rotación por defecto')
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

        CRUD::field('is_featured')
            ->label('Material destacado')
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
