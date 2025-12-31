<template>
    <!-- Quick Actions -->
    <div class="space-y-6">
        <Card>
            <CardHeader>
                <CardTitle>Quick Actions</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
            <Button  class="w-full justify-start" @click="viewSchedule">
                <Icon name="rss" class="mr-2 h-4 w-4" />
                Posting Agents
            </Button>
            <Button variant="outline" class="w-full justify-start" @click="openGenerateModal">
                <Icon name="plus" class="mr-2 h-4 w-4" />
                Generate New Post
            </Button>
            <Button variant="outline" disabled="true" class="w-full justify-start" @click="viewSchedule">
                <Icon name="headset" class="mr-2 h-4 w-4" />
                Support Agents
            </Button>
            <Button variant="outline" disabled="true" class="w-full justify-start" @click="viewSchedule">
                <Icon name="newspaper" class="mr-2 h-4 w-4" />
                Market Update Agents
            </Button>
            <Button variant="outline" disabled="true" class="w-full justify-start" @click="viewSchedule">
                <Icon name="chartPie" class="mr-2 h-4 w-4" />
                Market Analyzer Agents
            </Button>
            <Button variant="outline" disabled="true" class="w-full justify-start" @click="viewSchedule">
                <Icon name="heartPlus" class="mr-2 h-4 w-4" />
                Onboarding agents
            </Button>
            <Button variant="outline" disabled="true" class="w-full justify-start" @click="viewSchedule">
                <Icon name="MessageCircleMore" class="mr-2 h-4 w-4" />
                Commenting Agents
            </Button>
            <Button variant="outline" disabled="true" class="w-full justify-start" @click="viewSchedule">
                <Icon name="atSign" class="mr-2 h-4 w-4" />
                Replying Agents
            </Button>
            <Button variant="outline" disabled="true" class="w-full justify-start" @click="viewSchedule">
                <Icon name="siren" class="mr-2 h-4 w-4" />
                Moderator Agents
            </Button>
            <Button variant="outline" class="w-full justify-start" @click="openGenerateModal">
                <Icon name="clock" class="mr-2 h-4 w-4" />
                Posting Frequencies
            </Button>

            <!-- <Button variant="outline" class="w-full justify-start" @click="viewSchedule">
                <Icon name="calendar" class="mr-2 h-4 w-4" />
                View Schedule
            </Button> -->
            <!-- <Button variant="outline" class="w-full justify-start" @click="manageAiIntegrations">
                <Icon name="brain" class="mr-2 h-4 w-4" />
                AI Integrations
            </Button> -->
            </CardContent>
        </Card>

        <!-- AI Integrations Status -->
        <Card>
            <CardHeader>
            <CardTitle>AI Integrations Status</CardTitle>
            </CardHeader>
            <CardContent>
            <div class="space-y-3">
                <div v-for="integration in aiIntegrations" :key="integration.name"
                    class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <Icon :name="getAiIcon(integration.name)" class="h-4 w-4" />
                    <span class="text-sm">{{ integration.name }}</span>
                </div>
                <Badge :variant="integration.isActive ? 'default' : 'secondary'">
                    {{ integration.isActive ? 'Active' : 'Inactive' }}
                </Badge>
                </div>
            </div>
            </CardContent>
        </Card>

        <!-- Generate New Post Modal -->
        <Dialog v-model:open="showGenerateModal">
            <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>Generate New Post</DialogTitle>
                <DialogDescription>
                Enter a prompt to generate a new AI-powered post for your community.
                </DialogDescription>
            </DialogHeader>
            
            <div class="space-y-4 py-4">
                <div class="space-y-2">
                <Label for="post-prompt">Post Prompt</Label>
                <Textarea
                    id="post-prompt"
                    v-model="newPostPrompt"
                    placeholder="Describe the topic, style, and any specific requirements for your post..."
                    class="min-h-[120px] resize-none"
                />
                </div>

                <div class="space-y-2">
                <Label>Publish Date</Label>
                <Popover>
                    <PopoverTrigger as-child>
                    <Button
                        variant="outline"
                        class="w-full justify-start text-left font-normal"
                    >
                        <Icon name="calendar" class="mr-2 h-4 w-4" />
                        {{ newPostDate ? new Date(newPostDate).toLocaleDateString() : 'Pick a date' }}
                    </Button>
                    </PopoverTrigger>
                    <PopoverContent class="w-auto p-0" align="start">
                    <Calendar
                        v-model="newPostDate"
                    />
                    </PopoverContent>
                </Popover>
                </div>

                <div class="space-y-2">
                <Label for="post-time">Publish Time</Label>
                <input
                    id="post-time"
                    v-model="newPostTime"
                    type="time"
                    class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                />
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="closeGenerateModal">
                Cancel
                </Button>
                <Button @click="generateNewPost" :disabled="!newPostPrompt.trim()">
                <Icon name="sparkles" class="mr-2 h-4 w-4" />
                Generate Post
                </Button>
            </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, h } from 'vue'
import {   
    Card, 
    CardHeader, 
    CardTitle, 
    Badge,
    CardDescription, 
    CardContent,
    Button,  
    Icon,
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogFooter,
    Textarea,
    Label,
    Popover,
    PopoverContent,
    PopoverTrigger,
    Calendar,
} from '../ui';
import { getAiIcon } from '@/lib/utils'

const showGenerateModal = ref(false)
const newPostPrompt = ref('')
const newPostDate = ref('')
const newPostTime = ref('')

const aiIntegrations = ref([
  { name: 'OpenAI GPT-4', isActive: true },
  { name: 'Claude AI', isActive: true },
  { name: 'Gemini', isActive: false },
  { name: 'Perplexity AI', isActive: false },
  { name: 'Mistral AI', isActive: false },
])

// Generate new post methods
const openGenerateModal = () => {
  showGenerateModal.value = true
}

const generateNewPost = () => {
  if (newPostPrompt.value.trim()) {
    // Here you would typically call an API to generate the post
    console.log('Generating new post with prompt:', newPostPrompt.value)
    console.log('Publish date:', newPostDate.value)
    console.log('Publish time:', newPostTime.value)
    // For now, we'll just close the modal and reset the prompt
    showGenerateModal.value = false
    newPostPrompt.value = ''
    newPostDate.value = ''
    newPostTime.value = ''
  }
}

</script>