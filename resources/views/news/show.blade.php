@extends('layouts.app') <!-- Assuming you have a layout template -->

@section('content')
    <h1>{{ $article->title }}</h1>
    <p class="text-muted">{{ $article->date_added }}</p>
    <p>{{ $article->description }}</p>

    <a href="{{ route('news.index') }}" class="btn btn-secondary">Back to Articles</a>
@endsection
