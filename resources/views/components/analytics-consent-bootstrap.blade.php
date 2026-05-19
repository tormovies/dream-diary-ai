{{-- Метрика: сразу, если баннер отключён или админка; иначе после согласия (cookie-consent.js). --}}
@props(['excludeAdmin' => true])

@php
    $excludeAdmin = (bool) ($excludeAdmin ?? true);
    $onPublicSite = ! request()->is('admin*');
    $cookieBannerEnabled = (bool) config('compliance.cookie_banner_enabled', false);
    $deferAnalytics = $onPublicSite && $cookieBannerEnabled;
    $metrikaId = preg_replace('/\D/', '', (string) \App\Models\Setting::getValue('yandex_metrika_id', '89409547'));
    $globalHeadAd = (string) \App\Models\Setting::getValue('global_head_ad_code', '');
    $compliancePayload = [
        'policyVersion' => config('compliance.policy_version'),
        'consentLogUrl' => route('consent.store'),
        'deferContext' => $deferAnalytics,
        'bannerEnabled' => $cookieBannerEnabled,
        'hasMetrika' => $deferAnalytics && $metrikaId !== '',
        'hasDeferredHeadAd' => $deferAnalytics && $globalHeadAd !== '',
    ];
@endphp
<script>
window.__COMPLIANCE__ = @json($compliancePayload);
</script>
@if(! $deferAnalytics && $metrikaId !== '')
@include('components.partials.yandex-metrika-inline', ['metrikaId' => $metrikaId])
@endif
@if(! $deferAnalytics && $globalHeadAd !== '')
{!! $globalHeadAd !!}
@endif
@if($deferAnalytics && $metrikaId !== '')
<meta name="deferred-metrika-id" content="{{ $metrikaId }}">
@endif
@if($deferAnalytics && $globalHeadAd !== '')
<template id="deferred-global-head-ad">{!! $globalHeadAd !!}</template>
@endif
