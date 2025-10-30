# Environment Setup Documentation

## Overview
The FC Autoposter plugin now includes an intelligent environment detection system that automatically switches between development and production modes based on npm scripts.

## How It Works

### Environment Detection
- **Development Mode**: Loads assets from Vite dev server for hot reload
- **Production Mode**: Loads pre-built assets from dist/ directory
- **Auto-Detection**: WordPress checks environment config and dev server availability

### Environment Configuration Files

#### 1. `env-config.php`
Auto-generated file that stores current environment state:
```php
return [
    'mode' => 'development|production',
    'dev_server' => 'http://localhost:5174',
    'timestamp' => 1761816299136
];
```

#### 2. `scripts/set-env.js`
Node.js script that generates environment configuration:
```bash
node scripts/set-env.js <mode> [port]
```

### Updated NPM Scripts

```json
{
  "dev": "npm run set-dev-mode && vite",
  "build": "npm run set-prod-mode && vite build",
  "set-dev-mode": "node scripts/set-env.js development",
  "set-prod-mode": "node scripts/set-env.js production"
}
```

## Usage

### Development Mode
```bash
npm run dev
```
This will:
1. Set environment to development
2. Start Vite dev server
3. Enable hot module replacement
4. WordPress loads from dev server

### Production Mode
```bash
npm run build
```
This will:
1. Set environment to production  
2. Build optimized assets
3. WordPress loads from dist/ directory

### Manual Environment Control
```bash
# Set development mode (port 5173 default)
node scripts/set-env.js development

# Set development mode with custom port
node scripts/set-env.js development 5174

# Set production mode
node scripts/set-env.js production
```

## WordPress Integration

### Automatic Mode Detection
The `fc_autoposter_enqueue_admin_scripts()` function now:

1. **Reads environment config** from `env-config.php`
2. **Checks dev server availability** (development mode only)
3. **Loads appropriate assets**:
   - Development: Vite dev server URLs
   - Production: Built assets from manifest.json

### Development Mode Features
- Vite client script for HMR
- Module type scripts
- Admin notices showing current mode
- Dev server connectivity check

### Production Mode Features
- Optimized built assets
- Proper cache headers
- Manifest-based asset loading
- Production admin notices

## Debug Information

When `WP_DEBUG` is enabled, you'll see admin notices indicating:
- Current environment mode
- Dev server URL (development)
- Asset loading status
- Build availability (production)

## Troubleshooting

### Development Mode Issues

**Problem**: Changes not reflecting in WordPress admin
**Solution**: 
1. Ensure dev server is running: `npm run dev`
2. Check correct port in env-config.php
3. Verify WordPress can reach dev server

**Problem**: "Development server not running" error
**Solution**:
1. Start dev server: `npm run dev`  
2. Check firewall/port access
3. Manually set correct port: `node scripts/set-env.js development 5174`

### Production Mode Issues

**Problem**: "Production build not found" error
**Solution**:
1. Run build: `npm run build`
2. Check dist/ directory exists
3. Verify manifest.json is present

**Problem**: Assets not loading correctly  
**Solution**:
1. Clear WordPress cache
2. Rebuild assets: `npm run build`
3. Check file permissions in dist/

## File Structure

```
admin/
├── env-config.php          # Auto-generated environment config
├── scripts/
│   └── set-env.js         # Environment configuration script
├── dist/                  # Production build output
│   ├── manifest.json      # Asset manifest
│   └── assets/           # Built CSS/JS files
├── src/                  # Source files
└── package.json          # Updated npm scripts
```

## Best Practices

1. **Never edit env-config.php manually** - it's auto-generated
2. **Always use npm scripts** for consistent environment setup
3. **Check admin notices** for debugging information
4. **Test both modes** before deployment
5. **Keep dev server running** during development

## Git Considerations

The `env-config.php` file is ignored in git since it's auto-generated. Each developer will have their own environment configuration based on their local setup.

## Security Notes

- Development mode only works when dev server is accessible
- Production mode requires built assets
- Environment config includes timestamp for cache busting
- All admin notices are only shown when WP_DEBUG is enabled