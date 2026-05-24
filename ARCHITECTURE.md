# Architecture

## Overview

AniWaves is a Laravel 11 application following the MVC pattern with additional service and scraper layers. The frontend uses Blade templates with Tailwind CSS and Alpine.js, with a Plyr.io-based custom video player.

## Directory Structure

```
app/
├── Console/           # Artisan commands
├── Http/
│   ├── Controllers/   # Frontend + Admin controllers
│   ├── Middleware/     # CheckRole middleware
│   └── Requests/      # Form validation requests
├── Models/            # Eloquent models
├── Providers/         # Service providers
├── Services/          # Business logic (Jikan, YouTube, Scrapers)
└── View/              # View composers
```

## Key Layers

### Models

| Model | Table | Purpose |
|-------|-------|---------|
| `Anime` | `anime` | Core anime entity with metadata, genres, episodes |
| `Episode` | `episodes` | Episodes belonging to an anime with video sources |
| `Server` | `servers` | Video server entries per episode (MP4, M3U8, YouTube, embed) |
| `Genre` | `genres` | Genre taxonomy, many-to-many with anime |
| `User` | `users` | Extends default Laravel auth with role and username |
| `Favorite` | `favorites` | User anime lists with category (watching/completed/etc.) |
| `WatchHistory` | `watch_history` | Per-user per-episode progress tracking |
| `Comment` | `comments` | Episode comments |
| `SkipTime` | `skip_times` | Intro/outro timestamps per episode |
| `Report` | `reports` | Episode issue reports |
| `AnimeRequest` | `requests` | User-submitted anime requests |
| `Setting` | `settings` | Key-value site settings |
| `ChunkedUpload` | `chunked_uploads` | Large file upload tracking |

### Controllers

**Frontend:** `HomeController`, `AnimeController`, `WatchController`, `GenreController`, `ListController`, `RandomController`, `StaticController`, `CommentsController`, `FavoritesController`, `ProfileController`

**Admin:** `DashboardController`, `AnimeController`, `EpisodeController`, `GenreController`, `FeaturedController`, `UserController`, `ReportController`, `RequestController`, `SettingController`, `JikanController`, `UploadController`

### Services

- **`JikanService`** — Wraps the Jikan API v4 (MyAnimeList) for importing anime metadata and episodes with rate limiting and retry logic
- **`YouTubeService`** — Fetches video metadata via oEmbed API (free) with optional duration via YouTube Data API v3

### Database

SQLite by default, MySQL/MariaDB supported. Migrations define 18 tables including cache, sessions, and queue support.

### Frontend

- **Blade** templates with layout inheritance (`layouts/app.blade.php`)
- **Tailwind CSS 3** with dark mode (`class="dark"` on `<html>`)
- **Alpine.js 3** for carousel, player controls, modals, dynamic UI
- **Plyr.io 3** custom video player with HTML5 video and YouTube providers
- **Font Awesome 6** via CDN
- **Vite** for asset bundling

### Middleware

`CheckRole` — Route middleware that restricts access to users with specified roles (`super_admin`, `admin`). Applied to all admin routes.

## Data Flow

1. **Anime Import** — Admin uses Jikan API search → preview → import (single or batch). Data mapped from MAL format to local schema.
2. **Episode Sources** — Admin can upload video files directly, provide direct URLs, import from YouTube, or scrape from supported external sites.
3. **Video Playback** — Watch page loads Plyr player with available server sources. User can switch servers/languages. Skip times, watch history, and comments are loaded via the same page.
4. **User Lists** — Favorites controller handles toggling anime into user lists with category management via AJAX.
