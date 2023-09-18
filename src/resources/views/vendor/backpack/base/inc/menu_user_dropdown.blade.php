@if(admin())
<a class="dropdown-item" href="{{ route('actions') }}"><i class="la la-key me-2"></i> {{ __("gemadigital::messages.admin_actions") }}</a>
<a class="dropdown-item" href="{{ route('terminal') }}"><i class="la la-terminal me-2"></i> {{ __("gemadigital::messages.artisan_terminal") }}</a>
<div class="dropdown-divider"></div>
@endif
