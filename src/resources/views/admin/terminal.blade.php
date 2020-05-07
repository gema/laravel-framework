@extends('backpack::blank')

@section('after_styles')
<style media="screen">
    .backpack-profile-form .required::after {
        content: ' *';
        color: red;
    }
</style>
@endsection

@section('after_scripts')
<script type="text/javascript">
    let form = document.querySelector('#terminal');
    form.querySelector('[type=submit]').addEventListener('click', e => {
        fetch('{{ route('terminal_run') }}', {
            method: 'POST',
            credentials: "same-origin",
            body: new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            }
        })
        .then(response => response.text())
        .then(data => document.querySelector('pre').innerText = data);

        e.preventDefault();
    });
</script>
@endsection

@php
  $breadcrumbs = [
      trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
      trans('gemadigital::messages.artisan_terminal') => false,
  ];
@endphp

@section('header')
    <section class="content-header">
        <div class="container-fluid mb-3">
            <h1>{{ trans('gemadigital::messages.artisan_terminal') }}</h1>
        </div>
    </section>
@endsection

@section('content')
<div class="row">

    @if (session('success'))
    <div class="col-lg-12">
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    </div>
    @endif

    @if ($errors->count())
    <div class="col-lg-12">
        <div class="alert alert-danger">
            <ul class="mb-1">
                @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="col-lg-12">
        <form id="terminal" class="form" action="{{ route('terminal_run') }}" method="post">
            {!! csrf_field() !!}

            <div class="card">
                <div class="card-body backpack-profile-form">

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->count())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group">
                        <label class="required">{{ trans('gemadigital::messages.command') }}</label>
                        <hr />
                        <input id="cmd" required class="form-control" type="text" name="cmd" value="">
                    </div>

                    <pre style="min-height: 180px;"></pre>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-success"><span class="ladda-label"><i class="la la-terminal"></i> {{ trans('gemadigital::messages.run') }}</span></button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
