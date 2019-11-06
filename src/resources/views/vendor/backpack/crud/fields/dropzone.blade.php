@push('crud_fields_styles')
    <link rel="stylesheet" href="{{ asset('gemadigital/css/dropzone.min.css') }}">
    <style>
        .dropzone-target {
            border: 2px dashed #d2d6de;
            color: #333;
            font-size: 1.2em;
            font-weight: 700;
            padding: 2em;
        }

        .dropzone-previews {
            margin-bottom: 10px;
            border: 1px solid #d2d6de;
            padding: 6px;
        }

        .dropzone.dz-drag-hover {
            background: #ececec;
            border: 2px dashed #999;
        }

        .dz-message {
            text-align: left;
        }

        .dropzone .dz-preview .dz-image-no-hover {
            border-radius: 4px;
            cursor: move;
            display: block;
            height: 120px;
            overflow: hidden;
            position: relative;
            width: 120px;
            z-index: 10;
            background-size: cover;
            background-position: center;
        }
        .dropzone .dz-preview .dz-success-mark,
        .dropzone .dz-preview .dz-error-mark {
            margin-top: -37px;
        }
        .dropzone .dz-preview .dz-image-no-hover {
            margin: 8px;
            box-shadow: 0px 1px 6px rgba(0,0,0,0.35);
            background-color: #000;
        }
        .dz-error-message {
            width: 154px!important;
            top: 160px!important;
        }
        .dropzone .dz-preview .dz-error-message:after {
            left: 70px;
        }
    </style>
@endpush

<div @include('crud::inc.field_wrapper_attributes') >
    <label>{{ $field['label'] }}</label>
    <input type='hidden' name='{{ $field['name'] }}' value='' />
    <div id="{{ $field['name'] }}-existing" class="dropzone dropzone-previews">
        @foreach(old($field['name']) ?? $field['value'] ?? $field['default'] ?? [] as $key => $file_path)

            @php
            $path = $file_path;

            if(!$path) {
                continue;
            }

            if(!starts_with($path, 'http')) {
                $file = new \App\Helpers\FileHelper;

                // If it's on a disk
                if(isset($field['disk'])) {
                    $disk = Storage::disk($field['disk']);

                    // Replace video extension
                    if($file::isVideo($disk->path($path))) {
                        $path = $file::replaceExtension($disk->url($path), 'jpg');
                    }
                }
                else {

                    // Replace video extension
                    if($file::isVideo($path)) {
                        $path = $file::replaceExtension($path, 'jpg');
                    }
                }
            }
            @endphp

            <div class="dz-preview dz-image-preview dz-complete">
                <input type="hidden" name="{{ $field['name'] }}[]" value="{{ $file_path }}" />
                <div class="dz-image-no-hover" style="background-image:url('{{ url($path) }}');" /></div>
                <a class="dz-remove dz-remove-existing" href="javascript:undefined;">{{ trans('backpack::dropzone.remove_file') }}</a>
            </div>
        @endforeach
    </div>
    <div id="{{ $field['name'] }}-dropzone" class="dropzone dropzone-target"></div>
    <div id="{{ $field['name'] }}-hidden-input" class="hidden"></div>

    <template class="dropzone {{ $field['name'] }}">
        <div class="dz-preview dz-file-preview">
            <input type="hidden" name="{{ $field['name'] }}[]" class="dropzone-filename-field" />
            <div class="dz-image-no-hover"><img data-dz-thumbnail /></div>
            <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
            <div class="dz-error-message"><span data-dz-errormessage></span></div>
            <div class="dz-success-mark">
            <svg version="1.2" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0" y="0" width="54px" height="54px" viewBox="0 0 54 54">
                <path fill="#FFF" opacity="0.8" d="M27,0C12,0,0,12,0,27s12,27,27,27s27-12,27-27S42,0,27,0zM43,23l-17,17c-2,2-4,2-6,0c-0-0-0-0-0-0l-9-9c-2-2-2-4,0-6c2-2,4-2,6,0l6,6l14-14 c2-2,4-2,6,0C45,19,45,22,43,23z"/>
            </svg>
            </div>
            <div class="dz-error-mark">
                <svg version="1.2" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0" y="0" width="54px" height="54px" viewBox="0 0 54 54">
                    <path fill="#FFF" opacity="0.8" d="M27,0C12,0,0,12,0,27s12,27,27,27c14,0,27-12,27-27S41,0,27,0z M38,34c1,1,1,4,0,5c-1,1-4,1-5,0L27,34l-5,5c-1,1-4,1-5,0c-1-1-1-4,0-5l5-5l-5-5c-1-1-1-4,0-5c1-1,4-1,5,0l5,5l5-5c1-1,4-1,5,0c1,1,1,4,0,5L32,29L38,34z"/>
                </svg>
            </div>
        </div>
    </template>
