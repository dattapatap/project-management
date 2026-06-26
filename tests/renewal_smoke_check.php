<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CsdRenewal;
use App\Models\Clients;
use App\Models\User;
use App\Services\Csd\CsdClientResolverService;
use App\Services\Csd\CsdRenewalService;

$admin = User::role('Admin')->first();
$client = Clients::query()
    ->where(fn ($q) => $q->where('status', 'Matured')->orWhere('is_active', true))
    ->first();

$out = [
    'admin' => $admin?->email,
    'client' => $client ? ['id' => $client->id, 'name' => $client->name] : null,
];

if ($admin && $client) {
    $resolver = app(CsdClientResolverService::class);
    $service = app(CsdRenewalService::class);

    $out['selectable_count'] = $resolver->getSelectableClients($admin)->count();
    $out['can_access'] = $resolver->userCanAccessClient($admin, (int) $client->id);

    $renewal = $service->create([
        'client' => $client->id,
        'renewal_type' => 'domain',
        'title' => 'Smoke test domain renewal',
        'due_date' => now()->addMonth()->toDateString(),
        'amount' => 1500,
    ], $admin);

    $out['created_id'] = $renewal->id;
    $out['listed'] = $service->listQuery($admin)->where('id', $renewal->id)->exists();
    $out['found_via_show'] = $service->findForUser($renewal->id, $admin)->id;

    $renewal->delete();
    $out['cleaned_up'] = true;
}

echo json_encode($out, JSON_PRETTY_PRINT) . PHP_EOL;
