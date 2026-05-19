@php
    use App\Support\ComplianceCookieBanner;

    $bannerMode = ComplianceCookieBanner::mode();
@endphp

@if(!request()->is('admin*'))
    @if($bannerMode === ComplianceCookieBanner::MODE_CONSENT)
        @include('components.cookie-consent-consent')
    @elseif($bannerMode === ComplianceCookieBanner::MODE_INFORMATIVE)
        @include('components.cookie-consent-informative')
    @endif
@endif
