@extends('layouts.app') <!-- Assuming you have a layout template -->

@section('content')
    <h1>News Articles</h1>

    @foreach($articles as $article)
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $article->title }}</h5>
                <p class="card-text">{{ $article->description }}</p>
                <a href="{{ route('news.show', $article->id) }}" class="btn btn-primary">Read More</a>
            </div>
        </div>
    @endforeach

    {{ $articles->links() }} <!-- Pagination links -->
@endsection
