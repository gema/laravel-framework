<style>
.flags-list {
    display: flex;
    align-items: center;
    margin-right: 2px;
    height: 55px;
}
.flags-list:hover {
  background-color: rgba(0,0,0,0.2);
}
.flags-list > a {
  text-decoration: none;
  color: #FFF;
  font-size: 15px;
  padding: 16px 12px;
}
.flags-list li a {
  width: 100%;
  text-decoration: none;
  color: #000;
}
.dropdown-menu {
  padding: .5rem 0;
}
.flags-list li {
  color: #FFF;
  text-decoration: none;
}
.flags-list li:not(.title):hover, .flags-list li:not(.title).show {
  background-color: #eee;
}
.flags-list li.title {
  color: #444;
}
.flags-list li p {
  display: inline-block;
  margin: 0;
}
.flags-list .flag {
  width: 40px;
  height: 28px;
  display: inline-block;
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
  vertical-align: middle;
}
.flags-list li a {
  display: flex;
  align-items: center;
}
.dropdown-menu > .active > a {
  pointer-events: none;
}
.dropdown-menu>.active>a, .dropdown-menu>.active>a:focus, .dropdown-menu>.active>a:hover {
  background-color: #d2d6de;
  color: #000;
}
</style>

@php
$lang = Session::get('locale', 'en');
$locales = config('backpack.crud.locales');
@endphp
<li class="dropdown flags-list">
  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
    <div class="flag" style="background-image: url({{ asset("gemadigital/img/flags/$lang.png") }}); height: 20px;"></div>
    {{-- <span class="text">{{ $locales[$lang] }}</span> --}}
    <span class="caret"></span>
  </a>
  <ul class="dropdown-menu" role="menu">
    @foreach($locales as $local => $label)
    <li class="{{ $local == $lang ? "active" : "" }}">
      <a href="{{ route('lang', ['locale' => $local]) }}">
        <div class="flag" style="background-image: url({{ asset("gemadigital/img/flags/$local.png") }})"></div>
        <p>{{ $label }}</p>
      </a>
    </li>
    @endforeach
  </ul>
</li>
