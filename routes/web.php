<?php

use App\Http\Controllers\ClientDocsController;
use App\Http\Controllers\ClientDomainsController;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    if(Auth::check())
        return redirect()->route('home');
    else
        return view('auth.login');
});

Auth::routes();



Route::group(['middleware' => ['auth'] ], function () {

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


});


Route::group(['middleware' => ['auth'] ], function () {

    // Clients with Process
    Route::get('clients/{id}/edit', [ClientsController::class, 'edit']);
    Route::get('clients/{id}/{urlname}', [ClientsController::class, 'showClient'])->name('client.detail');
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




    // Departments
    Route::get('/department/category', [ProjectCategoryController::class, 'getcategorybyid']);
    Route::get('/department/filter', [DepartmentController::class, 'filterDepartment'])->name('departments.filterDepartment');


    // Project & Caregory
    Route::get('/projectcategory/subcategories', [ProjectSubCategoryController::class, 'getcategorybyid']);

    // Clients Projects /Sales Exxecutive
    Route::post('client/createprojecct', [DepartmentProjectsController::class, 'createNewProject']);


    // Payments
    Route::get('/payments', [ClientPaymentsController::class, 'index']);
    Route::get('/payments/getallpayments', [ClientPaymentsController::class, 'getallpayments']);
    Route::get('/payments/getpayments-by-package', [ClientPaymentsController::class, 'getPaymentsByPackage']);

    // Assign To Others
    Route::get('/users-by-team-members', [UserController::class, 'getAllUserByRole'])->name('getUsersToAssign');
    Route::post('/assignToExecutive', [ClientsController::class, 'assignToExecutive'])->name('assignUsersToexecutive');

});


Route::group(['middleware' => ['role:Admin'] ], function () {

    // Users
    Route::get('/users/status/{user_id}', [UserController::class, 'changestatus'])->name('users.changeStatus');
    Route::resource('/users', UserController::class);

    Route::get('/departments/members/edit', [DepartmentMemberController::class, 'edit'])->name('department.editmember');
    Route::post('/departments/members/delete/{id}', [DepartmentMemberController::class, 'deleteMember'])->name('department.deletemember');
    Route::post('/departments/members/status/{id}', [DepartmentMemberController::class, 'statusMember'])->name('department.member.status');

    // Manage Members in department
    Route::get('/departments/{name}/teams', [TeamsController::class, 'index']);
    Route::resource('/departments', DepartmentController::class)->parameters(['departments' =>'department:name']);

    // Teams
    Route::post('/teams/members/remove', [TeamsController::class, 'removeMember']);
    Route::post('/teams/members/add', [TeamsController::class, 'addMember']);
    Route::get('/teams/teammembers', [TeamsController::class, 'teammembers']);
    Route::resource('/teams', TeamsController::class);

    // Clients Domains
    Route::post('/clientdomain/store', [ClientDomainsController::class, 'store']);
    Route::post('/clientdomain/renew', [ClientDomainsController::class, 'renew']);
    Route::get('/clientdomain/edit', [ClientDomainsController::class, 'edit']);
    Route::get('/domains', [ClientDomainsController::class, 'index']);
    Route::get('/domains/getalldomains', [ClientDomainsController::class, 'getalldomains']);

});


Route::prefix('projects')->middleware(['auth'])->group(function(){
    Route::get('/all', [ProjectController::class, 'index']);

});


