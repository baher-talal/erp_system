<?php

return [
    'permission_modules' => [
        'employees' => ['view', 'write', 'delete'],
        'departments' => ['view', 'write', 'delete'],
        'tasks' => ['view', 'write', 'delete'],
        'projects' => ['view', 'write', 'delete'],
        'clients' => ['view', 'write', 'delete'],
        'payments' => ['view', 'write', 'delete'],
        'files' => ['view', 'write', 'delete'],
        'reports' => ['view'],
        'chat' => ['view', 'write', 'delete'],
        'attendance' => ['view', 'write', 'delete'],
        'payroll' => ['view', 'write', 'delete'],
        'calendar' => ['view', 'write', 'delete'],
        'inventory' => ['view', 'write', 'delete'],
        'personal_tasks' => ['view', 'write', 'delete'],
    ],

    'default_permissions' => [
        'employees' => ['view'],
        'departments' => ['view'],
        'tasks' => ['view', 'write', 'delete'],
        'projects' => ['view', 'write', 'delete'],
        'clients' => ['view', 'write', 'delete'],
        'payments' => [],
        'files' => ['view', 'write'],
        'reports' => [],
        'chat' => ['view', 'write', 'delete'],
        'attendance' => ['view', 'write'],
        'payroll' => [],
        'calendar' => ['view', 'write', 'delete'],
        'inventory' => ['view', 'write', 'delete'],
        'personal_tasks' => ['view', 'write', 'delete'],
    ],
];
