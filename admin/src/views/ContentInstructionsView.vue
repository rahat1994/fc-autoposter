<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center border-b border-border pb-4">
      <div>
        <h1 class="text-2xl font-bold text-foreground">Content Instructions</h1>
        <p class="text-muted-foreground">Manage content generation instructions for agents</p>
      </div>
      <Button @click="openCreateModal">
        <PlusIcon class="w-4 h-4 mr-2" />
        Create Instruction
      </Button>
    </div>

    <Card class="p-6">
      <div v-if="loading" class="flex justify-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
      </div>

      <div v-else-if="error" class="bg-destructive/10 text-destructive p-4 rounded-md mb-4">
        {{ error }}
      </div>

      <div v-else>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>ID</TableHead>
              <TableHead>Instruction</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Attempts</TableHead>
              <TableHead>Created At</TableHead>
              <TableHead class="text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="instruction in instructions" :key="instruction.id">
              <TableCell>#{{ instruction.id }}</TableCell>
              <TableCell class="max-w-md truncate" :title="instruction.instruction">
                {{ instruction.instruction }}
              </TableCell>
              <TableCell>
                <Badge :variant="getStatusVariant(instruction.status)">
                  {{ instruction.status }}
                </Badge>
              </TableCell>
              <TableCell>{{ instruction.attempts }}</TableCell>
              <TableCell>{{ formatDate(instruction.created_at) }}</TableCell>
              <TableCell class="text-right space-x-2">
                <Button variant="ghost" size="sm" @click="editInstruction(instruction)">
                  <PencilIcon class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="sm" @click="deleteInstruction(instruction.id)" class="text-destructive hover:text-destructive">
                  <TrashIcon class="w-4 h-4" />
                </Button>
                <Button v-if="instruction.status === 'failed'" variant="ghost" size="sm" @click="retryInstruction(instruction.id)" title="Retry">
                  <RefreshCwIcon class="w-4 h-4" />
                </Button>
              </TableCell>
            </TableRow>
            <TableRow v-if="instructions.length === 0">
              <TableCell colspan="6" class="text-center py-8 text-muted-foreground">
                No instructions found. Create one to get started.
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="flex justify-center mt-4 space-x-2">
          <Button 
            variant="outline" 
            size="sm" 
            :disabled="currentPage === 1"
            @click="changePage(currentPage - 1)"
          >
            Previous
          </Button>
          <span class="flex items-center px-2 text-sm text-muted-foreground">
            Page {{ currentPage }} of {{ totalPages }}
          </span>
          <Button 
            variant="outline" 
            size="sm" 
            :disabled="currentPage === totalPages"
            @click="changePage(currentPage + 1)"
          >
            Next
          </Button>
        </div>
      </div>
    </Card>

    <!-- Create/Edit Modal -->
    <Dialog :open="showModal" @update:open="showModal = $event">
      <DialogContent class="sm:max-w-[600px]">
        <DialogHeader>
          <DialogTitle>{{ isEditing ? 'Edit Instruction' : 'Create Instruction' }}</DialogTitle>
          <DialogDescription>
            Define what content the agents should generate.
          </DialogDescription>
        </DialogHeader>
        
        <div class="space-y-4 py-4">
          <div class="space-y-2">
            <Label for="instruction">Instruction</Label>
            <Textarea 
              id="instruction" 
              v-model="formData.instruction" 
              placeholder="e.g., Write a blog post about the benefits of AI in healthcare..." 
              rows="5"
            />
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="tone">Tone (Optional)</Label>
              <Select v-model="formData.metadata.tone">
                <SelectTrigger>
                  <SelectValue placeholder="Select tone" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="professional">Professional</SelectItem>
                  <SelectItem value="casual">Casual</SelectItem>
                  <SelectItem value="humorous">Humorous</SelectItem>
                  <SelectItem value="authoritative">Authoritative</SelectItem>
                </SelectContent>
              </Select>
            </div>
            
            <div class="space-y-2">
              <Label for="length">Length (Optional)</Label>
              <Select v-model="formData.metadata.length">
                <SelectTrigger>
                  <SelectValue placeholder="Select length" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="short">Short (~300 words)</SelectItem>
                  <SelectItem value="medium">Medium (~800 words)</SelectItem>
                  <SelectItem value="long">Long (~1500 words)</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" @click="showModal = false">Cancel</Button>
          <Button @click="saveInstruction" :disabled="saving">
            <span v-if="saving" class="animate-spin mr-2">⌛</span>
            {{ isEditing ? 'Update' : 'Create' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'
import { 
  Card, 
  Button, 
  Table, 
  TableHeader, 
  TableBody, 
  TableRow, 
  TableHead, 
  TableCell,
  Badge,
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
  Label,
  Textarea,
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem
} from '@/components/ui'
import { PlusIcon, PencilIcon, TrashIcon, RefreshCwIcon } from 'lucide-vue-next'
import { api } from '@/lib/api'

// State
const instructions = ref([])
const loading = ref(true)
const error = ref(null)
const saving = ref(false)
const showModal = ref(false)
const isEditing = ref(false)
const currentId = ref(null)
const currentPage = ref(1)
const totalPages = ref(1)

// Form Data
const formData = reactive({
  instruction: '',
  metadata: {
    tone: '',
    length: ''
  }
})

// Methods
const fetchInstructions = async (page = 1) => {
  loading.value = true
  error.value = null
  try {
    const response = await api.contentInstructions.getAll({ page, per_page: 10 })
    if (response.success) {
      instructions.value = response.data.data
      currentPage.value = response.data.meta.current_page
      totalPages.value = response.data.meta.last_page
    } else {
      error.value = response.message || 'Failed to load instructions'
    }
  } catch (err) {
    error.value = err.message || 'An error occurred'
  } finally {
    loading.value = false
  }
}

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    fetchInstructions(page)
  }
}

