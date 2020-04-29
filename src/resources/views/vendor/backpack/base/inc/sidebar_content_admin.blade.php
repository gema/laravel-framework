@php
$configs = [];
foreach (['filemanager', 'backups', 'translations', 'pages', 'authentication', 'settings', 'logs'] as $config) {
	$configs[$config] = \Config::get("gemadigital.sidebar.$config", true);
}
@endphp

<li class="header">{{ __("gemadigital::messages.admin") }}</li>

@if($configs['filemanager'])
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('elfinder') }}"><i class="nav-icon fa fa-files-o"></i> <span>{{ trans('backpack::crud.file_manager') }}</span></a></li>
@endif

@if($configs['backups'])
<li class="nav-item"><a class="nav-link" href='{{ backpack_url('backup') }}'><i class="nav-icon fa fa-hdd-o"></i> {{ __("gemadigital::messages.backups") }}</a></li>
@endif

@if($configs['translations'])
<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon fa fa-globe"></i> {{ __("gemadigital::messages.translations") }}</a>
	<ul class="nav-dropdown-items">
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('language') }}"><i class="nav-icon fa fa-flag-checkered"></i> <span>{{ __("gemadigital::messages.languages") }}</span></a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('language/texts') }}"><i class="nav-icon fa fa-language"></i> <span>{{ __("gemadigital::messages.site_texts") }}</span></a></li>
	</ul>
</li>
@endif

@if($configs['logs'])
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('log') }}'><i class='nav-icon fa fa-terminal'></i> {{ __("gemadigital::messages.logs") }}</a></li>
@endif

@if($configs['pages'])
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('page') }}'><i class='nav-icon fa fa-file-o'></i> <span>{{ __("gemadigital::messages.pages") }}</span></a></li>
@endif

@if($configs['authentication'])
<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon fa fa-group"></i> {{ __("gemadigital::messages.authentication") }}</a>
	<ul class="nav-dropdown-items">
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('user') }}"><i class="nav-icon fa fa-user"></i> <span>{{ __("gemadigital::messages.users") }}</span></a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('role') }}"><i class="nav-icon fa fa-group"></i> <span>{{ __("gemadigital::messages.roles") }}</span></a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('permission') }}"><i class="nav-icon fa fa-key"></i> <span>{{ __("gemadigital::messages.permissions") }}</span></a></li>
	</ul>
</li>
@endif

@if($configs['settings'])
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('setting') }}'><i class='nav-icon fa fa-cog'></i> <span>{{ __("gemadigital::messages.settings") }}</span></a></li>
@endif
