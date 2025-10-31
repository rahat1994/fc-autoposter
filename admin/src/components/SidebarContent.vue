<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { Icon, Card } from '@/components/ui'
import {
  NavigationMenu,
  NavigationMenuContent,
  NavigationMenuIndicator,
  NavigationMenuItem,
  NavigationMenuLink,
  NavigationMenuList,
  NavigationMenuTrigger,
  NavigationMenuViewport,
} from "@/components/ui/navigation-menu"
import { cn } from '@/lib/utils'

const router = useRouter()
const route = useRoute()
const emit = defineEmits(['navigate'])

// Navigation items
const mainNavItems = ref([
  { name: 'Dashboard', icon: 'home', route: 'dashboard' },
  { name: 'Posts', icon: 'fileText', route: 'posts' },
  { name: 'Agents', icon: 'fileText', route: 'agents' },
  // { name: 'Integrations', icon: 'fileText', route: 'integrations' },
  // { name: 'Schedule', icon: 'calendar', route: 'schedule' },
  // { name: 'Analytics', icon: 'barChart3', route: 'analytics' },
])

const bottomNavItems = ref([
  { name: 'Settings', icon: 'settings', route: 'settings' },
  { name: 'Help & Support', icon: 'helpCircle', route: 'help' },
])

// Compute current route name
const currentRoute = computed(() => {
  const name = route.name as string | undefined
  return name?.toLowerCase() || 'dashboard'
})

const navigate = (routeName: string) => {
  router.push(`/${routeName}`)
  emit('navigate', routeName)
}

const isActive = (itemRoute: string) => {
  return currentRoute.value === itemRoute
}
</script>

<template>
  <div class="flex flex-1 flex-col space-y-1">
    <!-- Main Navigation -->
    <NavigationMenu class="w-full justify-start">
      <NavigationMenuList class="flex flex-col space-y-1 w-full">
        <NavigationMenuItem v-for="item in mainNavItems" :key="item.name" class="w-full cursor-pointer">
          <NavigationMenuLink
            :class="cn(
              'w-full px-3 py-2 rounded-md text-sm font-medium text-left flex items-center',
              isActive(item.route) 
                ? 'bg-accent text-accent-foreground' 
                : 'text-muted-foreground hover:text-foreground hover:bg-accent/50'
            )"
            @click="navigate(item.route)"
          >
            <Icon :name="item.icon" class="mr-3 h-4 w-4" />
            {{ item.name }}
          </NavigationMenuLink>
        </NavigationMenuItem>
      </NavigationMenuList>
    </NavigationMenu>

    <!-- Divider -->
    <div class="my-4 border-t border-border"></div>

    <!-- Settings & Help -->
    <NavigationMenu class="w-full justify-start">
      <NavigationMenuList class="flex flex-col space-y-1 w-full">
        <NavigationMenuItem v-for="item in bottomNavItems" :key="item.name" class="w-full">
          <NavigationMenuLink
            :class="cn(
              'w-full px-3 py-2 rounded-md text-sm font-medium text-left flex items-center',
              isActive(item.route) 
                ? 'bg-accent text-accent-foreground' 
                : 'text-muted-foreground hover:text-foreground hover:bg-accent/50'
            )"
            @click="navigate(item.route)"
          >
            <Icon :name="item.icon" class="mr-3 h-4 w-4" />
            {{ item.name }}
          </NavigationMenuLink>
        </NavigationMenuItem>
      </NavigationMenuList>
    </NavigationMenu>

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
  </div>
</template>