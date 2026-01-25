@props(['data'])

@if(!empty($data) && is_array($data))
    @if(isset($data['@graph']))
        {{-- Если используется @graph, выводим как есть --}}
        <script type="application/ld+json">
{!! json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        </script>
    @elseif(isset($data['@context']) && isset($data['@type']))
        {{-- Одиночный объект --}}
        <script type="application/ld+json">
{!! json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        </script>
    @endif
@endif
