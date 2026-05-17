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
        // Users
        User::create(['name' => 'Super Admin', 'email' => 'admin@anime.test', 'password' => bcrypt('password'), 'role' => 'super_admin', 'username' => 'superadmin']);
        User::create(['name' => 'Admin', 'email' => 'admin2@anime.test', 'password' => bcrypt('password'), 'role' => 'admin', 'username' => 'admin']);
        User::create(['name' => 'User', 'email' => 'user@anime.test', 'password' => bcrypt('password'), 'role' => 'user', 'username' => 'user']);
        User::create(['name' => 'Demo User', 'email' => 'demo@anime.test', 'password' => bcrypt('password'), 'role' => 'user', 'username' => 'demo']);

        // Genres (from reference site)
        $genres = [
            'Action', 'Adventure', 'Comedy', 'Drama', 'Fantasy', 'Horror', 'Mystery',
            'Romance', 'Sci-Fi', 'Slice of Life', 'Sports', 'Supernatural', 'Thriller',
            'Ecchi', 'Harem', 'Isekai', 'Kids', 'Magic', 'Mecha', 'Military',
            'Music', 'Parody', 'Psychological', 'Samurai', 'School', 'Shounen',
            'Shoujo', 'Seinen', 'Josei', 'Space', 'Vampire', 'Yaoi', 'Yuri',
            'Game', 'Cars', 'Historical', 'Demons', 'Police', 'Martial Arts',
            'Dementia', 'Super Power', 'Ghost', 'Family', 'Dub',
        ];
        foreach ($genres as $name) {
            Genre::create(['name' => $name, 'slug' => Str::slug($name)]);
        }

        $genreIds = Genre::pluck('id')->toArray();

        // Sample anime
        $animeList = [
            ['title' => 'One Piece', 'type' => 'TV', 'status' => 'Ongoing', 'year' => 1999, 'rating' => 8.7, 'score' => 8.72, 'episodes_count' => 1100, 'duration' => 24, 'source' => 'Manga', 'studio' => 'Toei Animation', 'description' => 'Follow Monkey D. Luffy and his pirate crew in their adventurous journey to find the legendary treasure One Piece.'],
            ['title' => 'Jujutsu Kaisen', 'type' => 'TV', 'status' => 'Ongoing', 'year' => 2020, 'rating' => 8.8, 'score' => 8.78, 'episodes_count' => 47, 'duration' => 24, 'source' => 'Manga', 'studio' => 'MAPPA', 'description' => 'A boy fights for survival after being drawn into the world of curses and sorcerers.'],
            ['title' => 'Attack on Titan', 'type' => 'TV', 'status' => 'Completed', 'year' => 2013, 'rating' => 9.0, 'score' => 9.05, 'episodes_count' => 94, 'duration' => 24, 'source' => 'Manga', 'studio' => 'MAPPA', 'description' => 'Humanity fights for survival against giant humanoid Titans.'],
            ['title' => 'Demon Slayer', 'type' => 'TV', 'status' => 'Completed', 'year' => 2019, 'rating' => 8.9, 'score' => 8.88, 'episodes_count' => 63, 'duration' => 24, 'source' => 'Manga', 'studio' => 'ufotable', 'description' => 'A young boy becomes a demon slayer to avenge his family and cure his sister.'],
            ['title' => 'Naruto Shippuden', 'type' => 'TV', 'status' => 'Completed', 'year' => 2007, 'rating' => 8.3, 'score' => 8.27, 'episodes_count' => 500, 'duration' => 24, 'source' => 'Manga', 'studio' => 'Studio Pierrot', 'description' => 'Naruto returns after two years of training to face even greater threats.'],
            ['title' => 'Death Note', 'type' => 'TV', 'status' => 'Completed', 'year' => 2006, 'rating' => 8.9, 'score' => 8.93, 'episodes_count' => 37, 'duration' => 24, 'source' => 'Manga', 'studio' => 'Madhouse', 'description' => 'A genius high school student gains the power to kill anyone by writing their name in a notebook.'],
            ['title' => 'Fullmetal Alchemist: Brotherhood', 'type' => 'TV', 'status' => 'Completed', 'year' => 2009, 'rating' => 9.1, 'score' => 9.12, 'episodes_count' => 64, 'duration' => 24, 'source' => 'Manga', 'studio' => 'Bones', 'description' => 'Two brothers search for the Philosopher\'s Stone to restore their bodies after a failed alchemy experiment.'],
            ['title' => 'My Hero Academia', 'type' => 'TV', 'status' => 'Ongoing', 'year' => 2016, 'rating' => 8.2, 'score' => 8.24, 'episodes_count' => 159, 'duration' => 24, 'source' => 'Manga', 'studio' => 'Bones', 'description' => 'A boy without superpowers strives to become the greatest hero in a world of superpowers.'],
            ['title' => 'Spy x Family', 'type' => 'TV', 'status' => 'Ongoing', 'year' => 2022, 'rating' => 8.6, 'score' => 8.56, 'episodes_count' => 37, 'duration' => 24, 'source' => 'Manga', 'studio' => 'WIT Studio', 'description' => 'A spy creates a fake family for a mission but discovers they are all hiding secrets.'],
            ['title' => 'Chainsaw Man', 'type' => 'TV', 'status' => 'Completed', 'year' => 2022, 'rating' => 8.7, 'score' => 8.73, 'episodes_count' => 12, 'duration' => 24, 'source' => 'Manga', 'studio' => 'MAPPA', 'description' => 'A young man with a chainsaw devil partner joins a public safety organization to fight devils.'],
            ['title' => 'Solo Leveling', 'type' => 'TV', 'status' => 'Ongoing', 'year' => 2024, 'rating' => 8.8, 'score' => 8.82, 'episodes_count' => 12, 'duration' => 24, 'source' => 'Webtoon', 'studio' => 'A-1 Pictures', 'description' => 'In a world of hunters and monsters, the weakest hunter gains a unique leveling-up ability.'],
            ['title' => 'One Punch Man', 'type' => 'TV', 'status' => 'Completed', 'year' => 2015, 'rating' => 8.6, 'score' => 8.59, 'episodes_count' => 24, 'duration' => 24, 'source' => 'Webcomic', 'studio' => 'Madhouse', 'description' => 'A hero who can defeat any enemy with a single punch searches for a worthy opponent.'],
            ['title' => 'Steins;Gate', 'type' => 'TV', 'status' => 'Completed', 'year' => 2011, 'rating' => 9.0, 'score' => 9.04, 'episodes_count' => 24, 'duration' => 24, 'source' => 'Visual Novel', 'studio' => 'White Fox', 'description' => 'A self-proclaimed mad scientist accidentally invents time travel and must deal with its consequences.'],
            ['title' => 'Hunter x Hunter', 'type' => 'TV', 'status' => 'Completed', 'year' => 2011, 'rating' => 8.8, 'score' => 8.79, 'episodes_count' => 148, 'duration' => 24, 'source' => 'Manga', 'studio' => 'Madhouse', 'description' => 'A young boy embarks on a journey to become a Hunter and find his missing father.'],
            ['title' => 'Kimi no Na wa (Your Name)', 'type' => 'Movie', 'status' => 'Completed', 'year' => 2016, 'rating' => 8.8, 'score' => 8.84, 'episodes_count' => 1, 'duration' => 106, 'source' => 'Original', 'studio' => 'CoMix Wave Films', 'description' => 'Two strangers find themselves mysteriously swapping bodies and must navigate each other\'s lives.'],
            ['title' => 'Vinland Saga', 'type' => 'TV', 'status' => 'Ongoing', 'year' => 2019, 'rating' => 8.7, 'score' => 8.74, 'episodes_count' => 48, 'duration' => 24, 'source' => 'Manga', 'studio' => 'WIT Studio', 'description' => 'A young Viking seeks revenge against the man who killed his father in war-torn medieval Europe.'],
            ['title' => 'Tokyo Revengers', 'type' => 'TV', 'status' => 'Completed', 'year' => 2021, 'rating' => 8.0, 'score' => 8.03, 'episodes_count' => 50, 'duration' => 24, 'source' => 'Manga', 'studio' => 'Liden Films', 'description' => 'A man travels back in time to save his ex-girlfriend from a gang-related tragedy.'],
            ['title' => 'Bleach', 'type' => 'TV', 'status' => 'Ongoing', 'year' => 2004, 'rating' => 8.1, 'score' => 8.11, 'episodes_count' => 390, 'duration' => 24, 'source' => 'Manga', 'studio' => 'Studio Pierrot', 'description' => 'A teenager with the ability to see ghosts becomes a Soul Reaper and battles evil spirits.'],
            ['title' => 'Cowboy Bebop', 'type' => 'TV', 'status' => 'Completed', 'year' => 1998, 'rating' => 8.8, 'score' => 8.75, 'episodes_count' => 26, 'duration' => 24, 'source' => 'Original', 'studio' => 'Sunrise', 'description' => 'A ragtag crew of bounty hunters travels through space in search of adventure and the past.'],
            ['title' => 'Dragon Ball Z', 'type' => 'TV', 'status' => 'Completed', 'year' => 1989, 'rating' => 8.2, 'score' => 8.17, 'episodes_count' => 291, 'duration' => 24, 'source' => 'Manga', 'studio' => 'Toei Animation', 'description' => 'Goku and his friends defend Earth against powerful villains while searching for the Dragon Balls.'],
        ];

        foreach ($animeList as $data) {
            $data['slug'] = Str::slug($data['title']);
            $data['featured'] = in_array($data['title'], ['One Piece', 'Jujutsu Kaisen', 'Attack on Titan', 'Demon Slayer', 'Solo Leveling']);
            $anime = Anime::create($data);

            // Attach 2-4 random genres
            $selectedGenres = array_rand(array_flip($genreIds), rand(2, 4));
            $anime->genres()->attach($selectedGenres);

            // Create 3-5 episodes per anime
            $epCount = min(5, $data['episodes_count']);
            for ($i = 1; $i <= $epCount; $i++) {
                Episode::create([
                    'anime_id' => $anime->id,
                    'number' => $i,
                    'title' => "Episode {$i}",
                    'description' => "Episode {$i} of {$data['title']}",
                    'duration' => $data['duration'] * 60, // stored in seconds for the player
                    'has_sub' => true,
                    'has_dub' => $i % 3 === 0,
                    'created_by' => 1,
                ]);
            }
        }
    }
}
