@php
$options = collect(explode(',', $options))->map(function ($value) { return intval($value); });
$image = $disk ?? null === false ? url($src) : Storage::disk($disk ?? 'uploads')->url($src);
@endphp

<picture>
    <img src="{{ sized_image($image, $options->max()) }}"
        srcset="
        @foreach($options as $i => $size)
        {{ sized_image($image, $size) }} {{ $size }}w,
        @endforeach
        "
        sizes="{{ $sizes ?? $width ?? "100vw" }}"
        alt="{{ $alt ?? '' }}"
        width="{{ $width ?? "100%" }}"
        height="{{ $height ?? "100%" }}"
        loading="{{ $loading ?? "auto" }}"
        />
</picture>