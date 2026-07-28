<template>
  <div v-else id="app" class="bg-white dark:bg-notion-dark">
    <loading v-show="!isIframe" ref="loading" />
    <transition name="page" mode="out-in">
      <component :is="layout" v-if="layout" />
    </transition>
    <portal-target name="modals" multiple />
    <notifications />
  </div>
</template>

<script>
import Loading from './Loading.vue'
import Notifications from "./common/Notifications.vue"
import SeoMeta from '../mixins/seo-meta.js'

// Load layout components dynamically.
const requireContext = import.meta.glob('../layouts/**.vue', { eager: true })

const layouts = {}
Object.keys(requireContext)
  .map(file =>
    [file.match(/[^/]*(?=\.[^.]*$)/)[0], requireContext[file]]
  )
  .forEach(([name, component]) => {
    layouts[name] = component.default || component
  })

export default {
  el: '#app',

  components: {
    Notifications,
    Loading
  },

  mixins: [SeoMeta],

  data: () => ({
    metaTitle: 'Hello2Forms',
    metaDescription: 'Create beautiful forms for free. Unlimited fields, unlimited submissions. It\'s free and it takes less than 1 minute to create your first form.',
    layout: null,
    defaultLayout: 'default',
    announcement: false,
    alert: {
      type: null,
      autoClose: 0,
      message: '',
      confirmationProceed: null,
      confirmationCancel: null
    },
    navbarHidden: false
  }),

  mounted () {
    this.$loading = this.$refs.loading
  },

  methods: {
    /**
     * Set the application layout.
     *
     * @param {String} layout
     */
    setLayout (layout) {
      if (!layout || !layouts[layout]) {
        layout = this.defaultLayout
      }

      this.layout = layouts[layout]
    },
    workspaceAdded () {
      this.$router.push({ name: 'home' })
    },
    hideNavbar (hidden = true) {
      this.navbarHidden = hidden
    }
  },

  computed: {
    isIframe () {
      return window.location !== window.parent.location || window.frameElement
    },
    isLoggedIn () {
      return window.hello2formsConfig.is_logged_in === '1'
    }
  },

  watch: {
  }
}
</script>
