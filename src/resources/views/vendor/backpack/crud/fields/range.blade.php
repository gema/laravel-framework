{{-- html5 range input --}}
@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')
    <input
        type="range"
        name="{{ $field['name'] }}"
        min="{{ $field['min'] }}"
        max="{{ $field['max'] }}"
        step="{{ $field['step'] }}"
        value="{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}"
        @include('crud::fields.inc.attributes')
        oninput="updateRangeInput(this);"
        >

    <input type="text" disabled
        style="margin-top: 8px; max-width: 120px;"
        value="{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}"
        @include('crud::fields.inc.attributes')
        >

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->checkIfFieldIsFirstOfItsType($field, $fields))

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <script>
        function updateRangeInput(elem)
        {
            $(elem).next().val($(elem).val());
        }
    </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
