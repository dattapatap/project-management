{{-- Branch Manager Dashboard Charts --}}
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js')}}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const yearFilter = document.getElementById('bm_dashboard_year_filter');
    if (yearFilter) {
        yearFilter.addEventListener('change', function() {
            window.location.href = "{{ route('home') }}?year=" + this.value;
        });
    }

    if (typeof ApexCharts === 'undefined') return;

    const deptLabels = @json($adminData['department_overview']['labels'] ?? ['NSD', 'CSD', 'OD']);
    const deptStaff = @json($adminData['department_overview']['staff'] ?? [0, 0, 0]);
    const deptWorkload = @json($adminData['department_overview']['workload'] ?? [0, 0, 0]);
    const trendMonths = @json($adminData['monthly_matured_trend']['months'] ?? []);
    const trendCounts = @json($adminData['monthly_matured_trend']['counts'] ?? []);
    const nsdDist = @json($adminData['nsd']['status_distribution'] ?? []);

    const donutBase = {
        chart: { type: 'donut', height: 260 },
        legend: { position: 'bottom', fontSize: '11px' },
        dataLabels: { enabled: false },
        stroke: { show: true, width: 4, colors: ['#fff'] },
    };

    if (document.querySelector('#bm-dept-staff-chart')) {
        new ApexCharts(document.querySelector('#bm-dept-staff-chart'), {
            ...donutBase,
            series: deptStaff,
            labels: deptLabels,
            colors: ['#556ee6', '#34c38f', '#f1b44c'],
        }).render();
    }

    if (document.querySelector('#bm-dept-workload-chart')) {
        new ApexCharts(document.querySelector('#bm-dept-workload-chart'), {
            chart: { type: 'bar', height: 260, toolbar: { show: false } },
            series: [{ name: 'Active Items', data: deptWorkload }],
            xaxis: { categories: deptLabels },
            colors: ['#50a5f1'],
            plotOptions: { bar: { borderRadius: 6, columnWidth: '45%' } },
            dataLabels: { enabled: true },
        }).render();
    }

    if (document.querySelector('#bm-nsd-trend-chart')) {
        new ApexCharts(document.querySelector('#bm-nsd-trend-chart'), {
            chart: { type: 'area', height: 260, toolbar: { show: false } },
            series: [{ name: 'Matured', data: trendCounts }],
            xaxis: { categories: trendMonths },
            colors: ['#34c38f'],
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.45, opacityTo: 0.05 } },
            dataLabels: { enabled: false },
        }).render();
    }

    if (document.querySelector('#bm-nsd-stage-chart')) {
        new ApexCharts(document.querySelector('#bm-nsd-stage-chart'), {
            ...donutBase,
            series: [
                Number(nsdDist['Matured'] || 0),
                Number(nsdDist['Followup'] || 0),
                Number(nsdDist['Meeting Fixed'] || 0),
                Number(nsdDist['Fresh'] || 0),
                Number(nsdDist['Not Interested'] || 0),
            ],
            labels: ['Matured', 'Followup', 'Meeting Fixed', 'Fresh', 'Not Interested'],
            colors: ['#34c38f', '#50a5f1', '#7F00FF', '#556ee6', '#f46a6a'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '68%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                formatter: w => w.globals.seriesTotals.reduce((a, b) => a + b, 0),
                            },
                        },
                    },
                },
            },
        }).render();
    }

    const csdActive = Number('{{ $adminData['csd']['active_clients'] ?? 0 }}');
    const csdAtRisk = Number('{{ $adminData['csd']['at_risk_clients'] ?? 0 }}');
    const csdTickets = Number('{{ $adminData['csd']['open_tickets'] ?? 0 }}');
    const csdRenewals = Number('{{ $adminData['csd']['renewal_due_clients'] ?? 0 }}');

    if (document.querySelector('#bm-csd-health-chart')) {
        new ApexCharts(document.querySelector('#bm-csd-health-chart'), {
            ...donutBase,
            series: [csdActive, csdAtRisk, csdTickets, csdRenewals],
            labels: ['Active Clients', 'At Risk', 'Open Tickets', 'Renewals Due'],
            colors: ['#34c38f', '#f46a6a', '#50a5f1', '#f1b44c'],
        }).render();
    }

    const odTodo = Number('{{ $adminData['od']['projects_todo'] ?? 0 }}');
    const odProgress = Number('{{ $adminData['od']['projects_in_progress'] ?? 0 }}');
    const odDone = Number('{{ $adminData['od']['projects_completed'] ?? 0 }}');

    if (document.querySelector('#bm-od-project-chart')) {
        new ApexCharts(document.querySelector('#bm-od-project-chart'), {
            ...donutBase,
            series: [odTodo, odProgress, odDone],
            labels: ['ToDo', 'In Progress', 'Completed'],
            colors: ['#adb5bd', '#f1b44c', '#34c38f'],
        }).render();
    }
});
</script>
