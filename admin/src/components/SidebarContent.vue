<template>
  <Navigation class="flex flex-1 flex-col space-y-1">
    <!-- Main Navigation -->
    <div class="space-y-1">
      <NavigationLink 
        v-for="item in mainNavItems" 
        :key="item.name"
        :class="cn(
          'w-full justify-start text-left',
          item.current ? 'bg-accent text-accent-foreground' : 'text-muted-foreground hover:text-foreground'
        )"
        variant="ghost"
        @click="navigate(item.route)"
      >
        <Icon :name="item.icon" class="mr-3 h-4 w-4" />
        {{ item.name }}
      </NavigationLink>
    </div>

    <!-- Divider -->
    <div class="my-4 border-t border-border"></div>

    <!-- Divider -->
    <div class="my-4 border-t border-border"></div>

    <!-- Settings & Help -->
    <div class="space-y-1">
      <NavigationLink 
        v-for="item in bottomNavItems" 
        :key="item.name"
        :class="cn(
          'w-full justify-start text-left',
          item.current ? 'bg-accent text-accent-foreground' : 'text-muted-foreground hover:text-foreground'
        )"
        variant="ghost"
        @click="navigate(item.route)"
      >
        <Icon :name="item.icon" class="mr-3 h-4 w-4" />
        {{ item.name }}
      </NavigationLink>
    </div>

    <!-- User Profile Section (at bottom) -->
    <div class="mt-auto pt-4">
      <Card class="p-3">
        <div class="flex items-center space-x-3">
          <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center">
            <Icon name="user" class="h-4 w-4 text-primary" />
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-foreground truncate">Admin User</p>
            <p class="text-xs text-muted-foreground truncate">admin@example.com</p>
          </div>
        </div>
      </Card>
    </div>
  </Navigation>
</template>

<script setup>
import { ref } from 'vue'
import { Navigation, NavigationLink, Icon, Card } from '@/components/ui'
import { cn } from '@/lib/utils'

const emit = defineEmits(['navigate'])

// Navigation items
const mainNavItems = ref([
  { name: 'Dashboard', icon: 'home', route: 'dashboard', current: true },
  { name: 'Posts', icon: 'fileText', route: 'posts', current: false },
  { name: 'Schedule', icon: 'calendar', route: 'schedule', current: false },
  { name: 'Analytics', icon: 'barChart3', route: 'analytics', current: false },
  { name: 'Templates', icon: 'layout', route: 'templates', current: false },
])

const socialAccounts = ref([
  { name: 'Twitter', icon: 'twitter', route: 'twitter', connected: true },
  { name: 'Facebook', icon: 'facebook', route: 'facebook', connected: false },
  { name: 'LinkedIn', icon: 'linkedin', route: 'linkedin', connected: true },
  { name: 'Instagram', icon: 'instagram', route: 'instagram', connected: false },
])

const bottomNavItems = ref([
  { name: 'Settings', icon: 'settings', route: 'settings', current: false },
  { name: 'Help & Support', icon: 'helpCircle', route: 'help', current: false },
])

const navigate = (route) => {
  // Update current state
  mainNavItems.value.forEach(item => item.current = item.route === route)
  bottomNavItems.value.forEach(item => item.current = item.route === route)
  
  // Emit navigation event
  emit('navigate', route)
}
</script>