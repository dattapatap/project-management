{{-- Project Manager Dashboard Scripts --}}
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js')}}"></script>
<script>
    // PM Project Health Chart
    var pmTodo = parseInt("{{ $adminData['pm_proj_todo'] ?? 0 }}");
    var pmProgress = parseInt("{{ $adminData['pm_proj_in_progress'] ?? 0 }}");
    var pmDone = parseInt("{{ $adminData['pm_proj_completed'] ?? 0 }}");

    var pmProjOptions = {
        series: [pmTodo, pmProgress, pmDone],
        chart: {
            type: 'donut',
            height: 300,
            dropShadow: {
                enabled: true,
                color: '#000',
                top: 2,
                left: 2,
                blur: 8,
                opacity: 0.1
            }
        },
        labels: ['ToDo', 'InProgress', 'Completed'],
        colors: ['#adb5bd', '#f1b44c', '#34c38f'],
        stroke: {
            show: true,
            width: 5,
            colors: ['#fff']
        },
        dataLabels: {
            enabled: false
        },
        legend: {
            position: 'bottom',
            offsetY: 0,
            height: 30,
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '72%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Total Projects',
                            fontSize: '14px',
                            fontWeight: 600,
                            color: '#9599ad',
                            formatter: function(w) {
                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                            }
                        },
                        value: {
                            show: true,
                            fontSize: '24px',
                            fontWeight: 700,
                            color: '#343a40',
                            offsetY: 5
                        }
                    }
                }
            }
        }
    };
    new ApexCharts(document.querySelector("#pm-project-health-chart"), pmProjOptions).render();
</script>
