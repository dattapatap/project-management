{{-- Team Leader OD Dashboard Scripts --}}
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js')}}"></script>
<script>
    // TL Project Health Chart (Updated with Completed)
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof ApexCharts !== 'undefined' && document.querySelector("#tl-project-health-chart")) {
            console.log("Initializing Project Health Chart with data:", {!! json_encode($adminData['project_health']) !!});
            var tlHealth = {
                series: [
                    Number('{{ $adminData["project_health"]["Completed"] ?? 0 }}'),
                    Number('{{ $adminData["project_health"]["On Track"] ?? 0 }}'),
                    Number('{{ $adminData["project_health"]["At Risk"] ?? 0 }}'),
                    Number('{{ $adminData["project_health"]["Delayed"] ?? 0 }}')
                ],
                chart: {
                    type: 'donut',
                    height: 280,
                },
                labels: ['Completed', 'On Track', 'At Risk', 'Delayed'],
                colors: ['#34c38f', '#50a5f1', '#f1b44c', '#f46a6a'],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Yearly Projects',
                                    fontSize: '14px',
                                    fontWeight: 600,
                                    formatter: function(w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                    }
                                }
                            }
                        }
                    }
                },
                legend: { position: 'bottom' },
                dataLabels: { enabled: false }
            };
            var chart = new ApexCharts(document.querySelector("#tl-project-health-chart"), tlHealth);
            chart.render();
        }
    });
</script>
