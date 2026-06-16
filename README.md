# 🎌 AniWaves

> A full-featured anime streaming platform built with **Laravel 11** 🚀  
Browse, search, filter, and watch anime with a powerful admin panel and modern UI.

---

## 🚀 Live Features

![Laravel CI](https://github.com/Md-Moklesar-Rahman-Bappy/anime/actions/workflows/ci.yml/badge.svg)
![CodeQL](https://github.com/Md-Moklesar-Rahman-Bappy/anime/actions/workflows/codeql.yml/badge.svg)
![Release](https://github.com/Md-Moklesar-Rahman-Bappy/anime/actions/workflows/release.yml/badge.svg)

---

## ✨ Features

### 🎬 Anime Platform
- 📚 Anime catalog (A–Z, trending, ongoing, filters)
- 🔎 Advanced search & filtering system
- 🎯 Genre-based browsing
- 🎲 Random anime discovery

---

### ▶️ Video Player
- 🎥 Custom **Plyr.js** player
- 🌐 Multi-source streaming
- 🇯🇵 Language support
- ⏩ Skip intro/outro
- ⌨️ Keyboard shortcuts
- 🔁 Auto-next episode

---

### 👤 User System
- Registration & login
- Email verification
- Profile management
- Watch history tracking
- Personal anime lists:
  - Watching
  - Completed
  - Plan to Watch
  - Dropped

---

### 💬 Community
- Episode comments
- Episode issue reporting
- User feedback system

---

### 🛠️ Admin Panel

- 📊 Dashboard analytics
- 🎬 Anime CRUD management
- 📺 Episode management (multi-source)
- 🏷️ Genre management
- ⭐ Featured slider
- 👥 User & role management
- 🚨 Reports moderation
- 📥 Anime request system
- ⚙️ Global settings

---

### 🔗 Integrations

- 🔌 Jikan API (MyAnimeList import)
- 🧲 External scrapers:
  - Gogoanime
  - Zoro / AniWatch
  - AnimePahe
- 📺 YouTube import (oEmbed)

---

### 📦 Uploads

- Chunked video uploads
- Large file support
- Progress tracking

---

## 🧱 Tech Stack

| Layer | Technology |
|------|-----------|
| Backend | Laravel 11, PHP 8.2+ |
| Frontend | Blade, Tailwind CSS, Alpine.js |
| Player | Plyr.js |
| Build Tool | Vite |
| Database | MySQL / SQLite |
| Auth | Laravel Breeze |

---

## ⚙️ Installation

### 1️⃣ Clone Repo
```bash
git clone https://github.com/Md-Moklesar-Rahman-Bappy/anime.git
cd anime
```

### 2️⃣ Install Dependencies
```bash
composer install
npm install
```

### 3️⃣ Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 4️⃣ Configure Database
```bash
DB_DATABASE=your_db
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

### 5️⃣ Run Migrations
```bash
php artisan migrate
```

### 6️⃣ Build Frontend
```bash
npm run build
```

### 7️⃣ Start Server
```bash
php artisan serve
```

---

### 🧪 Testing
Run tests:
```bash
php artisan test
```

---

### 🔐 Security

- Uses Laravel security best practices
- CSRF protection enabled
- Input validation enforced
- Role-based access control

👉 See ./SECURITY.md

---

### 🤝 Contributing
We welcome contributions!
```bash
git checkout -b feature/my-feature
```
Follow:

PSR-12 coding standard
Conventional commits

👉 See ./CONTRIBUTING.md

---

### 📦 Release System

- Automated semantic versioning
- Auto GitHub releases
- CI/CD pipeline integrated

---

### 📊 Project Structure

```
app/
resources/
routes/
database/
tests/
.github/
```

---

### 📜 License
This project is licensed under the MIT License
👉 See ./LICENSE.md

---

### ⚠️ Disclaimer
This project is for educational and development purposes only.
Ensure compliance with local laws when using external content sources.

---

### 🙌 Credits
Created by:
**Md Moklesar Rahman Bappy**
Laravel Developer & UI/UX Enthusiast

---

### 🌟 Support
If you like this project:
⭐ Star the repo
🍴 Fork it
🧠 Contribute

---

✨ Built with passion using Laravel & modern web technologies.


















































# AniWaves

A full-featured anime streaming website built with Laravel 11. Browse, search, filter, and watch anime episodes with a custom video player, user lists, watch history, and a complete admin panel.

## Features

- **Anime Catalog** — Browse anime by genre, A-Z, newest, updated, ongoing, trending, or advanced filters
- **Video Player** — Custom Plyr.io-based player with multiple server/language sources, YouTube support, skip intro/outro, keyboard shortcuts, auto-next, and light mode
- **User System** — Registration, login, email verification, password reset, profile management (Laravel Breeze)
- **Anime Lists** — Personal categorized lists (Watching, Completed, Plan to Watch, On Hold, Dropped)
- **Watch History** — Tracks episode progress and completion per user
- **Comments** — Per-episode comment system
- **Episode Reporting** — Report broken videos, audio/sync issues, incorrect skip times, etc.
- **Admin Panel** — Full CRUD for anime, episodes, genres; featured slider management; user/role management; report and request moderation; site settings
- **MAL Import** — Import anime metadata from MyAnimeList via the Jikan API (single or batch with resume, filler/recap filtering)
- **External Scrapers** — Import episode sources from Gogoanime, Zoro/AniWatch, AnimePahe
- **YouTube Import** — Import YouTube videos as episodes via oEmbed (optional Data API for duration)
- **Chunked Video Uploads** — Upload large video files in chunks with progress tracking
- **Search & Filtering** — Full-text search with filters by type, status, year, season, country, rating, genres, and multiple sort options

## Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | Laravel 11, PHP 8.2+ |
| **Frontend** | Blade, Tailwind CSS 3, Alpine.js 3 |
| **Video Player** | Plyr.io 3 |
| **Icons** | Font Awesome 6 (CDN) |
| **Build Tool** | Vite + laravel-vite-plugin |
| **Database** | SQLite (default), MySQL/MariaDB supported |
| **Cache/Session/Queue** | Database driver (default) |
| **Auth** | Laravel Breeze |

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js & npm
- SQLite or MySQL/MariaDB

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/Md-Moklesar-Rahman-Bappy/anime.git
   cd anime
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database** in `.env` — SQLite is used by default; no additional config needed. For MySQL, uncomment and fill in the `DB_*` settings.

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Build frontend assets**
   ```bash
   npm run build
   ```

8. **Start the development server**
   ```bash
   php artisan serve
   ```

## Configuration

Key environment variables in `.env`:

| Variable | Description |
|----------|-------------|
| `YOUTUBE_API_KEY` | Optional — YouTube Data API v3 key for video duration |
| `FILESYSTEM_DISK` | Storage disk for uploads (`local` or `s3`) |

### Optional: Link Storage
```bash
php artisan storage:link
```

## Usage

### Default Routes

| Route | Description |
|-------|-------------|
| `/` | Homepage with featured slider, latest episodes, trending, ongoing |
| `/anime/{slug}` | Anime detail page |
| `/watch/{slug}` | Video player page |
| `/genre/{slug}` | Anime by genre |
| `/list/newest` | Newest anime |
| `/list/updated` | Recently updated |
| `/list/ongoing` | Ongoing anime |
| `/list/trending` | Trending anime |
| `/list/a-z/{letter}` | A-Z listing |
| `/list/filter` | Advanced search & filter |
| `/random` | Random anime redirect |
| `/faq`, `/about`, `/contact`, `/dmca`, `/terms` | Static pages |

### Admin Panel

Access at `/admin`. The first user can be assigned admin/super_admin role via `php artisan tinker`:

```php
$user = App\Models\User::find(1);
$user->role = 'super_admin';
$user->save();
```

Admin features:
- **Dashboard** — Site stats overview
- **Anime** — Create/edit/delete anime with genres, images, featured ordering
- **Episodes** — Manage episodes per anime with servers, uploads, YouTube/scraper imports
- **Genres** — Manage genre taxonomy
- **Featured Slider** — Manual reorder or auto-fill modes
- **Users** — List, change roles, delete
- **Reports** — Moderate episode issue reports
- **Requests** — Manage user-submitted anime requests
- **Settings** — Site-wide key-value settings
- **MAL Import** — Search, preview, and import from MyAnimeList
- **External Sources** — Search Gogoanime, Zoro, AnimePahe and import episodes
- **YouTube Import** — Import YouTube videos as episodes

## License

[MIT](https://opensource.org/licenses/MIT)


![CodeQL](https://github.com/Md-Moklesar-Rahman-Bappy/anime/actions/workflows/codeql-analysis.yml/badge.svg)
![Laravel CI](https://github.com/Md-Moklesar-Rahman-Bappy/anime/actions/workflows/ci.yml/badge.svg)
![PHPUnit Tests](https://github.com/Md-Moklesar-Rahman-Bappy/anime/actions/workflows/phpunit.yml/badge.svg)
![Release](https://github.com/Md-Moklesar-Rahman-Bappy/anime/actions/workflows/release.yml/badge.svg)
![Security Audit](https://github.com/Md-Moklesar-Rahman-Bappy/anime/actions/workflows/security-audit.yml/badge.svg)

