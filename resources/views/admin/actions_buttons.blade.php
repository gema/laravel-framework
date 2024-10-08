<div>
    <button path="{{ url('/admin/cache/flush') }}" success="{{ __("gemadigital::actions.cache.clear.success") }}" class="btn btn-default ajax">{{ __("gemadigital::actions.cache.clear.message") }}</button>
    <br />
    <div style="margin-bottom: 5px;"></div>
    <button path="{{ url('/admin/cache/config') }}" success="{{ __("gemadigital::actions.cache.config.success") }}" class="btn btn-default ajax">{{ __("gemadigital::actions.cache.config.message") }}</button>
    <button path="{{ url('/admin/cache/config/clear') }}" success="{{ __("gemadigital::actions.cache.config_clear.success") }}" class="btn btn-default ajax">{{ __("gemadigital::actions.cache.config_clear.message") }}</button>
    <br />
    <div style="margin-bottom: 5px;"></div>
    <button path="{{ url('/admin/cache/route') }}" success="{{ __("gemadigital::actions.cache.route.success") }}" class="btn btn-default ajax">{{ __("gemadigital::actions.cache.route.message") }}</button>
    <button path="{{ url('/admin/cache/route/clear') }}" success="{{ __("gemadigital::actions.cache.route_clear.success") }}" class="btn btn-default ajax">{{ __("gemadigital::actions.cache.route_clear.message") }}</button>
    <br />
    <div style="margin-bottom: 5px;"></div>
    <button path="{{ url('/admin/cache/view') }}" success="{{ __("gemadigital::actions.cache.view.success") }}" class="btn btn-default ajax">{{ __("gemadigital::actions.cache.view.message") }}</button>
    <button path="{{ url('/admin/cache/view/clear') }}" success="{{ __("gemadigital::actions.cache.view_clear.success") }}" class="btn btn-default ajax">{{ __("gemadigital::actions.cache.view_clear.message") }}</button>
    <div style="margin-bottom: 5px;"></div>
    <button path="{{ url('/admin/maintenance/down') }}" success="{{ __("gemadigital::actions.maintenance.up.success") }}" class="btn btn-danger ajax">{{ __("gemadigital::actions.maintenance.up.message") }}</button>
    <button path="{{ url('/admin/maintenance/up') }}" success="{{ __("gemadigital::actions.maintenance.down.success") }}" class="btn btn-success ajax">{{ __("gemadigital::actions.maintenance.down.message") }}</button>
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
