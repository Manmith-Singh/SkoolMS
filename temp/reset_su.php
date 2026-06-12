<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$u = App\Models\Master\User::find(1);
if ($u) {
    $u->password = 'superadmin123';
    $u->save();
    echo "OK: superadmin@school.test / superadmin123\n";
} else {
    echo "No super admin found, creating one...\n";
    $u = App\Models\Master\User::create([
        'name' => 'Super Admin',
        'email' => 'superadmin@school.test',
        'password' => 'superadmin123',
        'role' => 'super_admin',
        'tenant_id' => null,
    ]);
    echo "Created: {$u->email}\n";
}
