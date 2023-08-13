<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateArticleRequest;
use App\Models\Tag;
use App\Models\Article;
use App\Models\Category;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\StoreArticleRequest;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $articles = Article::with([
            'user',
            'tags'
        ])->latest()->simplePaginate();

        return view('articles.index', compact('articles'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {

        return view('articles.create', $this->getFormData());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreArticleRequest $request)
    {

        $article = Article::create([
            'slug' => Str::slug($request->title),
            'user_id' => auth()->id(),
            'status' => $request->status === "on",
        ] + $request->validated);

        $article->tags()->attach($request->tags);

        return redirect(route('articles.index'))->with('message', 'Article created');

    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article): View
    {
        return view('articles.show', compact('article'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Article $article): View
    {

        return view('articles.edit', array_merge(compact('article'), $this->getFormData()));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateArticleRequest $request, Article $article): RedirectResponse
    {
        $article->update($request->validated() + ['slug' => Str::slug($request->title)]);

        $article->tags()->sync($request->tags);

        return redirect(route('dashboard'))->with('message', 'Article updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();

        return redirect(route('dashboard'))->with('message', 'Article deleted');
    }

    private function getFormData(): array
    {
        $categories = Category::pluck('name', 'id');
        $tags = Tag::pluck('name', 'id');
        return compact('categories', 'tags');
    }
}