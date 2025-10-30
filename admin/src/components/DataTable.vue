<template>
  <div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
      <h3 class="text-lg font-medium text-gray-900">{{ title }}</h3>
      <button v-if="showCreateButton" @click="$emit('create')" 
              class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md text-sm font-medium transition-colors duration-200">
        Create New
      </button>
    </div>
    <div class="p-6">
      <div v-if="items.length === 0" class="text-center py-8">
        <div class="text-gray-400 text-4xl mb-4">{{ emptyIcon }}</div>
        <p class="text-gray-500 text-lg">{{ emptyMessage }}</p>
        <p class="text-gray-400 text-sm mt-2">{{ emptySubMessage }}</p>
      </div>
      <div v-else class="space-y-4">
        <div v-for="item in items" :key="item.id" 
             class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50 transition-colors duration-200">
          <div class="flex items-center space-x-4">
            <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center">
              <span class="text-gray-600">{{ getItemIcon(item) }}</span>
            </div>
            <div>
              <p class="font-medium text-gray-900">{{ item.title || item.name }}</p>
              <p class="text-sm text-gray-500">{{ getItemSubtext(item) }}</p>
            </div>
          </div>
          <div class="flex items-center space-x-2">
            <span v-if="item.status" :class="getStatusClass(item.status)" 
                  class="px-2 py-1 text-xs font-medium rounded-full">
              {{ item.status }}
            </span>
            <button @click="$emit('item-action', item)" 
                    class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
              ⋯
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  title: {
    type: String,
    required: true
  },
  items: {
    type: Array,
    default: () => []
  },
  showCreateButton: {
    type: Boolean,
    default: false
  },
  emptyIcon: {
    type: String,
    default: '📝'
  },
  emptyMessage: {
    type: String,
    default: 'No items yet'
  },
  emptySubMessage: {
    type: String,
    default: 'Create your first item to get started'
  }
})

defineEmits(['create', 'item-action'])

const getItemIcon = (item) => {
  if (item.platform) {
    return item.platform.charAt(0).toUpperCase()
  }
  if (item.type) {
    return item.type.charAt(0).toUpperCase()
  }
  return '📄'
}

const getItemSubtext = (item) => {
  const parts = []
  if (item.platform) parts.push(item.platform)
  if (item.scheduledFor) parts.push(item.scheduledFor)
  if (item.createdAt) parts.push(item.createdAt)
  return parts.join(' • ')
}

const getStatusClass = (status) => {
  const classes = {
    'Scheduled': 'bg-yellow-100 text-yellow-800',
    'Published': 'bg-green-100 text-green-800',
    'Failed': 'bg-red-100 text-red-800',
    'Draft': 'bg-gray-100 text-gray-800',
    'Active': 'bg-green-100 text-green-800',
    'Inactive': 'bg-gray-100 text-gray-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}
</script>