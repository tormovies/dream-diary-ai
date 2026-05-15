{{-- Совместимость: используйте <x-analytics-consent-bootstrap />. --}}
@props(['excludeAdmin' => false])

<x-analytics-consent-bootstrap :exclude-admin="$excludeAdmin" />
