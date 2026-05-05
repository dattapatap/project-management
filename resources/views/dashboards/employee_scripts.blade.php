{{-- Employee Dashboard Scripts --}}
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js')}}"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Year filter logic
        const yearFilter = document.getElementById('employee_dashboard_year_filter');
        if (yearFilter) {
            yearFilter.addEventListener('change', function() {
                window.location.href = "{{ url('/home') }}?year=" + this.value;
            });
        }

        // Growth Chart logic
        @if(isset($adminData['growth_trend']))
        var options = {
            series: [{
                name: 'Completed Tasks',
                data: [ @foreach($adminData['growth_trend'] as $t) {{ $t->count }}, @endforeach ]
            }],
            chart: {
                height: 250,
                type: 'area',
                toolbar: { show: false },
                sparkline: { enabled: false }
            },
            colors: ['#667eea'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [20, 100, 100, 100]
                }
            },
            xaxis: {
                categories: [ @foreach($adminData['growth_trend'] as $t) '{{ $t->month }}', @endforeach ],
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            grid: {
                borderColor: '#f1f1f1',
                padding: { bottom: 10 }
            },
            tooltip: { x: { format: 'dd/mm/yy HH:mm' } },
        };

        var chartEle = document.querySelector("#employee-growth-chart");
        if (chartEle) {
            var chart = new ApexCharts(chartEle, options);
            chart.render();
        }
        @endif
    });
</script>
