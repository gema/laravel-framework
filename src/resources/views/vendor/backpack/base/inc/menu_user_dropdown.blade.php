@if(isAdmin())
@if(Session::has('impersonated'))
<a class="dropdown-item" href="{{ route('impersonate.leave') }}"><i class="la la-user-secret me-2"></i> {{ ucfirst(__("gemadigital::messages.leave_impersonation")) }}</a>
@endif
<a class="dropdown-item" href="{{ route('actions') }}"><i class="la la-key me-2"></i> {{ __("gemadigital::messages.admin_actions") }}</a>
<div class="dropdown-divider"></div>
@endif
