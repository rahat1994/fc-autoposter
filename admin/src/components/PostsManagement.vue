<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-foreground">Posts Management</h1>
        <p class="text-muted-foreground">Manage your AI-generated community posts</p>
      </div>
      <div class="flex items-center space-x-2">
        <Dialog v-model:open="showCreateModal">
          <DialogTrigger as-child>
            <Button>
              <Icon name="plus" class="mr-2 h-4 w-4" />
              Create Post
            </Button>
          </DialogTrigger>
          <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
              <DialogTitle>Create New Post</DialogTitle>
              <DialogDescription>
                Generate a new post using AI integrations
              </DialogDescription>
            </DialogHeader>
            <div class="grid gap-4 py-4">
              <div class="grid grid-cols-4 items-center gap-4">
                <Label for="ai-provider" class="text-right">
                  AI Provider
                </Label>
                <Select v-model="newPost.aiProvider">
                  <SelectTrigger class="col-span-3">
                    <SelectValue placeholder="Select AI provider..." />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="option in aiProviderOptions" :key="option.value" :value="option.value">
                      {{ option.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div class="grid grid-cols-4 items-center gap-4">
                <Label for="topic" class="text-right">
                  Topic
                </Label>
                <Input
                  id="topic"
                  v-model="newPost.topic"
                  placeholder="Enter post topic..."
                  class="col-span-3"
                />
              </div>
              <div class="grid grid-cols-4 items-center gap-4">
                <Label for="schedule" class="text-right">
                  Schedule
                </Label>
                <Input
                  id="schedule"
                  v-model="newPost.scheduledFor"
                  type="datetime-local"
                  class="col-span-3"
                />
              </div>
            </div>
            <div class="flex justify-end space-x-2">
              <Button variant="outline" @click="showCreateModal = false">
                Cancel
              </Button>
              <Button @click="handleCreatePost">
                Create Post
              </Button>
            </div>
          </DialogContent>
        </Dialog>
      </div>
    </div>

    <!-- Filters -->
    <Card>
      <CardContent class="p-4">
        <div class="flex items-center space-x-4">
          <Select v-model="filters.status">
            <SelectTrigger class="w-[200px]">
              <SelectValue placeholder="All Statuses" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">All Statuses</SelectItem>
              <SelectItem v-for="option in statusOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </SelectItem>
            </SelectContent>
          </Select>
          
          <Select v-model="filters.aiProvider">
            <SelectTrigger class="w-[200px]">
              <SelectValue placeholder="All AI Providers" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">All AI Providers</SelectItem>
              <SelectItem v-for="option in aiProviderOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </SelectItem>
            </SelectContent>
          </Select>
          
          <Button variant="outline" @click="clearFilters">
            Clear Filters
          </Button>
        </div>
      </CardContent>
    </Card>

    <!-- Data Table -->
    <Card>
      <CardHeader>
        <CardTitle>AI-Generated Posts</CardTitle>
        <CardDescription>View and manage all your scheduled and published posts</CardDescription>
      </CardHeader>
      <CardContent>
        <DataTable 
          :data="filteredPosts" 
          :columns="columns" 
          search-placeholder="Search posts..."
        />
      </CardContent>
    </Card>
  </div>
</template>

<script setup>
import { ref, computed, h } from 'vue'
import {
  Button,
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
  Input,
  Label,
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
  Badge,
  Icon
} from '@/components/ui'
import DataTable from '@/components/DataTable.vue'

// Sample data
const posts = ref([
  {
    id: 1,
    title: 'Community Discussion: Best WordPress Practices',
    aiProvider: 'OpenAI GPT-4',
    status: 'Published',
    scheduledFor: '2024-10-29 14:30',
    createdAt: '2024-10-29 12:00'
  },
  {
    id: 2,
    title: 'Weekly Community Highlights and Updates',
    aiProvider: 'Claude AI',
    status: 'Scheduled',
    scheduledFor: '2024-10-31 09:00',
    createdAt: '2024-10-29 15:30'
  },
  {
    id: 3,
    title: 'New Feature Tutorial: Community Groups',
    aiProvider: 'Gemini',
    status: 'Draft',
    scheduledFor: null,
    createdAt: '2024-10-29 16:45'
  },
  {
    id: 4,
    title: 'How to Optimize Your Community Engagement',
    aiProvider: 'OpenAI GPT-4',
    status: 'Failed',
    scheduledFor: '2024-10-30 11:00',
    createdAt: '2024-10-29 10:15'
  }
])

// Filters
const filters = ref({
  status: '',
  aiProvider: ''
})

// Modal state
const showCreateModal = ref(false)
const newPost = ref({
  aiProvider: '',
  topic: '',
  scheduledFor: ''
})

// Options
const aiProviderOptions = [
  { label: 'OpenAI GPT-4', value: 'OpenAI GPT-4' },
  { label: 'Claude AI', value: 'Claude AI' },
  { label: 'Gemini', value: 'Gemini' },
  { label: 'Perplexity AI', value: 'Perplexity AI' }
]

const statusOptions = [
  { label: 'Published', value: 'Published' },
  { label: 'Scheduled', value: 'Scheduled' },
  { label: 'Draft', value: 'Draft' },
  { label: 'Failed', value: 'Failed' }
]

// Table columns
const columns = [
  {
    accessorKey: 'title',
    header: 'Title',
    cell: ({ value }) => h('div', { class: 'font-medium' }, value)
  },
  {
    accessorKey: 'aiProvider',
    header: 'AI Provider',
    cell: ({ value }) => h('div', { class: 'flex items-center space-x-2' }, [
      h(Icon, { name: 'brain', class: 'h-4 w-4' }),
      h('span', value)
    ])
  },
  {
    accessorKey: 'status',
    header: 'Status',
    cell: ({ value }) => {
      const statusClasses = {
        'Published': 'bg-green-100 text-green-800',
        'Scheduled': 'bg-yellow-100 text-yellow-800',
        'Draft': 'bg-gray-100 text-gray-800',
        'Failed': 'bg-red-100 text-red-800'
      }
      return h('span', {
        class: `px-2 py-1 text-xs font-medium rounded-full ${statusClasses[value] || 'bg-gray-100 text-gray-800'}`
      }, value)
    }
  },
  {
    accessorKey: 'scheduledFor',
    header: 'Scheduled For',
    cell: ({ value }) => h('div', value || 'Not scheduled')
  },
  {
    id: 'actions',
    header: 'Actions',
    cell: ({ row }) => {
      const dropdownItems = [
        {
          label: 'Edit',
          icon: 'edit',
          action: () => editPost(row.id)
        },
        {
          label: 'Duplicate',
          icon: 'copy',
          action: () => duplicatePost(row.id)
        },
        { type: 'separator' },
        {
          label: 'Delete',
          icon: 'trash',
          action: () => deletePost(row.id)
        }
      ]

      return h(Dropdown, {
        items: dropdownItems,
        onSelect: (item) => console.log('Selected:', item)
      }, {
        trigger: () => h(Button, { variant: 'ghost', size: 'sm' }, [
          h(Icon, { name: 'moreHorizontal', class: 'h-4 w-4' })
        ])
      })
    }
  }
]

// Computed
const filteredPosts = computed(() => {
  return posts.value.filter(post => {
    if (filters.value.status && post.status !== filters.value.status) {
      return false
    }
    if (filters.value.aiProvider && post.aiProvider !== filters.value.aiProvider) {
      return false
    }
    return true
  })
})

// Methods
const handleCreatePost = () => {
  console.log('Creating post:', newPost.value)
  // Add the new post logic here
  showCreateModal.value = false
  
  // Reset form
  newPost.value = {
    aiProvider: '',
    topic: '',
    scheduledFor: ''
  }
}

const clearFilters = () => {
  filters.value = {
    status: '',
    aiProvider: ''
  }
}

const editPost = (id) => {
  console.log('Edit post:', id)
}

const duplicatePost = (id) => {
  console.log('Duplicate post:', id)
}

const deletePost = (id) => {
  console.log('Delete post:', id)
}
</script>