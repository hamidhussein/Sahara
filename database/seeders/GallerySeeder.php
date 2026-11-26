<?php

namespace Database\Seeders;

use App\Models\GalleryAlbum;
use Illuminate\Database\Seeder;

/**
 * Gallery albums named in plan 1.1. The SSWF photos already in
 * database/seeders/images go into their matching albums; the others stay off
 * the Gallery page until editors add photos or videos.
 */
class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $albums = [
            ['Clean Water Plants', 'miachar-water-plant.jpg'],
            ['Eye Camp 2026', 'free-eye-camp.jpg'],
            // ramadan-rations.jpg is an illustration, not a photo, so this album waits for real photos.
            ['Ramadan Packages', null],
            ['Qurbani', null],
            ['Pani Har Ghar Tak – Danyor', 'danyor-water.jpg'],
            ['Muharram Medical Camps', null],
            ['Volunteers’ Appreciation Ceremony', null],
        ];

        foreach ($albums as $i => [$title, $photo]) {
            $album = GalleryAlbum::firstOrCreate(['title' => $title], ['sort_order' => $i + 1]);

            $path = $photo ? database_path('seeders/images/'.$photo) : null;
            if ($path && is_file($path) && ! $album->hasMedia('photos')) {
                $album->addMedia($path)->preservingOriginal()->toMediaCollection('photos');
            }
        }
    }
}
