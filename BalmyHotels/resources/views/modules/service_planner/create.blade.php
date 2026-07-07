@extends('layouts.default')
@section('title', 'Yeni Servis Plani')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Yeni Servis Plani</h4>
                <span>Servis sayisi, koltuk kapasitesi ve kalkis noktasi tanimlayin</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 d-flex justify-content-sm-end mt-2 mt-sm-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('service-planner.index') }}">Servis Planlayici</a></li>
                <li class="breadcrumb-item active">Yeni Plan</li>
            </ol>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            Lutfem formu kontrol edin. Eksik veya hatali alanlar var.
        </div>
    @endif

    <form action="{{ route('service-planner.store') }}" method="POST">
        @csrf
        @include('modules.service_planner._form')

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('service-planner.index') }}" class="btn btn-outline-secondary">Iptal</a>
            <button type="submit" class="btn" style="background:#c19b77;border-color:#c19b77;color:#fff;">Plani Kaydet</button>
        </div>
    </form>
</div>
@endsection
