@if(admin())
<a class="dropdown-item" href="{{ route('actions') }}"><i class="la la-key"></i> {{ __("gemadigital::messages.admin_actions") }}</a>
<a class="dropdown-item" href="{{ route('terminal') }}"><i class="la la-terminal"></i> {{ __("gemadigital::messages.artisan_terminal") }}</a>
<a class="dropdown-item" href="{{ route('symlink') }}"><i class="la la-link"></i> {{ __("gemadigital::messages.symlink") }}</a>
<div class="dropdown-divider"></div>
@endif
