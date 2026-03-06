@extends('layouts.default')
@section('title', 'Yeni Araç')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Yeni Servis Aracı</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('shuttle.vehicles.index') }}">Servis Araçları</a></li>
                <li class="breadcrumb-item active">Yeni</li>
            </ol>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Araç Bilgileri</h5></div>
                <div class="card-body">
                    <form action="{{ route('shuttle.vehicles.store') }}" method="POST">
                        @csrf
                        @include('modules.shuttle.vehicles._form')
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> Kaydet
                            </button>
                            <a href="{{ route('shuttle.vehicles.index') }}" class="btn btn-outline-secondary">İptal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
