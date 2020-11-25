@extends('layouts.app')

@section('title', 'Новости')



@section('content')
<div class="container">

@if ($addForm)

    @if ($canEdit)
        <h1 class="bd-title">Добавить новость</h1>
        @include('form')
        <a href="/news" class="btn btn-warning">Вернуться к  списку</a>
    @endif

@else

    <h1 class="bd-title">Новости</h1>

    @if (!count($items))
        <p>Новостей пока нет</p>
    @endif

    @foreach ($items as $item)
        <p>{{$item->title}} ({{$item->created_at}})</p>
        <p>{{$item->content}} @if ($canEdit) <a onclick="if (!confirm('Удалить?')) return false;" href="/news?delete={{$item->id}}" title="Удалить"><i class="fas fa-trash"></i></a> @endif</p>
        <hr />
    @endforeach

    @if ($canEdit)
    <a href="/news?add" class="btn btn-primary">Добавить новость</a>
    @endif

@endif
</div>
@endsection
