<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Role-based access map
    |--------------------------------------------------------------------------
    |
    | Each role lists the route names it can access.  Patterns use a trailing
    | ".*" wildcard.  SuperAdmins and Admins always have full access and are
    | not listed here.
    |
    | Examples:
    |   'students.*'         → students.index, students.show, students.create, ...
    |   'fees.payments.*'    → fees.payments.index, fees.payments.store, ...
    |   '*'                  → every route (admin override)
    */

    'roles' => [

        'teacher' => [
            'dashboard',
            'classes.index', 'classes.show',
            'subjects.index', 'subjects.show',
            'exams.index', 'exams.show',
            'results.index', 'results.edit', 'results.update',
            'attendance.index', 'attendance.mark', 'attendance.store',
            'students.index', 'students.show',
            'teachers.index', 'teachers.show',
            'reports.index', 'reports.results', 'reports.attendance',
        ],

        'receptionist' => [
            'dashboard',
            'students.index', 'students.show', 'students.create', 'students.store',
            'students.edit',  'students.update', 'students.import', 'students.bulk-store',
            'teachers.index', 'teachers.show',
            'classes.index',  'classes.show',
            'attendance.index', 'attendance.mark', 'attendance.store',
            'reports.index', 'reports.attendance', 'reports.fees',
            'fees.payments.index', 'fees.payments.create', 'fees.payments.store',
            'fees.payments.show', 'fees.payments.receipt', 'fees.payments.destroy',
            'fees.index', 'fees.show',
        ],

        'student' => [
            'dashboard',
        ],
    ],

    /*
    | Roles that have full access to every tenant route (no need to enumerate).
    */
    'admin_roles' => ['super_admin', 'admin'],
];
