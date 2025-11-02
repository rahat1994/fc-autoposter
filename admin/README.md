# FC Autoposter Admin Dashboard

A modern, responsive admin dashboard for the FC Autoposter WordPress plugin, built with Vue.js and Tailwind CSS.

## Features

- **Modern Dashboard Design**: Clean, professional interface using Tailwind CSS
- **Responsive Layout**: Works seamlessly on desktop and mobile devices
- **Component-Based Architecture**: Modular Vue.js components for easy maintenance
- **Real-time Statistics**: Dashboard cards showing key metrics
- **Social Media Management**: Interface for managing connected accounts and posts
- **AI Agents Management**: Complete interface for creating and managing AI assistants
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

## AI Agents Feature

The AI Agents management interface (`AgentsView.vue`) provides a comprehensive system for creating and managing AI assistants for your community automation.

### Key Features

#### Agent Management Dashboard
- **Statistics Overview**: Real-time metrics showing total agents, active agents, interactions, and success rates
- **Agent List Table**: Comprehensive data table with agent details, status indicators, and quick actions
- **Empty State**: Encouraging empty state design for new users with call-to-action buttons
- **Responsive Design**: Fully responsive interface that works on all devices

#### Create Agent Modal
A sophisticated modal dialog for agent creation with:

**Basic Information Section:**
- **Agent Name**: Required field with 50 character limit and real-time counter
- **Description**: Optional textarea with 200 character limit for agent purpose

**Agent Configuration Section:**
- **Agent Type Dropdown**: Pre-defined options with availability status (Customer Support, Product Expert, News Summarizer, Data Analyzer, Content Writer, General Assistant). Each type includes metadata for disabled state, pro features, and coming soon indicators.
- **AI Model Selection**: Choose from GPT-4, GPT-3.5, Claude, or Custom models
- **System Instructions**: Required textarea for defining agent behavior and guidelines

**Capabilities Section:**
- **Web Search Toggle**: Enable/disable web search capabilities
- **File Processing Toggle**: Enable/disable file analysis features

**Validation & UX:**
- Real-time form validation with inline error messages
- Character counters for text inputs
- Disabled submit button until all required fields are valid
- Limitation notice about single agent restriction

#### Agent Details Modal
Detailed view modal showing:
- Complete agent configuration and metadata
- System instructions display
- Capabilities overview with badges
- Creation and modification timestamps
- Quick action buttons (Edit, Delete)

#### Agent Actions
- **View Agent**: Opens detailed modal with all agent information
- **Edit Agent**: Pre-populates creation form for modifications
- **Activate/Deactivate**: Toggle agent status with immediate UI updates
- **Delete Agent**: Remove agents with confirmation

### Technical Implementation

#### Reactive Data Management
```javascript
// Computed statistics that update automatically
const agentStats = computed(() => [
  { title: 'Total Agents', value: agents.value.length.toString(), icon: 'bot' },
  { title: 'Active Agents', value: agents.value.filter(a => a.status === 'Active').length.toString(), icon: 'activity' },
  // ... more stats
])

// Form validation with real-time error handling
const isFormValid = computed(() => {
  return formData.value.name.trim() !== '' &&
         formData.value.type !== '' &&
         formData.value.model !== '' &&
         formData.value.systemPrompt.trim() !== ''
})
```

#### Data Table Configuration
Custom column definitions with:
- **Agent Details Column**: Multi-line display with name, description, model info, and interaction count
- **Status Column**: Dynamic badge with color-coded status indicators
- **Actions Column**: Dropdown menu with contextual actions

#### Agent Type Configuration
Rich type system with metadata:
```javascript
const agentTypes = {
    'customer-support': { label: 'Customer Support', disabled: true, pro: false, comingSoon: true },
    'content-writer': { label: 'Content Writer', disabled: false, pro: false, comingSoon: false },
    // ... more types
}

// Helper functions
const getAgentTypeInfo = (type) => agentTypes[type] || defaultInfo
const isAgentTypeAvailable = (type) => !getAgentTypeInfo(type).disabled
```

#### Form Validation
Comprehensive validation system:
- Required field validation for name, type, model, and system prompt
- Character limit enforcement with visual feedback
- Real-time error display with field-level messaging
- Form state management preventing invalid submissions

### Usage Guidelines

#### Getting Started
1. **Empty State**: New users see an encouraging empty state with clear next steps
2. **Create First Agent**: Click "Create Your First Agent" to open the creation modal
3. **Fill Required Fields**: Name, type, model selection, and system instructions
4. **Configure Capabilities**: Enable web search and file processing as needed
5. **Submit**: Agent is created and immediately visible in the dashboard

#### Best Practices
- **Clear Naming**: Use descriptive names that indicate the agent's purpose
- **Detailed Instructions**: Provide comprehensive system prompts for better AI behavior
- **Appropriate Type**: Select the agent type that best matches intended use case
- **Model Selection**: Choose AI model based on complexity needs and cost considerations

#### Agent Types & Use Cases
- **Customer Support**: Handle inquiries, provide assistance, troubleshoot issues
- **Sales Assistant**: Lead qualification, product information, sales support
- **Data Analyzer**: Process data, generate insights, create reports
- **Content Writer**: Create posts, articles, social media content
- **General Assistant**: Multi-purpose tasks, general inquiries, basic automation

### Component Architecture
Built with modular Vue 3 components:
- **AgentsView.vue**: Main container component
- **DataTable.vue**: Reusable table component with sorting and search
- **Various UI components**: Cards, modals, forms, badges, buttons
- **Shadcn/ui components**: Modern, accessible component library

### Future Enhancements
- **Multi-Agent Support**: Currently limited to 1 agent, expansion planned
- **Agent Templates**: Pre-configured agent types for quick setup
- **Advanced Configuration**: Custom model parameters, response formatting
- **Analytics Dashboard**: Detailed agent performance metrics
- **Agent Collaboration**: Multiple agents working together on tasks

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