<template>
  <div class="space-y-6">
    <div class="border-b border-border pb-4">
      <h1 class="text-2xl font-bold text-foreground">Settings</h1>
      <p class="text-muted-foreground">Configure your automation settings</p>
    </div>

    <Card class="p-6">
      <div class="space-y-6">
        <div>
          <h3 class="text-lg font-medium">AI Provider Settings</h3>
          <p class="text-sm text-muted-foreground">Configure API keys for different AI providers.</p>
        </div>

        <TabsRoot class="flex flex-col w-full" defaultValue="openrouter">
          <TabsList class="relative shrink-0 flex border-b border-border mb-4" aria-label="AI Providers">
            <TabsIndicator class="absolute px-8 left-0 h-[2px] bottom-0 w-[--radix-tabs-indicator-size] translate-x-[--radix-tabs-indicator-position] rounded-full transition-[width,transform] duration-300">
              <div class="bg-primary w-full h-full" />
            </TabsIndicator>
            <TabsTrigger
              class="px-5 h-[45px] flex items-center justify-center text-[15px] leading-none text-muted-foreground select-none hover:text-foreground data-[state=active]:text-foreground data-[state=active]:font-medium outline-none cursor-default border-b-2 border-transparent data-[state=active]:border-primary transition-colors"
              value="openrouter"
            >
              OpenRouter
            </TabsTrigger>
            <TabsTrigger
              class="px-5 h-[45px] flex items-center justify-center text-[15px] leading-none text-muted-foreground select-none hover:text-foreground data-[state=active]:text-foreground data-[state=active]:font-medium outline-none cursor-pointer border-b-2 border-transparent data-[state=active]:border-primary transition-colors"
              value="openai"
            >
              OpenAI
            </TabsTrigger>
            <TabsTrigger
              class="px-5 h-[45px] flex items-center justify-center text-[15px] leading-none text-muted-foreground select-none hover:text-foreground data-[state=active]:text-foreground data-[state=active]:font-medium outline-none cursor-pointer border-b-2 border-transparent data-[state=active]:border-primary transition-colors"
              value="google"
            >
              Google
            </TabsTrigger>
          </TabsList>

          <!-- OpenRouter Content -->
          <TabsContent class="grow outline-none" value="openrouter">
            <div class="space-y-4">
              <div class="bg-muted/50 p-4 rounded-lg space-y-2">
                <h4 class="font-medium text-sm">About OpenRouter</h4>
                <p class="text-sm text-muted-foreground">
                  OpenRouter is a unified interface for LLMs. It allows you to access various models like GPT-4, Claude 3, and Llama 3 through a single API.
                </p>
                <a 
                  href="https://openrouter.ai/keys" 
                  target="_blank" 
                  class="text-sm text-primary hover:underline inline-flex items-center"
                >
                  Get your API Key <Icon name="external-link" class="ml-1 h-3 w-3" />
                </a>
              </div>

              <div class="space-y-2">
                <Label for="openrouter_api_key">OpenRouter API Key</Label>
                <Input 
                  id="openrouter_api_key" 
                  v-model="settings.openrouter_api_key" 
                  type="password" 
                  placeholder="sk-or-..." 
                />
                <p class="text-xs text-muted-foreground">
                  Enter your OpenRouter API key to enable access to multiple models.
                </p>
              </div>
            </div>
          </TabsContent>

          <!-- OpenAI Content -->
          <TabsContent class="grow outline-none" value="openai">
            <div class="space-y-4">
              <div class="bg-muted/50 p-4 rounded-lg space-y-2">
                <h4 class="font-medium text-sm">About OpenAI</h4>
                <p class="text-sm text-muted-foreground">
                  OpenAI provides industry-leading models like GPT-4 and GPT-3.5 Turbo.
                </p>
                <a 
                  href="https://platform.openai.com/api-keys" 
                  target="_blank" 
                  class="text-sm text-primary hover:underline inline-flex items-center"
                >
                  Get your API Key <Icon name="external-link" class="ml-1 h-3 w-3" />
                </a>
              </div>

              <div class="space-y-2">
                <Label for="openai_api_key">OpenAI API Key</Label>
                <Input 
                  id="openai_api_key" 
                  v-model="settings.openai_api_key" 
                  type="password" 
                  placeholder="sk-..." 
                />
                <p class="text-xs text-muted-foreground">
                  Enter your OpenAI API key to use GPT models directly.
                </p>
              </div>
            </div>
          </TabsContent>

          <!-- Google Content -->
          <TabsContent class="grow outline-none" value="google">
            <div class="space-y-4">
              <div class="bg-muted/50 p-4 rounded-lg space-y-2">
                <h4 class="font-medium text-sm">About Google Gemini</h4>
                <p class="text-sm text-muted-foreground">
                  Google's Gemini models offer multimodal capabilities and strong performance.
                </p>
                <a 
                  href="https://aistudio.google.com/app/apikey" 
                  target="_blank" 
                  class="text-sm text-primary hover:underline inline-flex items-center"
                >
                  Get your API Key <Icon name="external-link" class="ml-1 h-3 w-3" />
                </a>
              </div>

              <div class="space-y-2">
                <Label for="google_api_key">Google Gemini API Key</Label>
                <Input 
                  id="google_api_key" 
                  v-model="settings.google_api_key" 
                  type="password" 
                  placeholder="AIza..." 
                />
                <p class="text-xs text-muted-foreground">
                  Enter your Google AI Studio API key to use Gemini models.
                </p>
              </div>
            </div>
          </TabsContent>
        </TabsRoot>

        <div class="flex justify-end pt-4 border-t border-border mt-6">
          <Button @click="saveSettings" :disabled="isSaving">
            <Icon v-if="isSaving" name="loader" class="mr-2 h-4 w-4 animate-spin" />
            <Icon v-else name="save" class="mr-2 h-4 w-4" />
            {{ isSaving ? 'Saving...' : 'Save Settings' }}
          </Button>
        </div>
      </div>
    </Card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Card, Button, Input, Label, Icon } from '@/components/ui'
import { api } from '@/lib/api'
import { TabsContent, TabsIndicator, TabsList, TabsRoot, TabsTrigger } from 'radix-vue'

const settings = ref({
  openrouter_api_key: '',
  openai_api_key: '',
  google_api_key: ''
})

const isLoading = ref(false)
const isSaving = ref(false)

const fetchSettings = async () => {
  isLoading.value = true
  try {
    const response = await api.get('settings')
    if (response.data) {
      settings.value = {
        openrouter_api_key: response.data.openrouter_api_key || '',
        openai_api_key: response.data.openai_api_key || '',
        google_api_key: response.data.google_api_key || ''
      }
    }
  } catch (error) {
    console.error('Failed to fetch settings:', error)
    // TODO: Show error toast
  } finally {
    isLoading.value = false
  }
}

const saveSettings = async () => {
  isSaving.value = true
  try {
    await api.post('settings', settings.value)
    // TODO: Show success toast
    await fetchSettings() // Refresh settings to get masked values
  } catch (error) {
    console.error('Failed to save settings:', error)
    // TODO: Show error toast
  } finally {
    isSaving.value = false
  }
}

onMounted(() => {
  fetchSettings()
})
</script>
