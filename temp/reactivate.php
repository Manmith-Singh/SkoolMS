<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$t = App\Models\Master\Tenant::where('subdomain', 'greenfield')->first();
if ($t) {
    $t->status = 'active';
    $t->save();
    echo "Reactivated: {$t->name} ({$t->subdomain}) => status: {$t->status}\n";
} else {
    echo "Tenant not found\n";
}
