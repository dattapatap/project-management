<?php

use App\Http\Controllers\ClientDocsController;
use App\Http\Controllers\ClientHistoryController;
use App\Http\Controllers\ClientPaymentsController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DepartmentMemberController;
use App\Http\Controllers\DepartmentProjectsController;
use App\Http\Controllers\ProjectCategoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectSubCategoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TeamsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Csd\CsdAmcController;
use App\Http\Controllers\Csd\CsdChangeRequestController;
use App\Http\Controllers\Csd\CsdClientController;
use App\Http\Controllers\Csd\CsdCollectionController;
use App\Http\Controllers\Csd\CsdCommunicationController;
use App\Http\Controllers\Csd\CsdOpportunityController;
use App\Http\Controllers\Csd\CsdRenewalController;
use App\Http\Controllers\Csd\CsdSupportController;
use App\Http\Controllers\Csd\CsdTeamReportController;
use App\Http\Controllers\Reports\AdvancedReportController;
use App\Http\Controllers\Reports\EmployeeReportController;
use App\Http\Controllers\Reports\OperationsReportController;
use App\Http\Controllers\DailyClosingController;
use App\Http\Controllers\DailyTargetController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;




Route::get('/cache-clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    Artisan::call('optimize:clear');

    Artisan::call('config:cache');
    Artisan::call('view:cache');

    return "All Laravel caches and configurations cleared successfully!";
});

// Route::get('/storage-link', function () {
//     Artisan::call('storage:link');
//     return "Storage folder symlink created successfully on the server!";
// });




Route::get('/', function () {
    if (Auth::check())
        return redirect()->route('home');
    else
        return view('auth.login');
});

Auth::routes();




Route::group(['middleware' => ['auth']], function () {

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'updateBasicInfo'])->name('profile.update.info');
    Route::post('/profile/social', [ProfileController::class, 'updateSocialInfo'])->name('profile.update.socialinfo');
    Route::post('/profile/image', [ProfileController::class, 'profileimg'])->name('profileimg');

    Route::get('/changepassword', [ProfileController::class, 'changepassword'])->name('changepassword');
    Route::post('/changepassword', [ProfileController::class, 'updatePassword'])->name('updatePassword');

    // Dashboard
    Route::get('/home/chartdata', [DashboardController::class, 'chartdata']);
    Route::get('/todays/tbros', [DashboardController::class, 'getTodaysTbros']);




    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/mark-as-read', [NotificationController::class, 'markAsRead'])->name('mark-as-read-notification');
    Route::post('/mark-all-as-read', [NotificationController::class, 'markAsRead'])->name('mark-all-as-read-notification');

    // Shared Category & User Allocation Lookup Routes
    Route::get('/projectcategory/subcategories', [ProjectSubCategoryController::class, 'getcategorybyid']);
    Route::get('/users-by-team-members', [UserController::class, 'getAllUserByRole'])->name('getUsersToAssign');
    Route::post('client/ajax-create', [ClientsController::class, 'ajaxStore'])->name('client.ajax-create');
});


