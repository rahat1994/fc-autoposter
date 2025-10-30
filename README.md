# FC Autoposter

A WordPress plugin with Vue 3 and Vite admin panel.

## Features

- WordPress plugin with admin panel
- Vue 3 for reactive UI components
- Vite for fast development and optimized builds
- Hot Module Replacement (HMR) in development mode

## Installation

1. Clone or download this repository into your WordPress plugins directory:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/rahat1994/fc-autoposter.git
   ```

2. Install dependencies:
   ```bash
   cd fc-autoposter/admin
   npm install
   ```

3. Build the admin panel:
   ```bash
   npm run build
   ```

4. Activate the plugin in WordPress admin panel (Plugins > Installed Plugins > FC Autoposter > Activate)

## Development

To develop with hot module replacement:

1. Enable WordPress debug mode in `wp-config.php`:
   ```php
   define('WP_DEBUG', true);
   ```

2. (Optional) Configure custom dev server URL in `wp-config.php`:
   ```php
   define('FC_AUTOPOSTER_DEV_SERVER', 'http://localhost:5173');
   ```

3. Start the Vite dev server:
   ```bash
   cd admin
   npm run dev
   ```

4. The admin panel will automatically load from the dev server at `http://localhost:5173`
5. Make changes to Vue components in `admin/src/` and see them reflected instantly

**Note:** Development mode only works when `WP_DEBUG` is enabled for security reasons.

## Production Build

To create a production build:

```bash
cd admin
npm run build
```

This will create optimized files in the `admin/dist/` directory.

## Plugin Structure

```
fc-autoposter/
├── fc-autoposter.php       # Main plugin file
├── admin/                  # Admin panel Vue app
│   ├── src/
│   │   ├── main.js        # Vue app entry point
│   │   └── App.vue        # Main Vue component
│   ├── dist/              # Production build output (generated)
│   ├── package.json       # Node dependencies
│   └── vite.config.js     # Vite configuration
└── README.md
```

## Usage

After activating the plugin, you'll find "FC Autoposter" in the WordPress admin menu. Click it to access the admin panel powered by Vue 3.

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Node.js 18 or higher
- npm or yarn

## License

GPL v2 or later