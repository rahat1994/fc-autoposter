# shadcn/ui Components Setup

This project now includes shadcn/ui components for Vue 3. Below is a guide on how to use them in your WordPress plugin's admin interface.

## Available Components

### Button
```vue
<template>
  <div>
    <!-- Primary button -->
    <Button>Click me</Button>
    
    <!-- Secondary button -->
    <Button variant="secondary">Secondary</Button>
    
    <!-- Outline button -->
    <Button variant="outline">Outline</Button>
    
    <!-- Destructive button -->
    <Button variant="destructive">Delete</Button>
    
    <!-- Ghost button -->
    <Button variant="ghost">Ghost</Button>
    
    <!-- Link button -->
    <Button variant="link">Link</Button>
    
    <!-- Different sizes -->
    <Button size="sm">Small</Button>
    <Button size="lg">Large</Button>
    <Button size="icon">🎉</Button>
  </div>
</template>

<script setup>
import { Button } from '@/components/ui'
</script>
```

### Card Components
```vue
<template>
  <Card>
    <CardHeader>
      <CardTitle>Card Title</CardTitle>
      <CardDescription>Card description goes here</CardDescription>
    </CardHeader>
    <CardContent>
      <p>This is the main content of the card.</p>
    </CardContent>
    <CardFooter>
      <Button>Action</Button>
    </CardFooter>
  </Card>
</template>

<script setup>
import { 
  Card, 
  CardHeader, 
  CardTitle, 
  CardDescription, 
  CardContent, 
  CardFooter,
  Button 
} from '@/components/ui'
</script>
```

### Icon Component (using Lucide Vue Next)
```vue
<template>
  <div>
    <!-- Basic icons -->
    <Icon name="home" />
    <Icon name="user" />
    <Icon name="settings" />
    
    <!-- Styled icons -->
    <Icon name="check" class="text-green-500 h-6 w-6" />
    <Icon name="x" class="text-red-500 h-4 w-4" />
  </div>
</template>

<script setup>
import { Icon } from '@/components/ui'
</script>
```

## Utility Functions

### cn() - Class Name Utility
Use the `cn()` function to merge Tailwind classes with conditional classes:

```vue
<script setup>
import { cn } from '@/lib/utils'

const isActive = ref(true)

const buttonClass = cn(
  'base-class',
  'another-class',
  {
    'active-class': isActive.value,
    'inactive-class': !isActive.value
  }
)
</script>
```

## Theme Customization

The theme is configured using CSS custom properties in `/src/style.css`. You can modify the color scheme by updating the CSS variables:

```css
:root {
  --primary: 221.2 83.2% 53.3%;
  --secondary: 210 40% 96%;
  /* ... other variables */
}
```

## Dark Mode Support

The components include built-in dark mode support. Toggle dark mode by adding the `dark` class to the `html` element:

```javascript
// Enable dark mode
document.documentElement.classList.add('dark')

// Disable dark mode
document.documentElement.classList.remove('dark')
```

## Adding More Components

To add more shadcn/ui components:

1. Create the component in `/src/components/ui/`
2. Follow the shadcn/ui Vue patterns
3. Export it from `/src/components/ui/index.js`
4. Use the `cn()` utility for class merging
5. Follow the existing component structure

## Dependencies

The following packages were installed for shadcn/ui support:

- `@radix-ui/colors` - Color system
- `class-variance-authority` - Class variant utilities
- `clsx` - Class name utility
- `tailwind-merge` - Tailwind class merging
- `lucide-vue-next` - Icon library

## Examples in Current Code

The main App.vue and components have been updated to use shadcn/ui components:

- `StatsCard.vue` - Uses Card component with semantic colors
- `DataTable.vue` - Uses Card, CardHeader, CardTitle, CardContent, and Button
- `App.vue` - Uses various shadcn/ui components throughout

You can refer to these files to see practical implementations of the components.