{{-- Login by email code (MAGIC_LINK_LOGIN): the theme's my_account without passwords. The email is the login, so it is
     read-only: Backpack only changes it with the current password (AccountInfoRequest), which nobody knows now --}}
@extends(backpack_view('blank'))

@section('after_styles')
    <style media="screen">
        .backpack-profile-form .required::after {
            content: ' *';
            color: red;
        }
    </style>
@endsection

@php
  $breadcrumbs = [
      trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
      trans('backpack::base.my_account') => false,
  ];
@endphp

@section('header')
    <section class="content-header">
        <div class="container-fluid mb-3">
            <h1>{{ trans('backpack::base.my_account') }}</h1>
        </div>
    </section>
@endsection

@section('content')
    <div class="row">

        @if (session('success'))
        <div class="col-lg-8">
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        </div>
        @endif

        @if ($errors->count())
        <div class="col-lg-8">
            <div class="alert alert-danger">
                <ul class="mb-1">
                    @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        {{-- UPDATE INFO FORM --}}
        <div class="col-lg-8 mb-4">
            <form class="form" action="{{ route('backpack.account.info.store') }}" method="post">

                {!! csrf_field() !!}

                <div class="card">

                    <div class="card-header">
                        <h3 class="card-title">{{ trans('backpack::base.update_account_info') }}</h3>
                    </div>

                    <div class="card-body backpack-profile-form bold-labels">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                @php
                                    $label = trans('backpack::base.name');
                                    $field = 'name';
                                @endphp
                                <label class="required">{{ $label }}</label>
                                <input required class="form-control" type="text" name="{{ $field }}" value="{{ old($field) ? old($field) : $user->$field }}">
                            </div>

                            <div class="col-md-6 form-group">
                                @php
                                    $label = trans('backpack::base.'.strtolower(config('backpack.base.authentication_column_name')));
                                    $field = backpack_authentication_column();
                                @endphp
                                <label>{{ $label }}</label>
                                <input readonly class="form-control" type="{{ backpack_authentication_column()==backpack_email_column()?'email':'text' }}" name="{{ $field }}" value="{{ $user->$field }}">
                                <small class="form-hint">{{ trans('gemadigital::magic_link.email_readonly') }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-success"><i class="la la-save"></i> {{ trans('backpack::base.save') }}</button>
                        <a href="{{ backpack_url() }}" class="btn">{{ trans('backpack::base.cancel') }}</a>
                    </div>
                </div>

            </form>
        </div>

        @includeWhen(config('backpack.multi-auth.features.two_factor'), 'backpack.multi-auth::auth._two_factor_section', ['user' => $user])

        @includeWhen(config('backpack.multi-auth.features.passkeys'), 'backpack.multi-auth::auth._passkeys_section', ['user' => $user])

        @includeWhen(config('backpack.multi-auth.socialite.enabled'), 'backpack.multi-auth::auth._socialite_section', ['user' => $user])

    </div>
@endsection
