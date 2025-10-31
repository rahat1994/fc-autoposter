<template>
    <!-- Quick Actions -->
    <div class="space-y-6">
        <Card>
            <CardHeader>
            <CardTitle>Quick Actions</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
            <Button class="w-full justify-start" @click="openGenerateModal">
                <Icon name="plus" class="mr-2 h-4 w-4" />
                Generate New Post
            </Button>
            <Button variant="outline" class="w-full justify-start" @click="viewSchedule">
                <Icon name="repeat" class="mr-2 h-4 w-4" />
                Posting Agents
            </Button>
            <Button variant="outline" class="w-full justify-start" @click="viewSchedule">
                <Icon name="MessageCircleMore" class="mr-2 h-4 w-4" />
                Commenting Agents
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
    </div>
</template>

<script setup>
import { ref, h } from 'vue'
import {   
    Card, 
    CardHeader, 
    CardTitle, 
    CardDescription, 
    CardContent,
    Button,  
    Icon
} from '../ui';
import { getAiIcon } from '@/lib/utils'

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

const closeGenerateModal = () => {
  showGenerateModal.value = false
  newPostPrompt.value = ''
  newPostDate.value = ''
  newPostTime.value = ''
}

</script>