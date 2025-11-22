<template>
  <div>
    <div class="space-y-6">
      <!-- Header Section -->
      <div class="flex items-center justify-between border-b border-border pb-4">
        <div>
          <h1 class="text-2xl font-bold text-foreground">AI Agents</h1>
          <p class="text-muted-foreground">Manage your AI assistants</p>
        </div>
        <Button @click="openCreateAgentModal" class="flex items-center space-x-2">
          <Icon name="plus" class="h-4 w-4" />
          <span>Create Agent</span>
        </Button>
      </div>

      <!-- Agent List Section -->
      <div v-if="agents.length > 0 || isLoading">
        <!-- Stats Row -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4 mb-6">
          <Card v-for="stat in agentStats" :key="stat.title" class="p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-primary/10 rounded-md flex items-center justify-center">
                  <Icon :name="stat.icon" class="h-4 w-4 text-primary" />
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-muted-foreground">{{ stat.title }}</p>
                <p class="text-2xl font-semibold text-foreground">{{ stat.value }}</p>
              </div>
            </div>
          </Card>
        </div>

        <!-- Agents Table -->
        <Card>
          <CardHeader>
            <CardTitle>Your AI Agents</CardTitle>
            <CardDescription>Manage and configure your AI assistants</CardDescription>
          </CardHeader>
          <CardContent>
            <DataTable 
              :data="agents" 
              :columns="agentColumns"
              search-placeholder="Search agents..."
              :manual-pagination="true"
              :total="totalAgents"
              :page-index="page - 1"
              :page-size="perPage"
              :loading="isLoading"
              @page-change="handlePageChange"
            />
          </CardContent>
        </Card>
      </div>

      <!-- Empty State -->
      <div v-else class="flex flex-col items-center justify-center py-12">
        <Card class="w-full max-w-md p-8 text-center">
          <div class="space-y-4">
            <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto">
              <Icon name="bot" class="h-8 w-8 text-primary" />
            </div>
            <div class="space-y-2">
              <h3 class="text-lg font-semibold text-foreground">No agents yet</h3>
              <p class="text-muted-foreground">Create your first AI agent to get started</p>
            </div>
            <Button @click="openCreateAgentModal" class="w-full">
              <Icon name="plus" class="mr-2 h-4 w-4" />
              Create Your First Agent
            </Button>
          </div>
        </Card>
      </div>
    </div>

    <!-- Create Agent Modal -->
    <Dialog v-model:open="showCreateModal">
      <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Create New Agent</DialogTitle>
          <DialogDescription>
            Set up your AI assistant with custom instructions and capabilities
          </DialogDescription>
        </DialogHeader>

        <form @submit.prevent="createAgent" class="space-y-6">
          <!-- Basic Information Section -->
          <div class="space-y-4">
            <h3 class="text-sm font-semibold text-foreground border-b pb-2">Basic Information</h3>
            
            <!-- Agent Name -->
            <div class="space-y-2">
              <Label for="agentName" class="text-sm font-medium">
                Agent Name <span class="text-red-500">*</span>
              </Label>
              <Input
                id="agentName"
                v-model="formData.name"
                placeholder="e.g., Customer Support Bot"
                maxlength="50"
                required
                :class="{ 'border-red-500': errors.name }"
              />
              <div class="flex justify-between text-xs text-muted-foreground">
                <span v-if="errors.name" class="text-red-500">{{ errors.name }}</span>
                <span class="ml-auto">{{ formData.name.length }}/50</span>
              </div>
            </div>

            <!-- Description
            <div class="space-y-2">
              <Label for="agentDescription" class="text-sm font-medium">
                Description
              </Label>
              <Textarea
                id="agentDescription"
                v-model="formData.description"
                placeholder="What does this agent do?"
                rows="3"
                maxlength="200"
                class="resize-none"
              />
              <div class="flex justify-end text-xs text-muted-foreground">
                <span>{{ formData.description.length }}/200</span>
              </div>
            </div> -->
          </div>

          <!-- Agent Configuration Section -->
          <div class="space-y-4">
            <h3 class="text-sm font-semibold text-foreground border-b pb-2">Agent Configuration</h3>
            
            <!-- Agent Type -->
            <div class="space-y-2">
              <Label for="agentType" class="text-sm font-medium">
                Agent Type <span class="text-red-500">*</span>
              </Label>
              <Select v-model="formData.type" required>
                <SelectTrigger :class="{ 'border-red-500': errors.type }">
                  <SelectValue placeholder="Select agent type" />
                </SelectTrigger>
                <SelectContent>
                  <SelectGroup>
                      <SelectLabel>Agent Type</SelectLabel>
                      <SelectItem 
                          v-for="(typeInfo, typeKey) in agentTypes"
                          :key="typeKey"
                          :value="typeKey"
                          :disabled="typeInfo.disabled"
                          >
                          {{ typeInfo.label }} {{ typeInfo.comingSoon ? "(Coming Soon!)" : "" }} {{ typeInfo.pro ? "" : "" }}
                      </SelectItem>
                  </SelectGroup>

                </SelectContent>
              </Select>
              <span v-if="errors.type" class="text-xs text-red-500">{{ errors.type }}</span>
            </div>

            <!-- AI Model -->
            <div class="space-y-2">
              <Label for="aiModel" class="text-sm font-medium">
                AI Model <span class="text-red-500">*</span>
              </Label>
              <Select v-model="formData.model" required>
                <SelectTrigger :class="{ 'border-red-500': errors.model }">
                  <SelectValue placeholder="Select AI model" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="gpt-4">GPT-4</SelectItem>
                  <SelectItem value="gpt-3.5">GPT-3.5</SelectItem>
                  <SelectItem value="claude">Claude</SelectItem>
                  <SelectItem value="custom">Custom</SelectItem>
                </SelectContent>
              </Select>
              <span v-if="errors.model" class="text-xs text-red-500">{{ errors.model }}</span>
            </div>

            <!-- System Prompt -->
            <div class="space-y-2">
              <Label for="systemPrompt" class="text-sm font-medium">
                System Instructions <span class="text-red-500">*</span>
              </Label>
              <Textarea
                id="systemPrompt"
                v-model="formData.systemPrompt"
                placeholder="Define the agent's behavior, personality, and guidelines..."
                rows="5"
                required
                class="resize-none"
                :class="{ 'border-red-500': errors.systemPrompt }"
              />
              <span v-if="errors.systemPrompt" class="text-xs text-red-500">{{ errors.systemPrompt }}</span>
            </div>
          </div>

          <!-- Capabilities Section -->
          <div class="space-y-4">
            <h3 class="text-sm font-semibold text-foreground border-b pb-2">Capabilities</h3>
            
            <!-- Web Search Toggle -->
            <div class="flex items-center justify-between">
              <div class="space-y-1">
                <Label class="text-sm font-medium">Enable Web Search</Label>
                <p class="text-xs text-muted-foreground">Allow agent to search the web for current information</p>
              </div>
              <Switch v-model="formData.webSearch" />
            </div>

            <!-- File Processing Toggle -->
            <div class="flex items-center justify-between">
              <div class="space-y-1">
                <Label class="text-sm font-medium">Enable File Processing</Label>
                <p class="text-xs text-muted-foreground">Allow agent to read and analyze uploaded files</p>
              </div>
              <Switch v-model="formData.fileProcessing" />
            </div>
          </div>

          <!-- Agent Account Creation Section -->
          <div class="space-y-4">
            <h3 class="text-sm font-semibold text-foreground border-b pb-2">Agent Account</h3>

            <div class="flex items-center justify-between">
              <div class="space-y-1">
                <Label class="text-sm font-medium">Create User for Agent</Label>
                <p class="text-xs text-muted-foreground">If enabled, a new wordpress user will be created and all content generated by this agent will be linked to that user.</p>
              </div>
              <Switch v-model="formData.createUser" />
            </div>

            <Transition name="slide-fade">
              <div v-if="formData.createUser" :key="'user-fields'" class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                <div class="space-y-2">
                  <Label for="agentUsername" class="text-sm font-medium">Username <span class="text-red-500">*</span></Label>
                  <Input id="agentUsername" v-model="formData.user.username" placeholder="agent-bot" :class="{ 'border-red-500': errors.user.username }" maxlength="30" />
                  <span v-if="errors.user.username" class="text-xs text-red-500">{{ errors.user.username }}</span>
                </div>

                <div class="space-y-2">
                  <Label for="agentEmail" class="text-sm font-medium">Email <span class="text-red-500">*</span></Label>
                  <Input id="agentEmail" v-model="formData.user.email" placeholder="bot@example.com" type="email" :class="{ 'border-red-500': errors.user.email }" />
                  <span v-if="errors.user.email" class="text-xs text-red-500">{{ errors.user.email }}</span>
                </div>
              </div>
            </Transition>
          </div>

          <!-- Limitation Notice -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
            <div class="flex items-start space-x-2">
              <Icon name="info" class="h-4 w-4 text-blue-600 mt-0.5 flex-shrink-0" />
              <p class="text-xs text-blue-800">
                Currently limited to 1 agent. Multiple agents coming soon!
              </p>
            </div>
          </div>
        </form>

        <DialogFooter class="flex gap-2 pt-4 border-t">
          <Button 
            type="button"
            variant="outline"
            @click="closeCreateModal"
          >
            Cancel
          </Button>
          <Button 
            type="button"
            @click="createAgent"
            :disabled="!isFormValid"
          >
            <Icon name="plus" class="mr-2 h-4 w-4" />
            Create Agent
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- View Agent Modal -->
    <Dialog v-model:open="showViewModal">
      <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
              <DialogTitle v-if="selectedAgent">{{ selectedAgent.name }}</DialogTitle>
              <p v-if="selectedAgent" class="text-sm text-muted-foreground mt-1">
                {{ selectedAgent.description }}
              </p>
            </div>
            <Badge v-if="selectedAgent" :variant="selectedAgent.status === 'Active' ? 'default' : 'secondary'">
              {{ selectedAgent.status }}
            </Badge>
          </div>
        </DialogHeader>
        
        <div v-if="selectedAgent" class="space-y-6">
          <!-- Agent Configuration -->
          <div class="space-y-4">
            <h3 class="font-semibold text-sm border-b pb-2">Configuration</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <p class="text-xs text-muted-foreground">Agent Type</p>
                <p class="font-medium">{{ getAgentTypeLabel(selectedAgent.type).label }}</p>
              </div>
              <div>
                <p class="text-xs text-muted-foreground">AI Model</p>
                <p class="font-medium flex items-center space-x-2">
                  <Icon :name="getAiIcon(selectedAgent.model)" class="h-4 w-4" />
                  <span>{{ selectedAgent.model.toUpperCase() }}</span>
                </p>
              </div>
              <div>
                <p class="text-xs text-muted-foreground">Created</p>
                <p class="font-medium">{{ selectedAgent.createdAt }}</p>
              </div>
              <div>
                <p class="text-xs text-muted-foreground">Last Modified</p>
                <p class="font-medium">{{ selectedAgent.lastModified }}</p>
              </div>
            </div>
          </div>

          <!-- System Instructions -->
          <div class="space-y-2">
            <h3 class="font-semibold text-sm">System Instructions</h3>
            <div class="bg-muted/50 rounded-lg p-4 text-sm text-foreground">
              {{ selectedAgent.systemPrompt }}
            </div>
          </div>

          <!-- Capabilities -->
          <div class="space-y-2">
            <h3 class="font-semibold text-sm">Capabilities</h3>
            <div class="flex flex-wrap gap-2">
              <Badge variant="outline" class="flex items-center space-x-1">
                <Icon name="search" class="h-3 w-3" />
                <span>Web Search: {{ selectedAgent.webSearch ? 'Enabled' : 'Disabled' }}</span>
              </Badge>
              <Badge variant="outline" class="flex items-center space-x-1">
                <Icon name="file" class="h-3 w-3" />
                <span>File Processing: {{ selectedAgent.fileProcessing ? 'Enabled' : 'Disabled' }}</span>
              </Badge>
            </div>
          </div>
        </div>

        <DialogFooter class="flex gap-2 pt-4 border-t">
          <Button 
            variant="outline"
            @click="closeViewModal"
          >
            Close
          </Button>
          <Button 
            variant="outline"
            @click="editAgent(selectedAgent.id)"
          >
            <Icon name="edit" class="mr-2 h-4 w-4" />
            Edit
          </Button>
          <Button 
            variant="destructive"
            @click="deleteModalAgent"
          >
            <Icon name="trash" class="mr-2 h-4 w-4" />
            Delete
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, h, onMounted } from 'vue'
import { 
  Card, 
  CardHeader, 
  CardTitle, 
  CardDescription, 
  CardContent, 
  Button, 
  Badge, 
  Icon,
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
  Input,
  Label,
  Textarea,
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem,
  SelectGroup,
  SelectLabel,
  Switch,
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui'
import DataTable from '@/components/DataTable.vue'
import { getAiIcon } from '@/lib/utils'
import { api } from '@/lib/api'


// Agent stats data
const agentStats = ref([
  { title: 'Total Agents', value: '0', icon: 'bot' },
  { title: 'Active Agents', value: '0', icon: 'activity' },
  { title: 'Total Interactions', value: '0', icon: 'messageSquare' },
  { title: 'Success Rate', value: '0%', icon: 'checkCircle' }
])

// Agents data
const agents = ref([])
const isLoading = ref(false)
const isSaving = ref(false)
const page = ref(1)
const perPage = ref(5)
const totalAgents = ref(0)


// Modal states
const showCreateModal = ref(false)
const showViewModal = ref(false)
const selectedAgent = ref(null)
const editingId = ref(null)

// Form data for creating agents
const formData = ref({
  name: '',
  description: '',
  type: '',
  model: '',
  systemPrompt: '',
  webSearch: false,
  fileProcessing: false,
  // New: whether to create a user account for this agent
  createUser: false,
  // New: user details used when createUser is true
  user: {
    username: '',
    email: ''
  }
})

// Form errors
const errors = ref({
  name: '',
  type: '',
  model: '',
  systemPrompt: '',
  user: {
    username: '',
    email: ''
  }
})

// Computed property to check if form is valid
const isFormValid = computed(() => {
  // Basic required fields
  let baseValid = formData.value.name.trim() !== '' &&
         formData.value.type !== '' &&
         formData.value.model !== '' &&
         formData.value.systemPrompt.trim() !== ''

  // If creating a user, ensure username and email are present and email looks valid
  if (formData.value.createUser) {
    const u = formData.value.user
    const emailOk = u.email && /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(u.email)
    baseValid = baseValid && u.username && u.username.trim() !== '' && emailOk
  }

  return baseValid
})

// Table columns for agents
const agentColumns = [
  {
    accessorKey: 'name',
    header: 'Agent Details',
    cell: ({ row }) => {
      const agent = row.original
      return h('div', { class: 'space-y-1' }, [
        h('div', { class: 'font-medium text-sm' }, agent.name),
        h('div', { class: 'text-xs text-muted-foreground' }, agent.description),
        h('div', { class: 'flex items-center space-x-2 text-xs text-muted-foreground mt-1' }, [
          h(Icon, { name: getAiIcon(agent.model), class: 'h-3 w-3' }),
          h('span', agent.model.toUpperCase()),
          h('span', '•'),
          h('span', getAgentTypeLabel(agent.type)),
          h('span', '•'),
          h('span', (agent.interactions || 0) + ' interactions')
        ])
      ])
    }
  },
  {
    accessorKey: 'status',
    header: 'Status',
    cell: ({ getValue }) => {
      const value = getValue()
      // Capitalize first letter for display if it's lowercase
      const displayValue = value.charAt(0).toUpperCase() + value.slice(1)
      return h(Badge, {
        variant: value === 'active' ? 'default' : 'secondary'
      }, () => displayValue)
    }
  },
  {
    accessorKey: 'updated_at',
    header: 'Last Modified',
    cell: ({ getValue }) => {
        const date = new Date(getValue())
        return h('div', { class: 'text-sm text-muted-foreground' }, date.toLocaleDateString())
    }
  },
  {
    id: 'actions',
    header: '',
    cell: ({ row }) => {
      return h(DropdownMenu, {}, {
        default: () => [
          h(DropdownMenuTrigger, { asChild: true }, {
            default: () => h(Button, { 
              variant: 'ghost', 
              size: 'sm',
              class: 'h-8 w-8 p-0'
            }, [
              h(Icon, { name: 'moreHorizontal', class: 'h-4 w-4' })
            ])
          }),
          h(DropdownMenuContent, { align: 'end' }, [
            h(DropdownMenuItem, { 
              onClick: () => viewAgent(row.original.id) 
            }, [
              h(Icon, { name: 'eye', class: 'mr-2 h-4 w-4' }),
              'View'
            ]),
            h(DropdownMenuItem, { 
              onClick: () => editAgent(row.original.id) 
            }, [
              h(Icon, { name: 'edit', class: 'mr-2 h-4 w-4' }),
              'Edit'
            ]),
            h(DropdownMenuItem, { 
              onClick: () => toggleAgentStatus(row.original.id) 
            }, [
              h(Icon, { name: row.original.status === 'active' ? 'pause' : 'play', class: 'mr-2 h-4 w-4' }),
              row.original.status === 'active' ? 'Deactivate' : 'Activate'
            ]),
            h(DropdownMenuSeparator),
            h(DropdownMenuItem, { 
              onClick: () => deleteAgent(row.original.id),
              class: 'text-red-600'
            }, [
              h(Icon, { name: 'trash', class: 'mr-2 h-4 w-4' }),
              'Delete'
            ])
          ])
        ]
      })
    }
  }
]



// Methods
const fetchAgents = async () => {
    isLoading.value = true
    try {
        const response = await api.get(`agents?page=${page.value}&per_page=${perPage.value}`)
        if (response.data && response.data.data && response.data.meta) {
             agents.value = response.data.data
             totalAgents.value = response.data.meta.total
        } else {
             agents.value = Array.isArray(response.data) ? response.data : []
             totalAgents.value = agents.value.length
        }
    } catch (error) {
        console.error('Failed to fetch agents:', error)
        // TODO: Show toast notification
    } finally {
        isLoading.value = false
    }
}

const handlePageChange = (newPageIndex) => {
    page.value = newPageIndex + 1
    fetchAgents()
}

const fetchStats = async () => {
    try {
        const response = await api.get('agents/stats')
        const stats = response.data
        agentStats.value = [
            { title: 'Total Agents', value: stats.total_agents.toString(), icon: 'bot' },
            { title: 'Active Agents', value: stats.active_agents.toString(), icon: 'activity' },
            { title: 'Total Interactions', value: stats.total_interactions.toString(), icon: 'messageSquare' },
            { title: 'Avg Interactions', value: stats.avg_interactions.toString(), icon: 'checkCircle' }
        ]
    } catch (error) {
        console.error('Failed to fetch stats:', error)
    }
}

const openCreateAgentModal = () => {
  resetForm()
  editingId.value = null
  showCreateModal.value = true
}

const closeCreateModal = () => {
  showCreateModal.value = false
  resetForm()
  editingId.value = null
}

const closeViewModal = () => {
  showViewModal.value = false
  selectedAgent.value = null
}


const resetForm = () => {
  formData.value = {
    name: '',
    description: '',
    type: '',
    model: '',
    systemPrompt: '',
    webSearch: false,
    fileProcessing: false,
    createUser: false,
    user: {
      username: '',
      email: ''
    }
  }
  errors.value = {
    name: '',
    type: '',
    model: '',
    systemPrompt: '',
    user: {
      username: '',
      email: ''
    }
  }
}

const validateForm = () => {
  errors.value = {
    name: '',
    type: '',
    model: '',
    systemPrompt: '',
    user: {
      username: '',
      email: ''
    }
  }

  if (!formData.value.name.trim()) {
    errors.value.name = 'Agent name is required'
  }

  if (!formData.value.type) {
    errors.value.type = 'Agent type is required'
  }

  if (!formData.value.model) {
    errors.value.model = 'AI model is required'
  }

  if (!formData.value.systemPrompt.trim()) {
    errors.value.systemPrompt = 'System instructions are required'
  }

  // If user creation is requested, validate username and email
  if (formData.value.createUser) {
    if (!formData.value.user.username || !formData.value.user.username.trim()) {
      errors.value.user.username = 'Username is required when creating a user'
    }
    if (!formData.value.user.email || !formData.value.user.email.trim()) {
      errors.value.user.email = 'Email is required when creating a user'
    } else if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(formData.value.user.email)) {
      errors.value.user.email = 'Please enter a valid email address'
    }
  }

  // flatten nested error objects (like errors.user) and ensure all are empty strings
  const flattenErrors = (obj) => Object.values(obj).flatMap(v => (v && typeof v === 'object') ? Object.values(v) : [v])
  return flattenErrors(errors.value).every(error => error === '')
}

