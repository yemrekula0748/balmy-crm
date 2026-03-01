@extends('layouts.fullwidth')

@section('content')
    <div class="col-md-6">
        <div class="authincation-content">
            <div class="row no-gutters">
                <div class="col-xl-12">
                    <div class="auth-form">
                        <div class="text-center mb-3">
                            <a href="{{ url('/')}}">
                                <img src="{{ asset('images/logo.svg') }}" alt="Balmy Hotels" style="height:55px;">
                            </a>
                        </div>
                        <h4 class="text-center mb-4">Hesabınıza Giriş Yapın</h4>

                        @if($errors->any())
                            <div class="alert alert-danger">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form action="{{ route('login') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label class="mb-1"><strong>E-posta</strong></label>
                                <input type="email" name="email" class="form-control"
                                    value="{{ old('email') }}" placeholder="admin@balmy.com" required autofocus>
                            </div>
                            <div class="form-group position-relative">
                                <label class="mb-1"><strong>Şifre</strong></label>
                                <input type="password" name="password" id="dz-password" class="form-control" required>
                                <span class="show-pass eye">
                                    <i class="fa fa-eye-slash"></i>
                                    <i class="fa fa-eye"></i>
                                </span>
                            </div>
                            <div class="form-row d-flex justify-content-between mt-4 mb-2">
                                <div class="form-group">
                                    <div class="form-check custom-checkbox ms-1">
                                        <input type="checkbox" name="remember" class="form-check-input" id="remember_me">
                                        <label class="custom-control-label" for="remember_me">Beni Hatırla</label>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-block">Giriş Yap</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection    