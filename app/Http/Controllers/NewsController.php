<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use Illuminate\Http\Request;


class NewsController extends Controller
{
    //
    public function index()
{
    $articles = NewsArticle::orderBy('date_added', 'desc')->paginate(10);

    return view('news.index', compact('articles'));
}
public function show($id)
{
    $article = NewsArticle::findOrFail($id);

    return view('news.show', compact('article'));
}
}