const createAgent = async () => {
  if (!validateForm()) {
    return
  }

  isSaving.value = true

  // Map frontend camelCase to backend snake_case
  const payload = {
    name: formData.value.name,
    description: formData.value.description,
    type: formData.value.type,
    model: formData.value.model,
    system_prompt: formData.value.systemPrompt,
    web_search: formData.value.webSearch ? 1 : 0,
    file_processing: formData.value.fileProcessing ? 1 : 0,
    create_user: formData.value.createUser ? 1 : 0,
    username: formData.value.createUser ? formData.value.user.username : '',
    user_email: formData.value.createUser ? formData.value.user.email : ''
  }

  try {
    if (editingId.value) {
        // Update existing agent
        await api.put(`agents/${editingId.value}`, payload)
    } else {
        // Create new agent
        await api.post('agents', payload)
    }
    
    await fetchAgents()
    await fetchStats()
    closeCreateModal()
  } catch (error) {
    console.error('Failed to save agent:', error)
    // TODO: Show error toast
    // If error is about name already exists, set error
    if (error.message.includes('name already exists')) {
        errors.value.name = 'Agent with this name already exists'
    }
  } finally {
    isSaving.value = false
  }
}



const viewAgent = (id) => {
  const agent = agents.value.find(a => a.id === id)
  if (agent) {
    // Map backend snake_case to frontend camelCase for display if needed
    // But the template uses properties directly. 
    // We need to make sure the template uses the correct property names.
    // The backend returns snake_case for database fields.
    // Let's normalize it for the view modal.
    selectedAgent.value = {
        ...agent,
        systemPrompt: agent.system_prompt,
        webSearch: agent.web_search,
        fileProcessing: agent.file_processing,
        createdAt: new Date(agent.created_at).toLocaleDateString(),
        lastModified: new Date(agent.updated_at).toLocaleDateString()
    }
    showViewModal.value = true
  }
}

