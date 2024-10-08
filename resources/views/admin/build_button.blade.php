<div>
    <button path="{{ url('/admin/build') }}" success="{{ __("gemadigital::actions.build.success") }}" class="btn btn-primary ajax">{{ __("gemadigital::actions.build.message") }} <span class="la la-code" aria-hidden="true" style="margin-left: 10px;"></span></button>
</div>

<script>
document.querySelectorAll('.btn.ajax').forEach(btn => {
    btn.addEventListener('click', e => {
        fetch(btn.getAttribute('path'), {
            method: 'POST',
            credentials: "same-origin",
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            }
        })
        .then(function(response) {
            new Noty(response.status == 200 ? {
                text: "<strong>{{ __("gemadigital::messages.success") }}</strong><br/>" + btn.getAttribute('success'),
                type: "success",
            } : {
                text: "<strong>{{ __("gemadigital::messages.error") }}</strong>",
                type: "error",
            }).show();
        });
    });
});
</script>
