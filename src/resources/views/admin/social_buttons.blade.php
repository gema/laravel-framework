@if (config('services.google.client_id') || config('services.azure.client_id'))
<hr class="my-4" />
<div class="d-flex justify-content-between align-items-center">
    @if (config('services.azure.client_id'))
    <a href="{{ route('socialite.login', ['driver' => 'azure']) }}" tabindex="7" class="btn me-2 w-100 btn-azure">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-brand-windows">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M21 13v5c0 1.57 -1.248 2.832 -2.715 2.923l-.113 .003l-.042 .018a1 1 0 0 1 -.336 .056l-.118 -.008l-4.676 -.585v-7.407zm-10 0v7.157l-5.3 -.662c-1.514 -.151 -2.7 -1.383 -2.7 -2.895v-3.6zm0 -9.158v7.158h-8v-3.6c0 -1.454 1.096 -2.648 2.505 -2.87zm10 2.058v5.1h-8v-7.409l4.717 -.589c1.759 -.145 3.283 1.189 3.283 2.898" />
        </svg>
        <span>{{ trans('Microsoft') }}</span>
    </a>
    @endif
    @if (config('services.google.client_id'))
    <a href="{{ route('socialite.login', ['driver' => 'google']) }}" tabindex="6" class="btn ms-2 w-100 btn-google">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-brand-google">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M12 2a9.96 9.96 0 0 1 6.29 2.226a1 1 0 0 1 .04 1.52l-1.51 1.362a1 1 0 0 1 -1.265 .06a6 6 0 1 0 2.103 6.836l.001 -.004h-3.66a1 1 0 0 1 -.992 -.883l-.007 -.117v-2a1 1 0 0 1 1 -1h6.945a1 1 0 0 1 .994 .89c.04 .367 .061 .737 .061 1.11c0 5.523 -4.477 10 -10 10s-10 -4.477 -10 -10s4.477 -10 10 -10z" />
        </svg>
        <span>{{ trans('Google') }}</span>
    </a>
    @endif
</div>
@endif