// 🔒 Sales Department Protected Routes
Route::group(['middleware' => ['auth', 'restrict.sales']], function () {
    // Bulk Upload
    Route::get('clients/bulk-upload', [ClientsController::class, 'bulkUploadForm'])->name('clients.bulkupload');
    Route::post('clients/bulk-upload', [ClientsController::class, 'bulkUploadStore'])->name('clients.bulkupload.store');
    Route::get('clients/bulk-upload/sample', [ClientsController::class, 'bulkUploadSample'])->name('clients.bulkupload.sample');

    // Clients with Process
    Route::get('clients/{id}/edit', [ClientsController::class, 'edit']);
    Route::post('clients/{id}/direct-mature', [ClientsController::class, 'directMature'])->name('clients.direct-mature');
    Route::get('clients/{id}/{urlname}', [ClientsController::class, 'showClient'])->name('client.detail');

    Route::redirect('client/Fresh', 'client/fresh');
    Route::redirect('client/Matured', 'client/matured');
    Route::redirect('client/Folloup', 'client/followup');
    Route::redirect('client/Followup', 'client/followup');
    Route::redirect('client/Not Interested', 'client/not-interested');

    Route::get('client/{category}', [ClientsController::class, 'clientsbycategory'])->name('clients.category');
    Route::resource('clients', ClientsController::class);

    //STS
    Route::post('clienthistory/creatests', [ClientHistoryController::class, 'createSts'])->name('client.createSts');
    Route::post('clienthistory/updatests', [ClientHistoryController::class, 'updateSts'])->name('client.updateSts');

    //DSR
    Route::post('clienthistory/createdsr', [ClientHistoryController::class, 'createDsr'])->name('client.createDsr');
    Route::post('clienthistory/updatedts', [ClientHistoryController::class, 'updateDsr'])->name('client.updateDsr');

    // Documents
    Route::post('clienthistory/addVisitingCard', [ClientHistoryController::class, 'addVisitingCard'])->name('client.addVisitingCard');
    Route::post('client/docs/uploadFile', [ClientDocsController::class, 'addDocument']);
    Route::get('client/docs/removechunck', [ClientDocsController::class, 'removeChunks']);
    Route::get('client/docs/downloadfile/{id}', [ClientDocsController::class, 'downloadfile'])->name('docs.download');

    // Client History
    Route::get('client/history/bycategory', [ClientHistoryController::class, 'getclienthistory'])->name('client.history');

    //Client Payments
    Route::get('client/payment/get', [ClientPaymentsController::class, 'getPaymentByClient'])->name('client.payments');
    Route::get('client/payment/byProjecct', [ClientPaymentsController::class, 'getPaymentByProject'])->name('client.getPendingPayments.byProject');
    Route::post('client/payment/add', [ClientPaymentsController::class, 'addPayment'])->name('client.addPayment');

    //Report Part
    Route::get('/mysts/searchsts', [ReportController::class, 'index']);
    Route::get('/mysts/searchsts/get', [ReportController::class, 'searchSTS'])->name('report.searchsts');

    Route::get('/reports/dsr/searchdsr', [ReportController::class, 'index_dsr']);
    Route::get('/reports/dsr/searchdsrget', [ReportController::class, 'searchDSR'])->name('report.searchdsr');

    Route::get('/reports/dsr/salesreports', [ReportController::class, 'sales_reports'])->name('report.sales.reports');
    Route::get('/reports/dsr/salesreports/get', [ReportController::class, 'sales_reports_get'])->name('report.salesreports');

    Route::get('/reports/searchsts/mysts', [ReportController::class, 'getCountMySts'])->name('report.get-count-my-sts');
    Route::get('/reports/searchsts/ajax', [ReportController::class, 'getCountMyStsByCategory'])->name('report.get-count-by-category');
    Route::get('/exportsts', [ReportController::class, 'exportStsReports']);

    // Nudges & Allocation
    Route::post('/assignToExecutive', [ClientsController::class, 'assignToExecutive'])->name('assignUsersToexecutive');
    Route::post('client/{client}/nudge', [ClientsController::class, 'nudgeExecutive'])->name('client.nudge');
    Route::post('clients/nudge-exec', [ClientsController::class, 'nudgeExecutiveByUserId']);

    // Sales Pipeline Kanban Board
    Route::get('sales/pipeline', [\App\Http\Controllers\Sales\SalesPipelineController::class, 'index'])->name('sales.pipeline');
    Route::post('sales/pipeline/move', [\App\Http\Controllers\Sales\SalesPipelineController::class, 'moveCard'])->name('sales.pipeline.move');

    // Service Catalog Manager
    Route::get('sales/catalog', [\App\Http\Controllers\Sales\ServiceCatalogController::class, 'index'])->name('sales.catalog.index');
    Route::post('sales/catalog', [\App\Http\Controllers\Sales\ServiceCatalogController::class, 'store'])->name('sales.catalog.store');
    Route::put('sales/catalog/{id}', [\App\Http\Controllers\Sales\ServiceCatalogController::class, 'update'])->name('sales.catalog.update');
    Route::post('sales/catalog/{id}/toggle', [\App\Http\Controllers\Sales\ServiceCatalogController::class, 'toggleStatus'])->name('sales.catalog.toggle');



    // Sales Activity Calendar
    Route::get('sales/calendar', [\App\Http\Controllers\Sales\SalesActivityController::class, 'index'])->name('sales.calendar.index');
    Route::get('sales/calendar/events', [\App\Http\Controllers\Sales\SalesActivityController::class, 'events'])->name('sales.calendar.events');

    // Automated Sales-to-OD Handoff Wizard
    Route::get('sales/handoff/{clientId}', [\App\Http\Controllers\Sales\HandoffWizardController::class, 'showWizard'])->name('sales.handoff.wizard');
    Route::post('sales/handoff', [\App\Http\Controllers\Sales\HandoffWizardController::class, 'processHandoff'])->name('sales.handoff.process');
});

