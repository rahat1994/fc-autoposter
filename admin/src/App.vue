<template>
  <AppLayout>
    <template #default="{ currentRoute }">
      <!-- Dashboard -->
      <DashboardContent v-if="currentRoute === 'dashboard'" />
      
      <!-- Posts -->
      <div v-else-if="currentRoute === 'posts'" class="space-y-6">
        <div class="border-b border-border pb-4">
          <h1 class="text-2xl font-bold text-foreground">Posts</h1>
          <p class="text-muted-foreground">Manage your social media posts</p>
        </div>
        <Card class="p-6">
          <p class="text-muted-foreground">Posts management interface coming soon...</p>
        </Card>
      </div>
      
      <!-- Schedule -->
      <div v-else-if="currentRoute === 'schedule'" class="space-y-6">
        <div class="border-b border-border pb-4">
          <h1 class="text-2xl font-bold text-foreground">Schedule</h1>
          <p class="text-muted-foreground">View and manage your posting schedule</p>
        </div>
        <Card class="p-6">
          <p class="text-muted-foreground">Schedule interface coming soon...</p>
        </Card>
      </div>
      
      <!-- Analytics -->
      <div v-else-if="currentRoute === 'analytics'" class="space-y-6">
        <div class="border-b border-border pb-4">
          <h1 class="text-2xl font-bold text-foreground">Analytics</h1>
          <p class="text-muted-foreground">Track your social media performance</p>
        </div>
        <Card class="p-6">
          <p class="text-muted-foreground">Analytics interface coming soon...</p>
        </Card>
      </div>
      
      <!-- Settings -->
      <div v-else-if="currentRoute === 'settings'" class="space-y-6">
        <div class="border-b border-border pb-4">
          <h1 class="text-2xl font-bold text-foreground">Settings</h1>
          <p class="text-muted-foreground">Configure your automation settings</p>
        </div>
        <Card class="p-6">
          <p class="text-muted-foreground">Settings interface coming soon...</p>
        </Card>
      </div>
      
      <!-- Default/Other routes -->
      <DashboardContent v-else />
    </template>
  </AppLayout>
</template>

<script setup>
import AppLayout from './components/AppLayout.vue'
import DashboardContent from './components/DashboardContent.vue'
import { Card } from '@/components/ui'
</script>
