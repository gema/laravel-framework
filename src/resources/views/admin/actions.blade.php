@extends('backpack::blank')

@php
  $breadcrumbs = [
      trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
      trans('gemadigital::messages.admin_actions') => false,
  ];
@endphp

@section('header')
    <section class="content-header">
        <div class="container-fluid mb-3">
            <h1>{{ trans('gemadigital::messages.admin_actions') }}</h1>
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
        <div class="card">
            <div class="card-body backpack-profile-form">
                @include('gemadigital::admin.actions_buttons')
            </div>
        </div>
    </div>
@endsection
