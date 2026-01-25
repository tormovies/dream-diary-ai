@props(['src', 'alt' => '', 'class' => '', 'width' => null, 'height' => null])

<img 
    src="{{ $src }}" 
    alt="{{ $alt }}" 
    class="{{ $class }}" 
    loading="lazy"
    decoding="async"
    @if($width) width="{{ $width }}" @endif
    @if($height) height="{{ $height }}" @endif
    {{ $attributes }}
>




















