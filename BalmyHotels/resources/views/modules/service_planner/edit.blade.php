@extends('layouts.default')
@section('title', 'Servis Planini Duzenle')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Servis Planini Duzenle</h4>
                <span>{{ $plan->name }}</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 d-flex justify-content-sm-end mt-2 mt-sm-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('service-planner.index') }}">Servis Planlayici</a></li>
                <li class="breadcrumb-item"><a href="{{ route('service-planner.show', $plan) }}">{{ $plan->name }}</a></li>
                <li class="breadcrumb-item active">Duzenle</li>
            </ol>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            Lutfem formu kontrol edin. Eksik veya hatali alanlar var.
        </div>
    @endif

    <form action="{{ route('service-planner.update', $plan) }}" method="POST">
        @csrf
        @method('PUT')
        @include('modules.service_planner._form')

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('service-planner.show', $plan) }}" class="btn btn-outline-secondary">Iptal</a>
            <button type="submit" class="btn" style="background:#c19b77;border-color:#c19b77;color:#fff;">Degisiklikleri Kaydet</button>
        </div>
    </form>
</div>
@endsection
