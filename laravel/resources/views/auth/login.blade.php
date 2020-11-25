@extends('layouts.app')

@section('content')
<div class="container">



<h1 class="bd-title">Авторизация</h1>

<form role="form" method="POST" action="{{ url('/login') }}">
    {!! csrf_field() !!}

    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
        <label>Почта</label>

            <input type="email" class="form-control" name="email" value="{{ old('email') }}">

            @if ($errors->has('email'))
                <span class="help-block">
                    <strong>{{ $errors->first('email') }}</strong>
                </span>
            @endif
    </div>

    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
        <label class="control-label">Пароль</label>

            <input type="password" class="form-control" name="password">

            @if ($errors->has('password'))
                <span class="help-block">
                    <strong>{{ $errors->first('password') }}</strong>
                </span>
            @endif
    </div>

    <div class="form-group form-check">
        <input type="checkbox" name="remember" class="form-check-input" id="exampleCheck1">
        <label class="form-check-label" for="exampleCheck1">Запомнить меня</label>
    </div>


    <div class="form-group">
        <button type="submit" class="btn btn-primary">
            <i class="fa fa-btn fa-sign-in"></i>Вход
        </button>

        <a class="btn btn-link" href="{{ url('/register') }}">Регистрация</a>

        <a class="btn btn-link" href="{{ url('/password/reset') }}">Забыли свой пароль?</a>
    </div>
</form>

</div>
@endsection
