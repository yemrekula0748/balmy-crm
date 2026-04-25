@extends('layouts.default')
@section('content')
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text"><h4>Yeni Vitrin</h4></div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('qrmenus.index') }}">QR Menüler</a></li>
                <li class="breadcrumb-item"><a href="{{ route('qrmenus.showcases.index') }}">Vitrinler</a></li>
                <li class="breadcrumb-item active">Yeni</li>
            </ol>
        </div>
    </div>

    <form method="POST" action="{{ route('qrmenus.showcases.store') }}" id="showcase-form">
        @csrf
        @include('modules.qrmenu.showcases._form', ['showcase' => null])
    </form>
</div>
@endsection
