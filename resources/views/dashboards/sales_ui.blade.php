{{-- Sales Executive / Team Leader Dept 1 UI --}}
<div class="row">
    <div class="col-xl-4">
        <div class="col-sm-12 col-xl-12">
            <div class="card card-top-border">
                <div class="card-body">
                    <div class="media">
                        <div class="media-body">
                            <h5 class="font-size-14">Total Sales</h5>
                        </div>
                        <div class="avatar-xs">
                            <span class="avatar-title rounded-circle bg-primary">
                                <i class="dripicons-box"></i>
                            </span>
                        </div>
                    </div>
                    <h3 class="mt-2 align-self-center">{{ getTotalSales($user, $user->getRoleNames()->first()) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-xl-12">
            <div class="card card-top-border">
                <div class="card-body">
                    <div class="media">
                        <div class="media-body">
                            <h5 class="font-size-14">Todays TBRO</h4>
                        </div>
                        <div class="avatar-xs">
                            <span class="avatar-title rounded-circle bg-primary">
                                <i class="dripicons-bell"></i>
                            </span>
                        </div>
                    </div>
                    <h3 class="mt-2 align-self-center">{{ getTbrosOfToday($user) }}</h3>
                </div>
            </div>
        </div>
    </div>


    <div class="col-xl-8">
        <div class="card card-top-border">
            <div class="card-body">
                <h4 class="header-title mb-4">Sales Analytics</h4>
                <div class="row justify-content-center">
                    <div class="col-sm-4">
                        <div class="text-center">
                            <p>Sales Month Wise</p>
                        </div>
                    </div>
                </div>
                <div id="line-column-chart" class="apex-charts" dir="ltr"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card card-top-border">
            <div class="card-body">
                <h4 class="header-title mb-4" style="margin-bottom: 1.5rem!important;">Todays Follow-ups</h4>

                <div class="table-responsive">
                    <table id="datatable"
                        class="table table-bordered dt-responsive table-centered table-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>Sl</th>
                                <th>Company</th>
                                <th>Mobile</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Remark</th>
                                <th> Date </th>
                                @if($user->hasRole(['Team-Leader']))
                                <th> Sal/Exc </th>
                                @endif
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
