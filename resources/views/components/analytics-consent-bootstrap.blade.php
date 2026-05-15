{{-- Конфиг и отложенные вставки: на публичном сайте Метрика и код из админки — после согласия (resources/js/cookie-consent.js). В админке — сразу. --}}
@props(['excludeAdmin' => true])

@php
    $excludeAdmin = (bool) ($excludeAdmin ?? true);
    $shouldDefer = ! $excludeAdmin || ! request()->is('admin*');
    $metrikaId = preg_replace('/\D/', '', (string) \App\Models\Setting::getValue('yandex_metrika_id', '89409547'));
    $globalHeadAd = (string) \App\Models\Setting::getValue('global_head_ad_code', '');
    $compliancePayload = [
        'policyVersion' => config('compliance.policy_version'),
        'consentLogUrl' => route('consent.store'),
        'deferContext' => $shouldDefer,
        'hasMetrika' => $shouldDefer && $metrikaId !== '',
        'hasDeferredHeadAd' => $shouldDefer && $globalHeadAd !== '',
    ];
@endphp
<script>
window.__COMPLIANCE__ = @json($compliancePayload);
</script>
@if(! $shouldDefer && $metrikaId !== '')
@include('components.partials.yandex-metrika-inline', ['metrikaId' => $metrikaId])
@endif
@if(! $shouldDefer && $globalHeadAd !== '')
{!! $globalHeadAd !!}
@endif
@if($shouldDefer && $metrikaId !== '')
<meta name="deferred-metrika-id" content="{{ $metrikaId }}">
@endif
@if($shouldDefer && $globalHeadAd !== '')
<template id="deferred-global-head-ad">{!! $globalHeadAd !!}</template>
@endif
