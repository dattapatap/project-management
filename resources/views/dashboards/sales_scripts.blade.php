{{-- Sales Executive / Team Leader Dept 1 Scripts --}}
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js')}}"></script>
<script src="{{ asset("assets/js/pages/dashboard.init.js")}}"></script>
<script>
    $(document).ready(function() {
        var dtColumns = [{
                data: 'DT_RowIndex',
                name: 'id',
                orderable: true,
                searchable: false
            },
            {
                data: 'name',
                name: 'name',
                orderable: false,
                searchable: true
            },
            {
                data: 'mobile',
                name: 'mobile',
                orderable: false,
                searchable: true
            },
            {
                data: 'category',
                name: 'history.category',
                orderable: false,
                searchable: false
            },
            {
                data: 'status',
                name: 'status',
                orderable: false,
                searchable: false
            },
            {
                data: 'remarks',
                name: 'history.remarks',
                orderable: false,
                searchable: false
            },
            {
                data: 'tbro',
                name: 'history.tbro',
                orderable: false,
                searchable: false
            }
        ];

        @if(isAdminOrTeamLeader($user))
        dtColumns.push({
            data: 'telereferral',
            name: 'telereferral.name',
            orderable: false,
            searchable: true
        });
        @endif

        dtColumns.push({
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false
        });

        $("#datatable").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                type: 'GET',
                url: base_url + "/todays/tbros",
                error: function(err) {
                    console.log(err);
                }
            },
            columns: dtColumns
        });
    });
</script>
