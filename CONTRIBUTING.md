# 🤝 Contributing Guide

Thank you for your interest in contributing to the **Anime Project** 🎌  
We welcome contributions from everyone — whether it's fixing bugs, adding features, or improving documentation.

---

## 📌 Getting Started

### 1. Fork the Repository

Click the **Fork** button and clone your fork:

```bash
git clone https://github.com/your-username/anime.git
cd anime
```

---

### 2. Install Dependencies
```
composer install
npm install
```

---

### 3. Setup Environment

```
cp .env.example .env
php artisan key:generate
```

### Then configure your database in .env:

```
DB_DATABASE=your_db
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

---

### 4. Run Migrations

```
php artisan migrate
```

---

### 5. Run Development Server

```
php artisan serve
npm run dev
```

---

# 🔄 Development Workflow

## ✅ Create a Branch

Always create a new branch:

```
git checkout -b feature/my-feature
```

Branch naming:

feature/feature-name
bugfix/bug-name
hotfix/urgent-fix
refactor/code-clean

---

### ✅ Make Changes

- Follow coding standards (PSR-12)
- Keep code clean and modular
- Avoid unnecessary changes

---

### ✅ Commit Changes (IMPORTANT)

We use Conventional Commits:
```
feat: add anime filter
fix: resolve video playback issue
docs: update README
refactor: clean service logic
chore: update dependencies
```
Breaking change:
```
feat!: change API structure
``
```

---

# 🧪 Testing
Before submitting your PR:
```
php artisan test
```

# ✅ Requirements:

- All tests must pass
- New features should include tests
- Bug fixes should include regression tests

---

# 🚀 Pull Request Process

### ✅ 1. Push Changes
```
git push origin feature/my-feature
```

---

### ✅ 2. Open Pull Request
When opening a PR:

- Provide clear description
- Link related issue (Fixes #)
- Explain changes

---

### ✅ 3. PR Checklist
Make sure:
 - Code follows project standards
 - Tests pass
 - Documentation updated (if needed)
 - No unnecessary files included

---

### 🔐 Code Quality Guidelines

- ✅ Keep controllers thin (use services)
- ✅ Avoid duplicate code
- ✅ Use proper naming
- ✅ Remove unused imports
- ✅ No debug logs in production

---

### ⚡ Performance Guidelines

- Use eager loading (avoid N+1 queries)
- Optimize database queries
- Use caching where needed

---

### 🔐 Security Guidelines

- Never commit .env or secrets
- Validate all inputs
- Follow secure coding practices

---

### 📦 Release Process
This project uses Automatic Semantic Release.
👉 Versioning is based on commit messages:

| Type | Version |
|--------|--------|
| fix | Patch |
| feat | Minor |
| feat! | Major |

---

### 🤖 Automation Rules

- CI must pass before merging
- CodeQL security scan must pass
- Dependabot handles dependencies
- Deployment is automatic

---

### 🙌 Contribution Types
You can contribute by:

- 🐞 Fixing bugs
- 🚀 Adding new features
- 📚 Improving documentation
- 🎨 Enhancing UI/UX
- ⚡ Improving performance

---

### 💬 Need Help?
If you need help:

- Open an issue
- Start a discussion
- Contact maintainers

---

### 🎯 Final Notes

- Keep changes minimal and focused
- Follow project structure
- Respect other contributors
































# Contributing

Thank you for your interest in contributing to AniWaves.

## Getting Started

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Development Setup

```bash
# Install dependencies
composer install
npm install

# Copy environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Build assets
npm run dev
```

## Coding Standards

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) for PHP code
- Use Blade templates for views with Tailwind CSS classes
- Use Alpine.js for client-side interactivity
- Run `./vendor/bin/pint` to auto-format PHP code before committing

## Pull Request Guidelines

- Keep PRs focused on a single concern
- Write clear commit messages
- Update documentation if needed
- Test your changes thoroughly

## Reporting Issues

Report bugs or suggest features by opening a GitHub issue.
