<?php

namespace App\Http\Controllers;

use App\Enums\ContentLanguage;
use App\Enums\PostCategory;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $category = PostCategory::tryFrom((string) $request->query('category'));
        $language = ContentLanguage::tryFrom((string) $request->query('language'));
        $filtered = $category || $language;

        $query = Post::published()
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($language, fn ($q) => $q->where('language', $language))
            ->with('media');

        // The featured post leads the first unfiltered page.
        $featured = ! $filtered && ! $request->query('page')
            ? (clone $query)->where('is_featured', true)->latest('published_at')->first()
            : null;

        $posts = $query
            ->when($featured, fn ($q) => $q->whereKeyNot($featured->getKey()))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('news.index', compact('posts', 'featured', 'category', 'language'));
    }

    public function show(Post $post): View
    {
        abort_unless($post->published_at?->isPast(), 404);

        $post->load(['media']);

        return view('news.show', [
            'post' => $post,
            'related' => Post::published()
                ->where('category', $post->category)
                ->whereKeyNot($post->getKey())
                ->with('media')
                ->latest('published_at')
                ->limit(3)
                ->get(),
        ]);
    }
}
