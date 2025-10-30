# FC Autoposter Admin Dashboard

A modern, responsive admin dashboard for the FC Autoposter WordPress plugin, built with Vue.js and Tailwind CSS.

## Features

- **Modern Dashboard Design**: Clean, professional interface using Tailwind CSS
- **Responsive Layout**: Works seamlessly on desktop and mobile devices
- **Component-Based Architecture**: Modular Vue.js components for easy maintenance
- **Real-time Statistics**: Dashboard cards showing key metrics
- **Social Media Management**: Interface for managing connected accounts and posts
- **Demo Data Toggle**: Built-in demo data for testing and presentation

## Technology Stack

- **Vue.js 3**: Modern JavaScript framework with Composition API
- **Tailwind CSS**: Utility-first CSS framework for rapid UI development
- **Vite**: Fast build tool and development server
- **PostCSS**: CSS processing with autoprefixer

## Development Setup

### Prerequisites

- Node.js (latest stable version recommended)
- npm or yarn package manager
- nvm (Node Version Manager) - optional but recommended

### Installation

1. **Navigate to the admin directory:**
   ```bash
   cd wp-content/plugins/fc-autoposter/admin
   ```

2. **Set Node.js version (if using nvm):**
   ```bash
   nvm use node
   ```

3. **Install dependencies:**
   ```bash
   npm install
   ```

### Development Commands

- **Start development server:**
  ```bash
  npm run dev
  ```
  Access the dashboard at `http://localhost:5173`

- **Build for production:**
  ```bash
  npm run build
  ```
  Built files will be in the `dist/` directory

- **Preview production build:**
  ```bash
  npm run preview
  ```

## Project Structure

```
admin/
├── src/
│   ├── components/
│   │   ├── StatsCard.vue      # Reusable statistics card component
│   │   └── DataTable.vue      # Flexible data table component
│   ├── App.vue                # Main dashboard component
│   ├── main.js               # Application entry point
│   └── style.css             # Tailwind CSS directives
├── dist/                     # Built files (generated)
├── index.html               # HTML template
├── package.json             # Dependencies and scripts
├── postcss.config.js        # PostCSS configuration
├── tailwind.config.js       # Tailwind CSS configuration
└── vite.config.js          # Vite build configuration
```

## Dashboard Components

### StatsCard Component
Displays key metrics with optional trend indicators:
- Title and value display
- Icon support with emojis
- Trend percentage with color coding (green for positive, red for negative)

**Props:**
- `title`: Card title
- `value`: Main metric value
- `icon`: Display icon (emoji)
- `change`: Percentage change (optional)

### DataTable Component
Flexible table component for displaying lists:
- Empty state with custom messaging
- Item actions and status indicators
- Hover effects and smooth transitions

**Props:**
- `title`: Table title
- `items`: Array of data items
- `showCreateButton`: Show/hide create button
- `emptyIcon`, `emptyMessage`, `emptySubMessage`: Empty state customization

**Events:**
- `create`: Emitted when create button is clicked
- `item-action`: Emitted when item action is triggered

## Tailwind CSS Configuration

The dashboard uses a custom Tailwind CSS setup:

- **Content paths**: Configured to scan Vue files and JavaScript
- **Responsive design**: Mobile-first approach with breakpoints
- **Custom styling**: Extended theme colors and spacing
- **PostCSS integration**: Automatic vendor prefixing

### Key Tailwind Classes Used

- **Layout**: `grid`, `flex`, `space-x-*`, `space-y-*`
- **Colors**: `bg-gray-*`, `text-gray-*`, `bg-blue-*`, `text-blue-*`
- **Spacing**: `p-*`, `m-*`, `px-*`, `py-*`
- **Typography**: `text-*`, `font-*`
- **Borders**: `rounded-*`, `border-*`
- **Shadows**: `shadow`, `shadow-sm`
- **Transitions**: `transition-colors`, `duration-*`

## Features Overview

### Dashboard Statistics
Four main metric cards showing:
- Total posts count
- Active social media accounts
- Monthly posts count  
- Success rate percentage

### Recent Posts Management
- List view of scheduled and published posts
- Status indicators (Scheduled, Published, Draft, Failed)
- Platform identification
- Quick actions menu

### Connected Accounts
- Visual list of linked social media accounts
- Connection status indicators
- Account management options

### Quick Actions Sidebar
- Create new post
- View posting schedule
- Access settings
- View analytics

### Weekly Summary
Real-time statistics for the current week:
- Posts scheduled vs published
- Engagement rate
- Reach metrics

### Recent Activity Feed
Timeline of recent actions and system events

## Demo Data

The dashboard includes a demo data toggle button (bottom-right corner) that allows you to:
- Preview the interface with sample data
- Test component functionality
- Demonstrate features to stakeholders

## WordPress Integration

This admin panel is designed to integrate with the FC Autoposter WordPress plugin:

1. **Build Process**: Run `npm run build` to generate production files
2. **Asset Integration**: Built files include a manifest for WordPress enqueueing
3. **API Integration**: Ready for WordPress REST API integration
4. **Security**: Follows WordPress coding standards and security practices

## Customization

### Styling
- Modify `tailwind.config.js` for theme customization
- Add custom CSS in `src/style.css`
- Use Tailwind utility classes for component styling

### Components
- Add new components in `src/components/`
- Import and use in `App.vue`
- Follow Vue.js composition API patterns

### Build Configuration
- Modify `vite.config.js` for build settings
- Adjust `postcss.config.js` for CSS processing
- Update `package.json` scripts as needed

## Browser Support

- Modern browsers with ES2015+ support
- Chrome 88+, Firefox 85+, Safari 14+, Edge 88+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Performance

- Optimized bundle size with tree-shaking
- Lazy loading of components (can be implemented)
- CSS purging removes unused Tailwind classes
- Fast development server with HMR (Hot Module Replacement)

## Contributing

When contributing to the dashboard:

1. Follow Vue.js style guide
2. Use Tailwind utility classes over custom CSS
3. Maintain responsive design principles
4. Test in multiple browsers
5. Document new components and features

## License

This project is part of the FC Autoposter WordPress plugin. Please refer to the main plugin license for usage terms.