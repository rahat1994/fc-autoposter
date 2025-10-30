# CORS Configuration Guide

## Overview
This guide helps resolve CORS (Cross-Origin Resource Sharing) issues when developing with the FC Autoposter admin panel.

## Common CORS Issues

### Problem: "Access to script blocked by CORS policy"
When your WordPress site is on a different domain than the Vite dev server, you'll see this error.

### Solution: Configure Allowed Origins

#### 1. Identify Your WordPress Domain
Common local development domains:
- `http://testing-ground.test` (Laravel Herd)
- `http://testing_ground.test` (Alternative)
- `http://localhost:8080` (MAMP/XAMPP)
- `http://127.0.0.1:8080`

#### 2. Update Vite Configuration
Edit `admin/vite.config.js` and add your domain to `ALLOWED_ORIGINS`:

```javascript
const ALLOWED_ORIGINS = [
  'http://localhost',
  'https://localhost',
  'http://your-domain.test',    // Add your domain here
  'https://your-domain.test',   // HTTPS version
  // ... other domains
]
```

#### 3. Restart Development Server
```bash
npm run dev:host
```

## Automated Tools

### Domain Detection
```bash
npm run detect-domain
```
This script analyzes your setup and suggests possible domains.

### CORS Testing  
```bash
node scripts/test-cors.js http://your-domain.test
```
Tests if CORS is configured correctly.

### Auto-Configuration
```bash
npm run config-domain
```
Automatically updates vite.config.js with detected domains.

## Manual Configuration Steps

### Step 1: Find Your WordPress URL
1. Open your WordPress admin panel
2. Note the URL in your browser address bar
3. This is your WordPress domain

### Step 2: Configure Vite
Add your WordPress domain to the ALLOWED_ORIGINS array in `vite.config.js`.

### Step 3: Test CORS
Use the test script to verify configuration:
```bash
node scripts/test-cors.js http://your-wordpress-domain
```

### Step 4: Restart Development
```bash
npm run dev:host
```

## Network Access

### Host Binding
The dev server runs with `--host` flag to accept external connections:
- Local: `http://localhost:5173/`
- Network: `http://YOUR-IP:5173/`

### Firewall Considerations
Ensure port 5173 is not blocked by your firewall.

## WordPress Integration

### Environment Detection
The plugin automatically detects development mode and loads scripts from the Vite server.

### Debug Information
Enable `WP_DEBUG` in WordPress to see admin notices with:
- Current environment mode
- Dev server URL
- CORS status

## Troubleshooting

### Issue: "Development server not running"
**Solution:**
1. Check if Vite is running: `npm run dev:host`
2. Verify port 5173 is accessible
3. Check firewall settings

### Issue: Scripts load but no hot reload
**Solution:**
1. Ensure WebSocket connection is working
2. Check browser console for connection errors
3. Try restarting the dev server

### Issue: CORS still blocked after configuration
**Solution:**
1. Clear browser cache
2. Check exact domain spelling
3. Ensure both HTTP and HTTPS variants are included
4. Restart dev server after config changes

## Environment Variables

### Custom Dev Server Port
```bash
node scripts/set-env.js development 3000
```

### Custom Dev Server Host
Edit `vite.config.js`:
```javascript
const DEV_HOST = '0.0.0.0'  // Accept all connections
const DEV_PORT = 5173       // Your preferred port
```

## Production Mode

CORS issues don't affect production mode since assets are served from WordPress itself.

Switch to production mode:
```bash
npm run build
```

## Security Notes

- CORS is only configured for development
- Production builds don't need CORS configuration
- Only add trusted domains to ALLOWED_ORIGINS
- The dev server should not be accessible from public networks

## Quick Commands Reference

```bash
# Start development with CORS support
npm run dev:host

# Detect your local domain
npm run detect-domain

# Test CORS configuration
node scripts/test-cors.js http://your-domain.test

# Auto-configure domains
npm run config-domain

# Switch to production mode
npm run build
```