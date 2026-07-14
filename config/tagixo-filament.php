<?php

use Ccast\TagixoFilament\Filament\Resources\Forms\FormResource;
use Ccast\TagixoFilament\Filament\Resources\LayoutResource;
use Ccast\TagixoFilament\Filament\Resources\Mails\MailResource;
use Ccast\TagixoFilament\Filament\Resources\MediaResource;
use Ccast\TagixoFilament\Filament\Resources\Menus\MenuResource;
use Ccast\TagixoFilament\Filament\Resources\Pages\PageResource;
use Ccast\TagixoFilament\Filament\Resources\PdfTemplates\PdfTemplateResource;
use Ccast\TagixoFilament\Filament\Resources\Popups\PopupResource;
use Ccast\TagixoFilament\Filament\Resources\Sliders\SliderResource;

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Resources (Builders)
    |--------------------------------------------------------------------------
    |
    | Each entry below registers one Tagixo builder as a Filament resource
    | (its own navigation item + list/create/edit screens).
    |
    | To HIDE a builder from the panel, simply COMMENT OUT its line. The
    | underlying feature keeps working everywhere else (rendering, the visual
    | builder, the database) — you only remove its dedicated admin section.
    |
    | The order of this array is the order resources are registered.
    |
    */
    'resources' => [

        PageResource::class,
        LayoutResource::class,
        MenuResource::class,
        FormResource::class,
        SliderResource::class,
        MailResource::class,
        PopupResource::class,
        PdfTemplateResource::class,

        /*
        | Optional resources — disabled by default.
        |
        | Uncomment to enable, or use the equivalent ->withMediaGallery()
        | plugin method. Enabling both is safe (registered only once).
        */
        // MediaResource::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Policies
    |--------------------------------------------------------------------------
    |
    | Optional per-model policy map, registered with Gate::policy() at boot.
    | Each resource follows its model's policy: navigation and page access
    | use viewAny(), record operations use the matching ability. The Theme
    | Builder page follows the Layout policy. Models without an entry stay
    | open to any panel user (Filament allows when no policy is registered).
    |
    | NOTE: define the ability methods explicitly (viewAny, view, create,
    | update, delete, ...). Laravel skips a policy's before() when the
    | ability method is missing, and Filament allows in that case — a
    | policy with only before() would not restrict anything.
    |
    | Example — restrict every builder to a custom gate:
    | 'policies' => [
    |     \Ccast\Tagixo\Models\Page::class => \App\Policies\TagixoResourcePolicy::class,
    |     \Ccast\Tagixo\Models\Layout::class => \App\Policies\TagixoResourcePolicy::class,
    | ],
    |
    */
    'policies' => [],

    /*
    |--------------------------------------------------------------------------
    | Form Mapper Extensions
    |--------------------------------------------------------------------------
    |
    | Map Tagixo runtime schema field/wrapper types to custom Filament
    | mapper classes.
    |
    | Example:
    | 'fields' => [
    |     'money' => \App\VisualBuilder\Form\MoneyFieldMapper::class,
    | ],
    |
    */
    'form_mapper' => [
        'fields' => [],
        'wrappers' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pages resource
    |--------------------------------------------------------------------------
    |
    | `sitemap_action`: show the "Generate Sitemap" header action on the Pages
    | list. Turn it off where a single global sitemap doesn't apply (e.g. a
    | per-tenant demo).
    |
    */
    'pages' => [
        'sitemap_action' => (bool) env('TAGIXO_FILAMENT_SITEMAP_ACTION', true),
    ],
];
