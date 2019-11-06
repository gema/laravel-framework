@if ($crud->hasAccess('update'))
	<div ajax-request="/<?=$crud->route;?>/activateAll" ajax-confirm="{{ __("gemadigital::messages.activate_message") }}" ajax-refresh-on-success="true" class="btn btn-default ladda-button">
		<span class="ladda-label"><i class="fa fa-check-square-o"></i> {{ __("gemadigital::messages.activate_all") }}</span>
	</div>
@endif