// 🔒 WMS / Operations (DO) Department Protected Routes
Route::group(['middleware' => ['auth', 'restrict.wms']], function () {
    // Advanced Reports
    Route::get('/reports/projects', [AdvancedReportController::class, 'projectsReport'])->name('reports.projects');
    Route::get('/reports/projects/data', [AdvancedReportController::class, 'projectsData'])->name('reports.projects.data');

    // Departments
    Route::get('/department/category', [ProjectCategoryController::class, 'getcategorybyid']);
    Route::get('/department/filter', [DepartmentController::class, 'filterDepartment'])->name('departments.filterDepartment');

    // Clients Projects / WMS Tasks & Projects
    Route::post('client/createprojecct', [DepartmentProjectsController::class, 'createNewProject']);
    Route::get('projects/{projectid}/history', [ProjectController::class, 'history']);
    Route::get('projects/get-employees', [ProjectController::class, 'getEmployeesByProject'])->name('projects.employees');
    Route::get('projects/get-team-leaders', [ProjectController::class, 'getTeamLeadersByCategory'])->name('projects.teamleaders');

    // Payments
    Route::get('/payments', [ClientPaymentsController::class, 'index']);
    Route::get('/payments/getallpayments', [ClientPaymentsController::class, 'getallpayments']);
    Route::get('/payments/getpayments-by-package', [ClientPaymentsController::class, 'getPaymentsByPackage']);

    // Operations Task Calendar
    Route::get('/operations/calendar', [\App\Http\Controllers\OperationsCalendarController::class, 'index'])->name('operations.calendar.index');
    Route::get('/operations/calendar/events', [\App\Http\Controllers\OperationsCalendarController::class, 'events'])->name('operations.calendar.events');
});

