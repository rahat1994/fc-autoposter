<template>
  <div class="min-h-screen bg-background">
    <!-- Sidebar -->
    <!-- <Sidebar 
      :is-mobile-open="sidebarOpen" 
      @close-mobile="sidebarOpen = false"
      @navigate="handleNavigation"
    /> -->

    <!-- Main content area -->
    <div>
      <!-- Top navigation for mobile -->
      <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b bg-background px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8 lg:hidden">
        <Button variant="ghost" size="icon" @click="sidebarOpen = true">
          <Icon name="menu" class="h-5 w-5" />
        </Button>
        
        <!-- Mobile header -->
        <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
          <div class="flex items-center">
            <Icon name="zap" class="h-6 w-6 text-primary" />
            <span class="ml-2 text-lg font-semibold">FC Autoposter</span>
          </div>
        </div>
      </div>

      <!-- Page content -->
      <main class="py-6">
        <div class="px-4 sm:px-6 lg:px-8">
          <slot :current-route="currentRoute" />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Sidebar from './Sidebar.vue'
import { Button, Icon } from '@/components/ui'

const sidebarOpen = ref(false)
const currentRoute = ref('dashboard')

const handleNavigation = (route) => {
  currentRoute.value = route
  console.log(`Navigating to: ${route}`)
}
</script>