@php
    $admin = url(config('gemadigital.routes.prefix', 'admin'));
    $down = app()->isDownForMaintenance();
@endphp

<div class="admin-actions d-flex flex-wrap gap-2">
    <button type="button" path="{{ $admin }}/cache/clear" class="btn btn-default">{{ __('gemadigital::actions.cache.clear.message') }}</button>
    <button type="button" path="{{ $admin }}/mail/test" prompt="{{ __('gemadigital::actions.mail.test.prompt') }}" email="{{ backpack_user()?->email }}" class="btn btn-default">{{ __('gemadigital::actions.mail.test.message') }}</button>
    <button type="button" path="{{ $admin }}/maintenance/down" confirm="{{ __('gemadigital::actions.maintenance.enable.confirm') }}" success="{{ __('gemadigital::actions.maintenance.enable.success') }}" class="btn btn-danger maintenance {{ $down ? 'd-none' : '' }}">{{ __('gemadigital::actions.maintenance.enable.message') }}</button>
    <button type="button" path="{{ $admin }}/maintenance/up" success="{{ __('gemadigital::actions.maintenance.disable.success') }}" class="btn btn-success maintenance {{ $down ? '' : 'd-none' }}">{{ __('gemadigital::actions.maintenance.disable.message') }}</button>
</div>

<script>
document.querySelectorAll('.admin-actions button[path]').forEach(btn => {
    btn.addEventListener('click', () => {
        if (btn.hasAttribute('confirm') && !confirm(btn.getAttribute('confirm'))) {
            return;
        }

        // The test mail asks for the address, the admin's own by default
        const body = {};

        if (btn.hasAttribute('prompt')) {
            body.email = prompt(btn.getAttribute('prompt'), btn.getAttribute('email'))?.trim();

            if (!body.email) {
                return;
            }
        }

        fetch(btn.getAttribute('path'), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify(body),
        })
        .then(response => response.json().catch(() => ({})).then(data => {
            // As text, since an SMTP error may have <addresses>; its line breaks become <br>. A wrong address comes
            // in errors when the framework's ExceptionHandler answers (debug mode), in message when Laravel does
            const message = document.createElement('div');
            message.innerText = data.message ?? (response.ok ? btn.getAttribute('success') : Object.values(data.errors ?? {}).flat().join('\n'));

            new Noty({
                text: '<strong>' + (response.ok ? @json(__('gemadigital::messages.success')) : @json(__('gemadigital::messages.error'))) + '</strong><br/>' + message.innerHTML,
                type: response.ok ? 'success' : 'error',
                // An error stays until it's closed, so there's time to read it
                ...(response.ok ? {} : { timeout: false }),
            }).show();

            // Maintenance: the button for the other state
            if (response.ok && btn.classList.contains('maintenance')) {
                document.querySelectorAll('.admin-actions .maintenance').forEach(button => button.classList.toggle('d-none'));
            }
        }));
    });
});
</script>
