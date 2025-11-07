<?php

return [
    'hot_file' => public_path('hot'),

    'build_path' => 'build',

    'commands' => [
        'dev' => 'vite',
        'build' => 'vite build',
    ],

    'vite_public_path' => env('VITE_PUBLIC_PATH'),
];
