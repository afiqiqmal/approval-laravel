<?php

return [
    'enabled' => env('APPROVAL_ENABLED', true),

    'model' => [
        'reject_by' => \App\Models\User::class,
        'approve_by' => \App\Models\User::class,
    ],

    'tables' => [
        'name' => 'approvals',
        'reject_by' => 'users',
        'approve_by' => 'users',
    ],

    'when' => [ // need to approve
        'create' => true,
        'update' => true,
        'delete' => true
    ]
];
