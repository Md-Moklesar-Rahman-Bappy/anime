<?php

namespace Database\Seeders;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | USERS
        |--------------------------------------------------------------------------
        */
        User::insert([
            [
                'name' => 'Super Admin',
                'email' => 'admin@anime.test',
                'password' => bcrypt('password'),
                'role' => 'super_admin',
                'username' => 'superadmin',
            ],
            [
                'name' => 'Admin',
                'email' => 'admin2@anime.test',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'username' => 'admin',
            ],
            [
                'name' => 'User',
                'email' => 'user@anime.test',
                'password' => bcrypt('password'),
                'role' => 'user',
                'username' => 'user',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | GENRES
        |--------------------------------------------------------------------------
        */
        $genres = [
            'Action',
            'Adventure',
            'Comedy',
            'Drama',
            'Fantasy',
            'Horror',
            'Mystery',
            'Romance',
            'Sci-Fi',
            'Slice of Life',
            'Sports',
            'Supernatural',
            'Thriller',
            'Ecchi',
            'Harem',
            'Isekai',
            'Kids',
            'Magic',
            'Mecha',
            'Military',
            'Music',
            'Parody',
            'Psychological',
            'Samurai',
            'School',
            'Shounen',
            'Shoujo',
            'Seinen',
            'Josei',
            'Space',
            'Vampire',
            'Yaoi',
            'Yuri',
            'Game',
            'Cars',
            'Historical',
            'Demons',
            'Police',
            'Martial Arts',
            'Super Power',
            'Ghost',
            'Family'
        ];

        foreach ($genres as $name) {
            Genre::firstOrCreate([
                'slug' => Str::slug($name)
            ], [
                'name' => $name
            ]);
        }

        $genreIds = Genre::pluck('id')->toArray();

        /*
        |--------------------------------------------------------------------------
        | ANIME + EPISODES
        |--------------------------------------------------------------------------
        */
        $animeList = [
            [
                'title' => 'One Piece',
                'type' => 'TV',
                'status' => 'Ongoing',
                'year' => 1999,
                'rating' => 8.7,
                'score' => 8.72,
                'episodes_count' => 1100,
                'duration' => 24,
                'studio' => 'Toei Animation',
            ],
            [
                'title' => 'Jujutsu Kaisen',
                'type' => 'TV',
                'status' => 'Ongoing',
                'year' => 2020,
                'rating' => 8.8,
                'score' => 8.78,
                'episodes_count' => 47,
                'duration' => 24,
                'studio' => 'MAPPA',
            ],
            [
                'title' => 'Attack on Titan',
                'type' => 'TV',
                'status' => 'Completed',
                'year' => 2013,
                'rating' => 9.0,
                'score' => 9.05,
                'episodes_count' => 94,
                'duration' => 24,
                'studio' => 'MAPPA',
            ],
        ];

        foreach ($animeList as $data) {

            $anime = Anime::create([
                ...$data,
                'slug' => Str::slug($data['title']),
                'description' => fake()->paragraph(),
                'featured' => true,
                'views' => rand(10000, 1000000),
            ]);

            // ✅ attach genres
            $anime->genres()->attach(
                collect($genreIds)->random(rand(2, 4))->toArray()
            );

            // ✅ create episodes
            $episodeCount = min(5, $data['episodes_count']);

            for ($i = 1; $i <= $episodeCount; $i++) {

                $episode = Episode::create([
                    'anime_id' => $anime->id,
                    'number' => $i,
                    'title' => "Episode $i",
                    'description' => fake()->sentence(),
                    'duration' => $data['duration'] * 60,
                    'has_sub' => true,
                    'has_dub' => $i % 2 === 0,
                    'created_by' => 1,
                ]);

                // ✅ BONUS: create dummy server
                $episode->servers()->create([
                    'label' => 'Server 1',
                    'url' => 'https://sample-video-url.com/video.mp4',
                    'type' => 'mp4',
                    'language' => 'sub',
                    'priority' => 1,
                ]);
            }
        }
    }
}
