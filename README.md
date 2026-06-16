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
