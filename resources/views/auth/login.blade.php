@extends('layouts.app')

@section('title', 'تسجيل الدخول')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card p-4">
                    <h2 class="mb-4 text-center">تسجيل الدخول</h2>

                    <form method="POST" action="{{ route('login.post') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">البريد الإلكتروني</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">كلمة المرور</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember">تذكرني</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">دخول</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('register') }}">إنشاء حساب جديد</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