const editAgent = (id) => {
  const agent = agents.value.find(a => a.id === id)
  if (agent) {
    editingId.value = id
    // Pre-populate form with agent data for editing
    formData.value = {
      name: agent.name,
      description: agent.description || '',
      type: agent.type,
      model: agent.model,
      systemPrompt: agent.system_prompt,
      webSearch: !!agent.web_search,
      fileProcessing: !!agent.file_processing,
      createUser: !!agent.create_user,
      user: {
        username: agent.username || '',
        email: agent.user_email || ''
      }
    }
    showCreateModal.value = true
  }
}

const toggleAgentStatus = async (id) => {
  try {
    await api.post(`agents/${id}/toggle-status`)
    await fetchAgents()
    await fetchStats()
  } catch (error) {
    console.error('Failed to toggle status:', error)
  }
}

const deleteAgent = async (id) => {
  if (!confirm('Are you sure you want to delete this agent?')) return
  
  try {
    await api.delete(`agents/${id}`)
    await fetchAgents()
    await fetchStats()
  } catch (error) {
    console.error('Failed to delete agent:', error)
  }
}

const deleteModalAgent = async () => {
  if (selectedAgent.value) {
    if (!confirm('Are you sure you want to delete this agent?')) return
    
    try {
        await api.delete(`agents/${selectedAgent.value.id}`)
        await fetchAgents()
        await fetchStats()
        closeViewModal()
    } catch (error) {
        console.error('Failed to delete agent:', error)
    }
  }
}


// Agent type configuration
const agentTypes = {
    'content-writer': { label: 'Content Writer', disabled: false, pro: false, comingSoon: false },
    'customer-support': { label: 'Customer Support', disabled: true, pro: false, comingSoon: true },
    'product-expert': { label: 'Product Expert', disabled: true, pro: false, comingSoon: true },
    'news-summarizer': { label: 'News Summarizer', disabled: true, pro: false, comingSoon: true },
    'data-analyzer': { label: 'Data Analyzer', disabled: true, pro: false, comingSoon: true },
    'general-assistant': { label: 'General Assistant', disabled: true, pro: false, comingSoon: true }
}

// Helper functions for agent types
const getAgentTypeInfo = (type) => {
    return agentTypes[type] || { label: type, disabled: false, pro: false, comingSoon: false }
}

const getAgentTypeLabel = (type) => {
    return type
}

const isAgentTypeAvailable = (type) => {
    const info = getAgentTypeInfo(type)
    return !info.disabled && !info.pro
}

const getAvailableAgentTypes = () => {
    return Object.entries(agentTypes).filter(([key, info]) => !info.disabled)
}

// Initial fetch
onMounted(() => {
    fetchAgents()
    fetchStats()
})
</script>
