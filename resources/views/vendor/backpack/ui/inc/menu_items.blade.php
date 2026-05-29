{{-- This file is used for menu items by any Backpack v7 theme --}}

{{-- Dashboard de Backpack oculto — el admin entra directo al builder --}}

<li class="nav-title px-3 mt-3 text-uppercase text-muted small">
    Decorador Virtual
</li>

<x-backpack::menu-item
    title="Builder"
    icon="la la-magic"
    :link="backpack_url('builder')"
/>

<x-backpack::menu-item
    title="Categorías"
    icon="la la-folder-open"
    :link="backpack_url('builder/material-categories')"
/>

<x-backpack::menu-item
    title="Materiales"
    icon="la la-gem"
    :link="backpack_url('builder/materials')"
/>

<x-backpack::menu-item
    title="Ambientes"
    icon="la la-image"
    :link="backpack_url('builder/environments')"
/>

<x-backpack::menu-item
    title="Zonas"
    icon="la la-object-group"
    :link="backpack_url('builder/environment-zones')"
/>

{{-- Leads oculto del menú --}}
{{--
<li class="nav-title px-3 mt-3 text-uppercase text-muted small">
    Ventas
</li>

<x-backpack::menu-item
    title="Leads / Solicitudes"
    icon="la la-user-plus"
    :link="backpack_url('builder/leads')"
/>
--}}