const openCreateModal = () => {
  isEditing.value = false
  currentId.value = null
  formData.instruction = ''
  formData.metadata = { tone: '', length: '' }
  showModal.value = true
}

const editInstruction = (instruction) => {
  isEditing.value = true
  currentId.value = instruction.id
  formData.instruction = instruction.instruction
  formData.metadata = instruction.metadata || { tone: '', length: '' }
  showModal.value = true
}

const saveInstruction = async () => {
  if (!formData.instruction) {
    alert('Please enter an instruction')
    return
  }

  saving.value = true
  try {
    let response
    if (isEditing.value) {
      response = await api.contentInstructions.update(currentId.value, formData)
    } else {
      response = await api.contentInstructions.create(formData)
    }

    if (response.success) {
      showModal.value = false
      fetchInstructions(currentPage.value)
    } else {
      alert(response.message || 'Operation failed')
    }
  } catch (err) {
    alert(err.message || 'An error occurred')
  } finally {
    saving.value = false
  }
}

const deleteInstruction = async (id) => {
  if (!confirm('Are you sure you want to delete this instruction?')) return

  try {
    const response = await api.contentInstructions.delete(id)
    if (response.success) {
      fetchInstructions(currentPage.value)
    } else {
      alert(response.message || 'Failed to delete')
    }
  } catch (err) {
    alert(err.message || 'An error occurred')
  }
}

const retryInstruction = async (id) => {
  try {
    const response = await api.contentInstructions.retry(id)
    if (response.success) {
      fetchInstructions(currentPage.value)
    } else {
      alert(response.message || 'Failed to retry')
    }
  } catch (err) {
    alert(err.message || 'An error occurred')
  }
}

const getStatusVariant = (status) => {
  switch (status) {
    case 'completed': return 'success'
    case 'processing': return 'warning'
    case 'failed': return 'destructive'
    default: return 'secondary'
  }
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleString()
}

// Lifecycle
onMounted(() => {
  fetchInstructions()
})
</script>
