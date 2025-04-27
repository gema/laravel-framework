@if(Session::has('impersonated'))
{{-- Impersonate anonymous icon --}}
<i class="la la-user-secret fs-1 p-1 rounded rounded-circle text-white position-absolute" style="background-color: var(--colorA); border: 1px solid #000;"></i>
@else
{{-- Boring Avatars --}}
<div class="boring-avatars position-absolute rounded-circle overflow-hidden" style="--tblr-avatar-size: 3.3rem" data-name="{{ backpack_auth()->user()->email }}" data-colors="#ffffff, #5e9188, #3e5954, #253342, #232226"></div>
@endif