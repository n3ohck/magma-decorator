<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\LeadRequest;
use App\Models\DesignSession;
use App\Models\Lead;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class LeadCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(Lead::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/lead');
        CRUD::setEntityNameStrings('solicitud', 'solicitudes');
    }

    protected function setupListOperation()
    {
        CRUD::column('id')
            ->label('ID');

        CRUD::column('name')
            ->label('Nombre');

        CRUD::column('phone')
            ->label('Teléfono');

        CRUD::column('email')
            ->label('Correo');

        CRUD::column('city')
            ->label('Ciudad');

        CRUD::column('project_type')
            ->label('Tipo de proyecto');

        CRUD::column('preferred_contact_method')
            ->label('Contacto preferido');

        CRUD::column('status')
            ->label('Estatus');

        CRUD::column('created_at')
            ->label('Fecha')
            ->type('datetime');
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(LeadRequest::class);

        CRUD::field('design_session_id')
            ->label('Diseño / sesión')
            ->type('select')
            ->entity('designSession')
            ->model(DesignSession::class)
            ->attribute('public_uuid')
            ->allows_null(true);

        CRUD::field('name')
            ->label('Nombre')
            ->type('text');

        CRUD::field('phone')
            ->label('Teléfono')
            ->type('text');

        CRUD::field('email')
            ->label('Correo')
            ->type('email');

        CRUD::field('city')
            ->label('Ciudad')
            ->type('text');

        CRUD::field('project_type')
            ->label('Tipo de proyecto')
            ->type('select_from_array')
            ->options([
                'kitchen' => 'Cocina',
                'bathroom' => 'Baño',
                'residential' => 'Residencial',
                'commercial' => 'Comercial',
                'exterior' => 'Exterior',
                'other' => 'Otro',
            ])
            ->allows_null(true);

        CRUD::field('preferred_contact_method')
            ->label('Medio de contacto preferido')
            ->type('select_from_array')
            ->options([
                'whatsapp' => 'WhatsApp',
                'phone' => 'Llamada',
                'email' => 'Correo',
            ])
            ->allows_null(true);

        CRUD::field('message')
            ->label('Mensaje')
            ->type('textarea');

        CRUD::field('status')
            ->label('Estatus')
            ->type('select_from_array')
            ->options([
                'new' => 'Nueva',
                'contacted' => 'Contactada',
                'quoted' => 'Cotizada',
                'won' => 'Ganada',
                'lost' => 'Perdida',
            ])
            ->default('new');
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
