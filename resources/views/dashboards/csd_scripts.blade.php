<script>
document.getElementById('csd_dashboard_year_filter')?.addEventListener('change', function () {
    const url = new URL(window.location.href);
    url.searchParams.set('year', this.value);
    window.location.href = url.toString();
});
</script>
