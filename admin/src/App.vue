<template>
  <div class="min-h-screen bg-gray-100">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Stats Overview -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <StatsCard
          v-for="stat in stats"
          :key="stat.name"
          :title="stat.name"
          :value="stat.value"
          :icon="stat.icon"
          :change="stat.change"
        />
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
          <!-- Recent Posts -->
          <DataTable
            title="Recent Posts"
            :items="recentPosts"
            :show-create-button="true"
            empty-icon="📝"
            empty-message="No posts scheduled yet"
            empty-sub-message="Create your first automated post to get started"
            @create="createPost"
            @item-action="handlePostAction"
          />
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Quick Actions -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
            </div>
            <div class="p-6 space-y-3">
              <button @click="createPost" 
                      class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md text-sm font-medium transition-colors duration-200 flex items-center justify-center space-x-2">
                <span>➕</span>
                <span>Create New Post</span>
              </button>
              <button @click="viewSchedule" 
                      class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-md text-sm font-medium transition-colors duration-200 flex items-center justify-center space-x-2">
                <span>📅</span>
                <span>View Schedule</span>
              </button>
              <button @click="openSettings" 
                      class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-md text-sm font-medium transition-colors duration-200 flex items-center justify-center space-x-2">
                <span>⚙️</span>
                <span>Settings</span>
              </button>
              <button @click="viewAnalytics" 
                      class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-md text-sm font-medium transition-colors duration-200 flex items-center justify-center space-x-2">
                <span>📊</span>
                <span>Analytics</span>
              </button>
            </div>
          </div>

          <!-- Connected Accounts -->
          <DataTable
            title="Connected Accounts"
            :items="connectedAccounts"
            empty-icon="🔗"
            empty-message="No accounts connected"
            empty-sub-message="Connect your social media accounts to start posting"
            @item-action="handleAccountAction"
          />

          <!-- Schedule Summary -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">This Week</h3>
            </div>
            <div class="p-6">
              <div class="space-y-4">
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Posts Scheduled</span>
                  <span class="text-sm font-medium text-gray-900">{{ weeklyStats.scheduled }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Posts Published</span>
                  <span class="text-sm font-medium text-gray-900">{{ weeklyStats.published }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Engagement Rate</span>
                  <span class="text-sm font-medium text-green-600">{{ weeklyStats.engagement }}%</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Reach</span>
                  <span class="text-sm font-medium text-gray-900">{{ weeklyStats.reach.toLocaleString() }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Activity -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Recent Activity</h3>
            </div>
            <div class="p-6">
              <div v-if="recentActivity.length === 0" class="text-center py-4">
                <div class="text-gray-400 text-2xl mb-2">🎯</div>
                <p class="text-gray-500 text-sm">No recent activity</p>
              </div>
              <div v-else class="space-y-3">
                <div v-for="activity in recentActivity" :key="activity.id" 
                     class="flex items-start space-x-3">
                  <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-blue-600 text-xs">{{ activity.type === 'post' ? '📄' : '🔗' }}</span>
                  </div>
                  <div class="flex-1">
                    <p class="text-sm text-gray-900">{{ activity.message }}</p>
                    <p class="text-xs text-gray-500">{{ activity.time }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Demo Data Toggle -->
    <div class="fixed bottom-4 right-4">
      <button @click="toggleDemoData" 
              class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-full text-sm font-medium shadow-lg transition-colors duration-200">
        {{ showingDemoData ? 'Hide' : 'Show' }} Demo Data
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import StatsCard from './components/StatsCard.vue'
import DataTable from './components/DataTable.vue'

// Demo data toggle
const showingDemoData = ref(false)

// Sample data
const stats = ref([
  { name: 'Total Posts', value: '0', icon: '📊', change: null },
  { name: 'Active Accounts', value: '0', icon: '🔗', change: null },
  { name: 'This Month', value: '0', icon: '📅', change: null },
  { name: 'Success Rate', value: '0%', icon: '✅', change: null }
])

const recentPosts = ref([])

const connectedAccounts = ref([])

const recentActivity = ref([])

const weeklyStats = reactive({
  scheduled: 0,
  published: 0,
  engagement: 0,
  reach: 0
})

// Demo data
const demoStats = [
  { name: 'Total Posts', value: '127', icon: '📊', change: 12 },
  { name: 'Active Accounts', value: '4', icon: '🔗', change: 25 },
  { name: 'This Month', value: '18', icon: '📅', change: -5 },
  { name: 'Success Rate', value: '94%', icon: '✅', change: 3 }
]

const demoPosts = [
  {
    id: 1,
    title: 'New product launch announcement',
    platform: 'Twitter',
    status: 'Published',
    scheduledFor: '2 hours ago'
  },
  {
    id: 2,
    title: 'Weekly newsletter featuring our latest updates',
    platform: 'LinkedIn',
    status: 'Scheduled',
    scheduledFor: 'Tomorrow at 9:00 AM'
  },
  {
    id: 3,
    title: 'Behind the scenes video from our office',
    platform: 'Instagram',
    status: 'Draft',
    scheduledFor: 'Not scheduled'
  }
]

const demoAccounts = [
  {
    id: 1,
    name: '@yourcompany',
    platform: 'Twitter',
    status: 'Active'
  },
  {
    id: 2,
    name: 'Your Company Page',
    platform: 'LinkedIn',
    status: 'Active'
  },
  {
    id: 3,
    name: '@yourcompany.official',
    platform: 'Instagram',
    status: 'Active'
  }
]

const demoActivity = [
  {
    id: 1,
    type: 'post',
    message: 'Post published to Twitter',
    time: '2 hours ago'
  },
  {
    id: 2,
    type: 'account',
    message: 'Instagram account connected',
    time: '1 day ago'
  },
  {
    id: 3,
    type: 'post',
    message: 'LinkedIn post scheduled for tomorrow',
    time: '2 days ago'
  }
]

const demoWeeklyStats = {
  scheduled: 12,
  published: 8,
  engagement: 7.2,
  reach: 15420
}

// Methods
const toggleDemoData = () => {
  showingDemoData.value = !showingDemoData.value
  
  if (showingDemoData.value) {
    // Show demo data
    stats.value = [...demoStats]
    recentPosts.value = [...demoPosts]
    connectedAccounts.value = [...demoAccounts]
    recentActivity.value = [...demoActivity]
    Object.assign(weeklyStats, demoWeeklyStats)
  } else {
    // Reset to empty data
    stats.value = [
      { name: 'Total Posts', value: '0', icon: '📊', change: null },
      { name: 'Active Accounts', value: '0', icon: '🔗', change: null },
      { name: 'This Month', value: '0', icon: '📅', change: null },
      { name: 'Success Rate', value: '0%', icon: '✅', change: null }
    ]
    recentPosts.value = []
    connectedAccounts.value = []
    recentActivity.value = []
    Object.assign(weeklyStats, { scheduled: 0, published: 0, engagement: 0, reach: 0 })
  }
}

const createPost = () => {
  console.log('Create post clicked')
  // TODO: Implement create post functionality
}

const viewSchedule = () => {
  console.log('View schedule clicked')
  // TODO: Implement view schedule functionality
}

const openSettings = () => {
  console.log('Settings clicked')
  // TODO: Implement settings functionality
}

const viewAnalytics = () => {
  console.log('Analytics clicked')
  // TODO: Implement analytics functionality
}

const connectAccount = () => {
  console.log('Connect account clicked')
  // TODO: Implement connect account functionality
}

const handlePostAction = (post) => {
  console.log('Post action clicked for:', post)
  // TODO: Implement post actions (edit, delete, etc.)
}

const handleAccountAction = (account) => {
  console.log('Account action clicked for:', account)
  // TODO: Implement account actions (disconnect, settings, etc.)
}
</script>
