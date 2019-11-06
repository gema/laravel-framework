@if(admin())
<a class="dropdown-item" href="{{ route('actions') }}"><i class="fa fa-key"></i> {{ __("gemadigital::messages.admin_actions") }}</a>
<a class="dropdown-item" href="{{ route('terminal') }}"><i class="fa fa-terminal"></i> {{ __("gemadigital::messages.artisan_terminal") }}</a>
<a class="dropdown-item" href="{{ route('symlink') }}"><i class="fa fa-link"></i> {{ __("gemadigital::messages.symlink") }}</a>
<div class="dropdown-divider"></div>
@endif
