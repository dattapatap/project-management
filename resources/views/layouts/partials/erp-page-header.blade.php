{{-- Reusable page header — use global erp-components.css classes only --}}
<div class="erp-page-header">
    <div class="erp-page-header__main">
        <h4 class="erp-page-title">{{ $title }}</h4>
        @if(!empty($subtitle))
        <p class="erp-page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    @if(!empty($actions))
    <div class="erp-page-header__actions">
        {!! $actions !!}
    </div>
    @endif
</div>
