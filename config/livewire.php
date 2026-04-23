<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Class Namespace
    |--------------------------------------------------------------------------
    |
    | This value sets the root namespace for Livewire component classes to be
    | used when automagically generating a fully-qualified class name for the
    | component based on its "name".
    |
    */
    'class_namespace' => 'App\\Livewire',

    /*
    |--------------------------------------------------------------------------
    | View Path
    |--------------------------------------------------------------------------
    |
    | This value is the path, relative to your resources/views directory
    | where Livewire component Blade templates are stored.
    |
    */
    'view_path' => 'livewire',

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | The view that will be rendered around Livewire component views. This
    | exists so that you don't have to repeat the same layout in every single
    | component included as a Livewire component.
    |
    */
    'layout' => 'layouts.guest',

    /*
    |--------------------------------------------------------------------------
    | Auto-refresh DOM
    |--------------------------------------------------------------------------
    |
    | This value determines if the DOM should be refreshed when Livewire
    | is processing an action.
    |
    */
    'morphdom' => true,

];
