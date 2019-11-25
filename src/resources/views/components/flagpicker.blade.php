@php
$lang = Session::get('locale', 'en');
$locales = config('backpack.crud.locales');
@endphp

@foreach($locales as $local => $label)
<a href="{{ route('lang', ['locale' => $local]) }}" class="lang{{ $local == $lang ? " active" : "" }}">
    <span>{{ strtoupper(__("$local")) }}</span>
</a>
@endforeach
