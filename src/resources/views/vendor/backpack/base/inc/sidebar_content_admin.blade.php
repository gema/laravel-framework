@php
$configs = collect(['filemanager', 'backups', 'translations', 'pages', 'authentication', 'settings', 'logs'])
->mapWithKeys(fn($config) => [$config => \Config::get("gemadigital.sidebar.$config", true)]);

if($configs->values()->every(fn($value) => false)) return;
@endphp

{{-- Header --}}
<x-backpack::menu-separator :title="__('gemadigital::messages.extras')" />

<x-backpack::menu-dropdown :title="__('gemadigital::messages.admin')" icon="la la-terminal">
    @includeWhen(class_exists(\Backpack\DevTools\DevToolsServiceProvider::class), 'backpack.devtools::buttons.sidebar_item')

    @if($configs['authentication'])
    <x-backpack::menu-dropdown nested="true" :title="__('gemadigital::messages.authentication')" icon="la la-group">
        <x-backpack::menu-dropdown-item :title="__('gemadigital::messages.users')" icon="la la-user" :link="backpack_url('user')" />
        <x-backpack::menu-dropdown-item :title="__('gemadigital::messages.roles')" icon="la la-group" :link="backpack_url('role')" />
        <x-backpack::menu-dropdown-item :title="__('gemadigital::messages.permissions')" icon="la la-key" :link="backpack_url('permission')" />
    </x-backpack::menu-dropdown>
    @endif

    @if($configs['filemanager'])
    <x-backpack::menu-dropdown-item :title="__('backpack::crud.file_manager')" icon="la la-files-o" :link="backpack_url('elfinder')" />
    @endif

    @if($configs['pages'])
    <x-backpack::menu-dropdown-item :title="__('gemadigital::messages.pages')" icon="la la-file-o" :link="backpack_url('page')" />
    @endif

    @if($configs['translations'])
    <x-backpack::menu-dropdown nested="true" :title="__('gemadigital::messages.translations')" icon="la la-globe">
        <x-backpack::menu-dropdown-item :title="__('gemadigital::messages.languages')" icon="la la-flag-checkered" :link="backpack_url('language')" />
        <x-backpack::menu-dropdown-item :title="__('gemadigital::messages.site_texts')" icon="la la-language" :link="backpack_url('language/texts')" />
    </x-backpack::menu-dropdown>
    @endif

    @if($configs['backups'])
    <x-backpack::menu-dropdown-item :title="__('gemadigital::messages.backups')" icon="la la-hdd-o" :link="backpack_url('backup')" />
    @endif

    @if($configs['logs'])
    <x-backpack::menu-dropdown-item :title="__('gemadigital::messages.logs')" icon="la la-terminal" :link="backpack_url('log')" />
    @endif

    @if($configs['settings'])
    <x-backpack::menu-dropdown-item :title="__('gemadigital::messages.settings')" icon="la la-cog" :link="backpack_url('setting')" />
    @endif
</x-backpack::menu-dropdown>
