@extends('layouts.default')
@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Vitrin Düzenle</h4>
                <span class="text-muted small">{{ $showcase->title }}</span>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('qrmenus.index') }}">QR Menüler</a></li>
                <li class="breadcrumb-item"><a href="{{ route('qrmenus.showcases.index') }}">Vitrinler</a></li>
                <li class="breadcrumb-item active">Düzenle</li>
            </ol>
        </div>
    </div>

    <form method="POST" action="{{ route('qrmenus.showcases.update', $showcase) }}" id="showcase-form">
        @csrf @method('PUT')
        @include('modules.qrmenu.showcases._form', ['showcase' => $showcase])
    </form>
</div>
@endsection
