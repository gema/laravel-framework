<style>
  .roles-list {
      display: flex;
      align-items: center;
      margin-right: 28px;
      height: 55px;
  }
  .roles-list:hover {
    background-color: rgba(0,0,0,0.2);
  }
  .roles-list > a {
    text-decoration: none;
    color: #FFF;
    fill: #FFF;
    padding: 16px 12px;
  }
  .roles-list li a {
    width: 100%;
    text-decoration: none;
    color: #000;
  }
  .roles-list li {
    color: #FFF;
    text-decoration: none;
  }
  .roles-list li:not(.title):hover, .roles-list li:not(.title).show {
    background-color: #eee;
  }
  .roles-list li.title {
    color: #444;
  }
  .roles-list li p {
    display: inline-block;
    margin: 0;
  }
  .roles-list .flag {
    width: 40px;
    height: 28px;
    display: inline-block;
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    vertical-align: middle;
  }
  .roles-list li a {
    display: flex;
    align-items: center;
  }
  .dropdown-menu {
    padding: .5rem 0;
  }
  .dropdown-menu > li > a {
    padding: 3px 25px;
  }
  .dropdown-menu > .active > a {
    pointer-events: none;
  }
  .dropdown-menu > .toggle.active > a {
    pointer-events: initial;
  }
  .dropdown-menu>.active>a, .dropdown-menu>.active>a:focus, .dropdown-menu>.active>a:hover {
    background-color: #d2d6de;
    color: #000;
  }
  .dropdown-menu > .title {
    padding: 0px 25px 4px;
      font-size: 12px;
  }
  li.toggle.active p:before {
      content: '✓ ';
      margin-left: -15px;
  }
  hr {
    margin: 8px 0;
  }
</style>

@php
$current_role = Session::get('role', 'admin');
$current_permissions = Session::get('permissions', []);
$roles = Config::get("enums.user.roles");
$permissions = Config::get("enums.user.permissions");
@endphp
<li class="dropdown roles-list">
  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
    @if($current_role != 'admin')
    <i>
      <svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="11" height="17" viewBox="0 0 18 28">
        <path d="M5 12h8v-3c0-2.203-1.797-4-4-4s-4 1.797-4 4v3zM18 13.5v9c0 0.828-0.672 1.5-1.5 1.5h-15c-0.828 0-1.5-0.672-1.5-1.5v-9c0-0.828 0.672-1.5 1.5-1.5h0.5v-3c0-3.844 3.156-7 7-7s7 3.156 7 7v3h0.5c0.828 0 1.5 0.672 1.5 1.5z"></path>
      </svg>
    </i>&nbsp;&nbsp;{{ ucfirst(__($current_role)) }}
    @else
    <i>
      <svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="28" viewBox="0 0 26 28">
        <path d="M26 9v4c0 0.547-0.453 1-1 1h-1c-0.547 0-1-0.453-1-1v-4c0-2.203-1.797-4-4-4s-4 1.797-4 4v3h1.5c0.828 0 1.5 0.672 1.5 1.5v9c0 0.828-0.672 1.5-1.5 1.5h-15c-0.828 0-1.5-0.672-1.5-1.5v-9c0-0.828 0.672-1.5 1.5-1.5h10.5v-3c0-3.859 3.141-7 7-7s7 3.141 7 7z"></path>
      </svg>
    </i>
    @endif
    <span class="caret"></span>
  </a>
  <ul class="dropdown-menu" role="menu">
    <li class="title">{{ __("gemadigital::messages.view_as") }}:</li>
    @foreach($roles as $role)
    <li class="{{ $role == $current_role ? "active" : "" }}">
      <a href="{{ route('view-as-role', ['role' => $role]) }}">
        <p>{{ ucfirst(__($role)) }}</p>
      </a>
    </li>
    @endforeach
    <hr />
    @foreach($permissions as $permission)
    @php
      $state = in_array($permission, $current_permissions);
    @endphp
    <li class="toggle {{ $state ? "active" : "" }}">
      <a href="{{ route('view-as-permission', ['permission' => $permission, 'state' => $state ? 0 : 1]) }}">
        <p>{{ ucfirst(__($permission)) }}</p>
      </a>
    </li>
    @endforeach
    <hr />
    <li>
      <a href="{{ route('view-as-permission', ['permission' => 'all', 'state' => 0]) }}">
        <p>{{ __("gemadigital::messages.clear_all") }}</p>
      </a>
    </li>
  </ul>
</li>
