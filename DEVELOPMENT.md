# Development Setup

## Prerequisites

- PHP 7.4 or higher
- Composer
- Node.js 18+
- WordPress development environment

## Initial Setup

1. **Install PHP dependencies:**
   ```bash
   composer install
   ```

2. **Install Node.js dependencies:**
   ```bash
   cd admin
   npm install
   ```

3. **Build assets:**
   ```bash
   npm run build
   ```

## Development Workflow

### Adding PHP Packages

To add new PHP packages via Composer:

```bash
composer require vendor/package-name
```

For development-only packages:

```bash
composer require --dev vendor/package-name
```

**Popular packages you might want to add:**

- `monolog/monolog` - Advanced logging
- `guzzlehttp/guzzle` - HTTP client for API calls
- `league/fractal` - Data transformation layer
- `respect/validation` - Powerful validation library
- `carbon/carbon` - Date manipulation library

### Autoloading

The plugin uses PSR-4 autoloading via Composer. All classes in the `app/` directory are automatically loaded using the `FCAutoposter\` namespace.

- `app/Controllers/` → `FCAutoposter\Controllers\`
- `app/Middleware/` → `FCAutoposter\Middleware\`
- `app/Routing/` → `FCAutoposter\Routing\`

### Adding New Classes

1. Create your class in the appropriate `app/` subdirectory
2. Use the correct namespace (e.g., `namespace FCAutoposter\Controllers;`)
3. The class will be automatically available - no manual includes needed

### Regenerating Autoloader

If you add new classes or move files, regenerate the autoloader:

```bash
composer dump-autoload
```

### Available Composer Scripts

- `composer test` - Run PHPUnit tests (when configured)
- `composer autoload` - Regenerate optimized autoloader
- `composer install --no-dev` - Install production dependencies only

## File Structure

```
fc-autoposter/
├── composer.json          # PHP dependencies and autoloading config
├── vendor/               # Composer packages (git-ignored)
├── app/                  # PHP application code (PSR-4 autoloaded)
│   ├── Controllers/
│   ├── Middleware/
│   └── Routing/
├── admin/                # Vue.js admin interface
└── routes/               # Route definitions
```

## Best Practices

1. Always run `composer install` after pulling changes
2. Use `composer install --no-dev` for production deployments
3. Never commit the `vendor/` directory
4. Follow PSR-4 naming conventions for classes and namespaces
5. Regenerate autoloader after structural changes