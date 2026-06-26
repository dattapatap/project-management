<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;

$results = [];

$tables = [
    'csd_client_assignments', 'csd_contact_persons', 'csd_communications',
    'csd_collection_followups', 'csd_change_requests', 'csd_support_tickets',
    'csd_amc_contracts', 'csd_renewals', 'csd_opportunities',
];

foreach ($tables as $table) {
    $results['tables'][$table] = Schema::hasTable($table);
}

$csdRoutes = collect(Route::getRoutes())->filter(fn ($r) => str_starts_with($r->uri(), 'csd/'));
$results['route_count'] = $csdRoutes->count();
$results['routes'] = $csdRoutes->map(fn ($r) => $r->methods()[0] . ' ' . $r->uri())->values()->all();

$controllers = [
    'Dashboard' => \App\Http\Controllers\Csd\CsdDashboardController::class,
    'Clients' => \App\Http\Controllers\Csd\CsdClientController::class,
    'Collections' => \App\Http\Controllers\Csd\CsdCollectionController::class,
    'Communications' => \App\Http\Controllers\Csd\CsdCommunicationController::class,
    'ChangeRequests' => \App\Http\Controllers\Csd\CsdChangeRequestController::class,
    'Support' => \App\Http\Controllers\Csd\CsdSupportController::class,
    'Amc' => \App\Http\Controllers\Csd\CsdAmcController::class,
    'Renewals' => \App\Http\Controllers\Csd\CsdRenewalController::class,
    'Opportunities' => \App\Http\Controllers\Csd\CsdOpportunityController::class,
];

foreach ($controllers as $name => $class) {
    $results['controllers'][$name] = class_exists($class);
}

$views = [
    'clients', 'collections', 'communications', 'change-requests',
    'support', 'amc', 'renewals', 'opportunities',
];
foreach ($views as $v) {
    $path = resource_path("views/components/csd/{$v}/index.blade.php");
    $results['views'][$v] = file_exists($path);
}

echo json_encode($results, JSON_PRETTY_PRINT) . PHP_EOL;
