<template>
  <!-- Mobile Sidebar -->
  <Sheet :is-open="isMobileOpen" side="left" @close="closeMobile">
    <SheetHeader class="flex flex-row items-center justify-between">
      <SheetTitle class="flex items-center space-x-2">
        <Icon name="zap" class="h-6 w-6 text-primary" />
        <span>FC Autoposter</span>
      </SheetTitle>
      <Button variant="ghost" size="icon" @click="closeMobile">
        <Icon name="x" class="h-4 w-4" />
      </Button>
    </SheetHeader>
    <SheetContent>
      <SidebarContent @navigate="handleNavigate" />
    </SheetContent>
  </Sheet>

  <!-- Desktop Sidebar -->
  <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
    <div class="flex grow flex-col gap-y-5 overflow-y-auto border-r bg-card px-6 py-6">
      <!-- Logo -->
      <div class="flex h-16 shrink-0 items-center">
        <Icon name="zap" class="h-8 w-8 text-primary" />
        <span class="ml-2 text-xl font-bold text-foreground">FC Autoposter</span>
      </div>
      
      <!-- Desktop Navigation -->
      <SidebarContent @navigate="handleNavigate" />
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'
import { 
  Sheet, 
  SheetHeader, 
  SheetTitle, 
  SheetContent,
  Button, 
  Icon 
} from '@/components/ui'
import SidebarContent from './SidebarContent.vue'

defineProps({
  isMobileOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['closeMobile', 'navigate'])

const closeMobile = () => {
  emit('closeMobile')
}

const handleNavigate = (route) => {
  emit('navigate', route)
  emit('closeMobile') // Close mobile sidebar when navigating
}
</script>