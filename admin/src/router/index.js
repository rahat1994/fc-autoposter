import { createRouter, createWebHashHistory } from 'vue-router'
import DashboardContent from '../components/DashboardContent.vue'

const routes = [
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: DashboardContent,
    meta: {
      title: 'Dashboard'
    }
  },
  {
    path: '/agents',
    name: 'Agents',
    component: () => import('../views/AgentsView.vue'),
    meta: {
      title: 'Agents'
    }
  },
  {
    path: '/posts',
    name: 'Posts',
    component: () => import('../views/PostsView.vue'),
    meta: {
      title: 'Posts'
    }
  },
  {
    path: '/schedule',
    name: 'Schedule',
    component: () => import('../views/ScheduleView.vue'),
    meta: {
      title: 'Schedule'
    }
  },
  {
    path: '/analytics',
    name: 'Analytics',
    component: () => import('../views/AnalyticsView.vue'),
    meta: {
      title: 'Analytics'
    }
  },
  {
    path: '/settings',
    name: 'Settings',
    component: () => import('../views/SettingsView.vue'),
    meta: {
      title: 'Settings'
    }
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/dashboard'
  }
]

const router = createRouter({
  history: createWebHashHistory(),
  routes
})

// Update page title on route change
router.afterEach((to) => {
  document.title = `${to.meta.title || 'Page'} - FC Autoposter`
})

export default router
