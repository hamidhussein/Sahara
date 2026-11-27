<?php

namespace App\Http\Controllers;

use App\Models\GalleryAlbum;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        return view('gallery.index', [
            // Albums without photos or videos stay off the page until editors fill them.
            'albums' => GalleryAlbum::published()->with('media')->get()->filter->hasContent()->values(),
        ]);
    }

    public function show(GalleryAlbum $album): View
    {
        $album->load('media');
        abort_unless($album->is_published && $album->hasContent(), 404);

        return view('gallery.show', ['album' => $album]);
    }
}
