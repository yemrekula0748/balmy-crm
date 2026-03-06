@extends('layouts.default')
@section('title', 'Güzergah Düzenle')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Güzergah Düzenle</h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('shuttle.routes.index') }}">Güzergahlar</a></li>
                <li class="breadcrumb-item active">Düzenle</li>
            </ol>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ $route->name }}</h5></div>
                <div class="card-body">
                    <form action="{{ route('shuttle.routes.update', $route) }}" method="POST">
                        @csrf @method('PUT')
                        @include('modules.shuttle.routes._form')
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Güncelle
                            </button>
                            <a href="{{ route('shuttle.routes.index') }}" class="btn btn-outline-secondary">İptal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
