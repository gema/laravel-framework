@php
$lang = Session::get('locale', 'en');
$locales = config('backpack.crud.locales');
$translate = $translate ?? null;
@endphp

@foreach($locales as $local => $label)
<a href="{{ route('lang', ['locale' => $local]) }}" class="lang{{ $local == $lang ? " active" : "" }}" style="text-transform: uppercase;">
    @if(!$translate)
    {{ $local }}</a>
    @else
    {{ Locale::getDisplayLanguage($local, $local) }}</a>
    @endif
@endforeach
