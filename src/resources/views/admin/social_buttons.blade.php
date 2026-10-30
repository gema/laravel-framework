@php
    $azure = config('services.azure.client_id') && \GemaDigital\Http\Controllers\SocialiteController::isAvailable('azure');
    $google = config('services.google.client_id') && \GemaDigital\Http\Controllers\SocialiteController::isAvailable('google');
@endphp

@if ($azure || $google)
<style>
    .btn-azure { --tblr-btn-bg: #000; --tblr-btn-border-color: #000; --tblr-btn-color: #fff; --tblr-btn-hover-bg: #2f2f2f; --tblr-btn-hover-border-color: #2f2f2f; --tblr-btn-hover-color: #fff; }
    .btn-azure svg { flex-shrink: 0; width: 18px; margin-right: 6px; }
    .social-buttons { column-gap: 1rem; row-gap: .5rem; }
    .social-buttons > .btn { flex: 1 1 0; min-width: max-content; }
</style>

<hr class="my-4" />
{{-- Side by side and the same width while both texts fit, otherwise one under the other --}}
<div class="social-buttons d-flex flex-wrap">
    @if ($azure)
    <a href="{{ route('socialite.login', ['driver' => 'azure']) }}" tabindex="7" class="btn btn-azure">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 21 21" aria-hidden="true">
            <rect x="1" y="1" width="9" height="9" fill="#f25022" />
            <rect x="11" y="1" width="9" height="9" fill="#7fba00" />
            <rect x="1" y="11" width="9" height="9" fill="#00a4ef" />
            <rect x="11" y="11" width="9" height="9" fill="#ffb900" />
        </svg>
        <span>{{ trans('Sign in with Microsoft') }}</span>
    </a>
    @endif
    @if ($google)
    <a href="{{ route('socialite.login', ['driver' => 'google']) }}" tabindex="6" class="btn btn-google">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-brand-google">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M12 2a9.96 9.96 0 0 1 6.29 2.226a1 1 0 0 1 .04 1.52l-1.51 1.362a1 1 0 0 1 -1.265 .06a6 6 0 1 0 2.103 6.836l.001 -.004h-3.66a1 1 0 0 1 -.992 -.883l-.007 -.117v-2a1 1 0 0 1 1 -1h6.945a1 1 0 0 1 .994 .89c.04 .367 .061 .737 .061 1.11c0 5.523 -4.477 10 -10 10s-10 -4.477 -10 -10s4.477 -10 10 -10z" />
        </svg>
        <span>{{ trans('Sign in with Google') }}</span>
    </a>
    @endif
</div>
@endif