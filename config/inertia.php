<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Server Side Rendering
    |--------------------------------------------------------------------------
    |
    | These options configures if and how Inertia uses Server Side Rendering
    | to pre-render each initial request made to your application.
    |
    */

    'ssr' => [
        'enabled' => false, // Disabled - client-side rendering only
        'url' => 'http://127.0.0.1:13714',

        // Automatically enable SSR for Bun runtime
        'runtime' => env('INERTIA_SSR_RUNTIME', 'bun'),

        // Verify runtime exists before starting SSR server
        'ensure_runtime_exists' => (bool) env('INERTIA_SSR_ENSURE_RUNTIME_EXISTS', false),

        // Throw exception on SSR error instead of fallback (for testing)
        'throw_on_error' => (bool) env('INERTIA_SSR_THROW_ON_ERROR', false),

        // Check if SSR bundle exists before dispatching request
        'ensure_bundle_exists' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Testing
    |--------------------------------------------------------------------------
    |
    | The value given here is used to determine the page assertion limit when
    | using the `assertComponent` method in your tests.
    |
    */

    'testing' => [
        'ensure_valid_page_data' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | History Encryption
    |--------------------------------------------------------------------------
    |
    | The history encryption configuration is used to encrypt the history
    | state data when using the `remember` feature.
    |
    */

    'history_encryption' => [
        'enabled' => true,
        'key' => env('INERTIA_HISTORY_ENCRYPTION_KEY'),
    ],

];
