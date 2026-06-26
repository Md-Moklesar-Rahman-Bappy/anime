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
