<template>
  <div class="fc-autoposter-container">
    <h1>{{ message }}</h1>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const message = ref('Welcome to FC Autoposter Admin Panel')
</script>

<style scoped>
.fc-autoposter-container {
  padding: 20px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

h1 {
  color: #2c3e50;
  font-size: 24px;
  margin-bottom: 20px;
}
</style>