// 🔒 CSD / Customer Service Department Protected Routes
Route::group(['prefix' => 'csd', 'middleware' => ['auth', 'restrict.csd'], 'as' => 'csd.'], function () {
    Route::get('/clients', [CsdClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/data', [CsdClientController::class, 'data'])->name('clients.data');
    Route::get('/clients/active', [CsdClientController::class, 'activeClients'])->name('clients.active');
    Route::post('/clients', [CsdClientController::class, 'store'])->name('clients.store');
    Route::put('/clients/{assignment}', [CsdClientController::class, 'update'])->name('clients.update');
    Route::get('/clients/{assignment}', [CsdClientController::class, 'show'])->name('clients.show');
    Route::post('/contacts', [CsdClientController::class, 'storeContact'])->name('contacts.store');

    Route::get('/collections', [CsdCollectionController::class, 'index'])->name('collections.index');
    Route::get('/collections/data', [CsdCollectionController::class, 'data'])->name('collections.data');
    Route::post('/collections', [CsdCollectionController::class, 'store'])->name('collections.store');
    Route::put('/collections/{collection}', [CsdCollectionController::class, 'update'])->name('collections.update');
    Route::get('/collections/{collection}', [CsdCollectionController::class, 'show'])->name('collections.show');

    Route::get('/change-requests', [CsdChangeRequestController::class, 'index'])->name('change-requests.index');
    Route::get('/change-requests/data', [CsdChangeRequestController::class, 'data'])->name('change-requests.data');
    Route::post('/change-requests', [CsdChangeRequestController::class, 'store'])->name('change-requests.store');
    Route::put('/change-requests/{changeRequest}', [CsdChangeRequestController::class, 'update'])->name('change-requests.update');
    Route::get('/change-requests/{changeRequest}', [CsdChangeRequestController::class, 'show'])->name('change-requests.show');
    Route::post('/change-requests/{changeRequest}/transfer-to-od', [CsdChangeRequestController::class, 'transferToOd'])->name('change-requests.transfer');

    Route::get('/support', [CsdSupportController::class, 'index'])->name('support.index');
    Route::get('/support/data', [CsdSupportController::class, 'data'])->name('support.data');
    Route::post('/support', [CsdSupportController::class, 'store'])->name('support.store');
    Route::put('/support/{ticket}', [CsdSupportController::class, 'update'])->name('support.update');
    Route::get('/support/{ticket}', [CsdSupportController::class, 'show'])->name('support.show');

    Route::get('/amc', [CsdAmcController::class, 'index'])->name('amc.index');
    Route::get('/amc/create', [CsdAmcController::class, 'create'])->name('amc.create');
    Route::get('/amc/data', [CsdAmcController::class, 'data'])->name('amc.data');
    Route::post('/amc', [CsdAmcController::class, 'store'])->name('amc.store');
    Route::get('/amc/{amc}/edit', [CsdAmcController::class, 'edit'])->name('amc.edit');
    Route::get('/amc/{amc}/document', [CsdAmcController::class, 'document'])->name('amc.document');
    Route::put('/amc/{amc}', [CsdAmcController::class, 'update'])->name('amc.update');
    Route::get('/amc/{amc}', [CsdAmcController::class, 'show'])->name('amc.show');

    Route::get('/communications', [CsdCommunicationController::class, 'index'])->name('communications.index');
    Route::get('/communications/data', [CsdCommunicationController::class, 'data'])->name('communications.data');
    Route::post('/communications', [CsdCommunicationController::class, 'store'])->name('communications.store');
    Route::get('/communications/{communication}', [CsdCommunicationController::class, 'show'])->name('communications.show');
    Route::put('/communications/{communication}', [CsdCommunicationController::class, 'update'])->name('communications.update');

    Route::get('/renewals', [CsdRenewalController::class, 'index'])->name('renewals.index');
    Route::get('/renewals/data', [CsdRenewalController::class, 'data'])->name('renewals.data');
    Route::get('/renewals/amc-options', [CsdRenewalController::class, 'amcOptions'])->name('renewals.amc-options');
    Route::post('/renewals/sync', [CsdRenewalController::class, 'sync'])->name('renewals.sync');
    Route::post('/renewals', [CsdRenewalController::class, 'store'])->name('renewals.store');
    Route::post('/renewals/{renewal}/mark-renewed', [CsdRenewalController::class, 'markRenewed'])->name('renewals.mark-renewed');
    Route::post('/renewals/{renewal}/mark-lapsed', [CsdRenewalController::class, 'markLapsed'])->name('renewals.mark-lapsed');
    Route::put('/renewals/{renewal}', [CsdRenewalController::class, 'update'])->name('renewals.update');
    Route::get('/renewals/{renewal}', [CsdRenewalController::class, 'show'])->name('renewals.show');

    Route::get('/opportunities', [CsdOpportunityController::class, 'index'])->name('opportunities.index');
    Route::get('/opportunities/data', [CsdOpportunityController::class, 'data'])->name('opportunities.data');
    Route::post('/opportunities', [CsdOpportunityController::class, 'store'])->name('opportunities.store');
    Route::put('/opportunities/{opportunity}', [CsdOpportunityController::class, 'update'])->name('opportunities.update');
    Route::get('/opportunities/{opportunity}', [CsdOpportunityController::class, 'show'])->name('opportunities.show');

    Route::get('/reports/team', [CsdTeamReportController::class, 'index'])->name('reports.team');
});

// 🌐 Shared Authenticated Routes
Route::group(['middleware' => ['auth']], function () {
    Route::get('/documents/download/{id}', [\App\Http\Controllers\DocumentController::class, 'download'])->name('documents.download');

    // Employee & Sales Reports
    Route::get('/reports/employees', [EmployeeReportController::class, 'index'])->name('reports.employees');
    Route::get('/reports/employees/data', [EmployeeReportController::class, 'data'])->name('reports.employees.data');
    Route::get('/reports/employee/{id}', [EmployeeReportController::class, 'detail'])->name('reports.employee.detail');
    Route::get('/reports/employee/{id}/pdf', [EmployeeReportController::class, 'downloadPdf'])->name('reports.employee.pdf');
    Route::get('/reports/operations', [OperationsReportController::class, 'index'])->name('reports.operations');
    Route::get('/reports/operations/data', [OperationsReportController::class, 'data'])->name('reports.operations.data');
    Route::get('/my-insights', [EmployeeReportController::class, 'myInsights'])->name('my-insights');

    // Commercial engagements (upsell / cross-sell tracking)
    Route::prefix('commercial')->name('commercial.')->group(function () {
        Route::get('/engagements', [\App\Http\Controllers\Commercial\ClientEngagementController::class, 'index'])->name('engagements.index');
        Route::get('/engagements/data', [\App\Http\Controllers\Commercial\ClientEngagementController::class, 'data'])->name('engagements.data');
        Route::get('/engagements/client/{clientId}/timeline', [\App\Http\Controllers\Commercial\ClientEngagementController::class, 'clientTimeline'])->name('engagements.client-timeline');
        Route::get('/engagements/{engagement}', [\App\Http\Controllers\Commercial\ClientEngagementController::class, 'show'])->name('engagements.show');
        Route::post('/engagements/{engagement}/start-commercial', [\App\Http\Controllers\Commercial\ClientEngagementController::class, 'startCommercial'])->name('engagements.start-commercial');
        Route::post('/engagements/{engagement}/close-commercial', [\App\Http\Controllers\Commercial\ClientEngagementController::class, 'closeCommercial'])->name('engagements.close-commercial');
    });
});


Route::group(['middleware' => ['role:Admin|Branch-Manager']], function () {

    // Users
    Route::get('/users/status/{user_id}', [UserController::class, 'changestatus'])->name('users.changeStatus');
    Route::resource('/users', UserController::class);

    Route::get('/departments/members/edit', [DepartmentMemberController::class, 'edit'])->name('department.editmember');
    Route::post('/departments/members/delete/{id}', [DepartmentMemberController::class, 'deleteMember'])->name('department.deletemember');
    Route::post('/departments/members/status/{id}', [DepartmentMemberController::class, 'statusMember'])->name('department.member.status');

    // Manage Members in department
    Route::get('/departments/{name}/teams', [TeamsController::class, 'index']);
    Route::resource('/departments', DepartmentController::class)->parameters(['departments' => 'department:name']);

    // Teams
    Route::post('/teams/members/remove', [TeamsController::class, 'removeMember']);
    Route::post('/teams/members/add', [TeamsController::class, 'addMember']);
    Route::get('/teams/teammembers', [TeamsController::class, 'teammembers']);
    Route::resource('/teams', TeamsController::class);

    // Legacy domains URL → CSD Renewals
    Route::redirect('/domains', '/csd/renewals');
    Route::redirect('/domains/getalldomains', '/csd/renewals');
});

Route::group(['middleware' => ['auth']], function () {
    // Day Closing Routes for Employees
    Route::get('/day-closing', [DailyClosingController::class, 'index'])->name('day-closing.index');
    Route::post('/day-closing', [DailyClosingController::class, 'submit'])->name('day-closing.submit');

    // Day Closing Approval Routes for Team Leaders & Admins
    Route::group(['middleware' => ['role:Admin|Branch-Manager|Team-Leader']], function () {
        Route::get('/day-closing/approvals', [DailyClosingController::class, 'approvals'])->name('day-closing.approvals');
        Route::post('/day-closing/submit-leave-on-behalf', [DailyClosingController::class, 'submitLeaveOnBehalf'])->name('day-closing.submit-leave-on-behalf');
        Route::post('/day-closing/{id}/approve', [DailyClosingController::class, 'approve'])->name('day-closing.approve');
        Route::post('/day-closing/{id}/reject', [DailyClosingController::class, 'reject'])->name('day-closing.reject');
    });

    // Manage Daily Targets (Restricted to Admins and Branch-Managers only)
    Route::group(['middleware' => ['role:Admin|Branch-Manager']], function () {
        Route::get('/daily-targets', [DailyTargetController::class, 'index'])->name('daily-targets.index');
        Route::get('/daily-targets/configure', [DailyTargetController::class, 'configure'])->name('daily-targets.configure');
        Route::post('/daily-targets/store', [DailyTargetController::class, 'store'])->name('daily-targets.store');
        Route::get('/daily-targets/data', [DailyTargetController::class, 'getData'])->name('daily-targets.data');
    });

    // Sales Target Planner & Leaderboards (viewable by Sales, CSD, Admin, Branch-Manager, Team-Leaders)
    Route::group(['middleware' => ['role:Admin|Branch-Manager|Sales-Executive|CSD-Executive|Team-Leader']], function () {
        Route::get('sales/targets', [\App\Http\Controllers\Sales\SalesTargetController::class, 'index'])->name('sales.targets.index');
        Route::get('sales/leaderboard', [\App\Http\Controllers\Sales\SalesTargetController::class, 'leaderboard'])->name('sales.targets.leaderboard');
    });

    // Sales Target Creation (Restricted to Admins and Branch-Managers only)
    Route::group(['middleware' => ['role:Admin|Branch-Manager']], function () {
        Route::post('sales/targets', [\App\Http\Controllers\Sales\SalesTargetController::class, 'store'])->name('sales.targets.store');
    });
});
