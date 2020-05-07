@if ($crud->hasAccess('update'))
	<div ajax-request="/<?=$crud->route;?>/deactivateAll" ajax-confirm="{{ __("gemadigital::messages.deactivate_message") }}" ajax-refresh-on-success="true" class="btn btn-default ladda-button">
		<span class="ladda-label"><i class="la la-square-o"></i> {{ __("gemadigital::messages.deactivate_all") }}</span>
	</div>
@endif