@props(['src', 'alt' => '', 'class' => ''])

<img 
    src="{{ $src }}" 
    alt="{{ $alt }}" 
    class="{{ $class }}" 
    loading="lazy"
    decoding="async"
    {{ $attributes }}
>