</div>

@push('crud_fields_scripts')
    <script src="{{ asset('gemadigital/js/dropzone.min.js') }}"></script>
    <script src="{{ asset('gemadigital/js/sortable.min.js') }}"></script>
    <script>
        Dropzone.autoDiscover = false;

        @php
            $baseURL = '/admin/dropzone/' . implode('/', [
                $field['disk'] ?? 'uploads',
                $field['name'],
                str_replace('/', '_', $field['path']),
                $field['media'],
                json_encode($field['sizes'] ?? []),
            ]) . '/';

            $uploadURL = $baseURL . (($field['save_original'] ?? 0) ? 1 : 0) . '/' . ($field['quality'] ?? 0);
            $removeURL = $baseURL . str_replace('\\', '_', $field['class']) . '/remove';
        @endphp

        $(document).ready(function() {
            let template = document.createElement('div');
            template.appendChild(document.querySelector('template.dropzone.{{ $field['name'] }}').content.cloneNode(true));

            let dz = $("div#{{ $field['name'] }}-dropzone");

            dz.dropzone({
                url: "{{ $uploadURL }}",
                headers: {
                    'X-CSRF-Token': '{{ csrf_token() }}'
                },

                dictDefaultMessage: "{{ trans('backpack::dropzone.drop_to_upload') }}",
                dictFallbackMessage: "{{ trans('backpack::dropzone.not_supported') }}",
                dictFallbackText: null,
                dictInvalidFileType: "{{ trans('backpack::dropzone.invalid_file_type') }}",
                dictFileTooBig: "{{ trans('backpack::dropzone.file_too_big') }}",
                dictResponseError: "{{ trans('backpack::dropzone.response_error') }}",
                dictMaxFilesExceeded: "{{ trans('backpack::dropzone.max_files_exceeded') }}",
                dictCancelUpload: "{{ trans('backpack::dropzone.cancel_upload') }}",
                dictCancelUploadConfirmation: "{{ trans('backpack::dropzone.cancel_upload_confirmation') }}",
                dictRemoveFile: "{{ trans('backpack::dropzone.remove_file') }}",

                addRemoveLinks: true,
                previewsContainer: "div#{{ $field['name'] }}-existing",
                hiddenInputContainer: "div#{{ $field['name'] }}-hidden-input",
                previewTemplate: template.innerHTML,

                success: function(file, response, request) {
                    if(response.thumbnail) {
                        file.previewElement.querySelector('img').src = '/' + response.thumbnail;
                    }

                    if (response.success) {
                        file.previewElement.querySelector('.dropzone-filename-field').value = response.filename;
                    }

                    $(".dz-remove").off('click').on('click', removeFiles);
                },
            });

            let el = document.getElementById('{{ $field['name'] }}-existing');
            let sortable = new Sortable(el, {
                group: "{{ $field['name'] }}-sortable",
                handle: ".dz-preview",
                draggable: ".dz-preview",
                scroll: false,
            });

            $('dz-remove-existing, .dz-remove').on('click', removeFiles);

            function removeFiles(e) {
                e.preventDefault();
                e.stopPropagation();
                var element = $(this).closest('.dz-preview').remove();

                var file = $(this).siblings('input').val();

                if(file) {
                    $.ajax({
                        headers: {'X-CSRF-Token': '{{ csrf_token() }}'},
                        accepts: {json: 'application/json'},
                        url: '{{ $removeURL }}',
                        method: 'POST',
                        data: {
                            id: {{ isset($id) ? $id : '0' }},
                            filepath: file,
                        }
                    }).done(function(e) {
                        $(element).closest('.dz-preview').remove();
                    })
                } else {
                    $(this).closest('.dz-preview').remove();
                }
            }

        });
    </script>
@endpush
