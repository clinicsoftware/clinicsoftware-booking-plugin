<template>
  <nav v-if="hasNavbar" class="bg-white dark:bg-notion-dark border-b">
    <div class="max-w-4xl mx-auto px-4">
      <div class="flex items-center justify-between h-16">
        <div class="flex items-center">
          <router-link :to="{name: 'home'}" class="flex-shrink-0 font-semibold hover:no-underline flex items-center">
            <img :src="asset('img/logo.jpg')" alt="logo" class="w-8 h-8 rounded">

            <span
              class="ml-2 text-md sm:inline hidden text-black dark:text-white"
            >
              HELLO2FORMS
            </span>
          </router-link>
          <workspace-dropdown class="ml-6"/>
        </div>
        <div class="hidden md:block ml-auto relative">
          <router-link :to="{name:'templates'}"
                       class="text-sm text-gray-600 dark:text-white hover:text-gray-800 cursor-pointer mt-1 mr-8">
            Templates
          </router-link>

          <router-link :to="{name: 'home'}" class="text-sm text-gray-600 dark:text-white hover:text-gray-800 cursor-pointer mt-1 mr-8"
          >
            My Forms
          </router-link>

          <router-link :to="{name: 'settings.workspaces'}" class="text-sm text-gray-600 dark:text-white hover:text-gray-800 cursor-pointer mt-1 mr-8"
          >
            Workspaces
          </router-link>

          <router-link :to="{name: 'actions.list'}" class="text-sm text-gray-600 dark:text-white hover:text-gray-800 cursor-pointer mt-1 mr-8"
          >
            Actions
          </router-link>

          <a :href="wordPressUrl"
             class="text-sm text-gray-600 dark:text-white hover:text-gray-800 cursor-pointer mt-1 mr-8">
            WP Admin
          </a>
        </div>
<!--        <div class="block">-->
<!--          <div class="flex items-center">-->
<!--            <div class="ml-3 mr-4 relative">-->
<!--              <div class="relative inline-block text-left">-->
<!--                <dropdown  dusk="nav-dropdown">-->
<!--                  <template #trigger="{toggle}">-->
<!--                    <button id="dropdown-menu-button" type="button"-->
<!--                            class="flex items-center justify-center w-full rounded-md  px-4 py-2 text-sm text-gray-700 dark:text-gray-50 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-gray-500"-->
<!--                            dusk="nav-dropdown-button" @click.prevent="toggle()"-->
<!--                    >-->
<!--                      <img :src="user.photo_url" class="rounded-full w-6 h-6">-->
<!--                      <p class="ml-2 hidden sm:inline">-->
<!--                        {{ user.name }}-->
<!--                      </p>-->
<!--                    </button>-->
<!--                  </template>-->

<!--                  <a :href="wordPressUrl"-->
<!--                               class="block block px-4 py-2 text-md text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-100 dark:hover:text-white dark:hover:bg-gray-600 flex items-center"-->
<!--                  >-->
<!--                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"-->
<!--                         stroke="currentColor"-->
<!--                    >-->
<!--                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"-->
<!--                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"-->
<!--                      />-->
<!--                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"-->
<!--                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"-->
<!--                      />-->
<!--                    </svg>-->
<!--                    Go Back to WordPress-->
<!--                  </a>-->
<!--                </dropdown>-->

<!--              </div>-->
<!--            </div>-->
<!--          </div>-->
<!--        </div>-->
      </div>
    </div>
  </nav>
</template>

<script>
import {mapGetters} from 'vuex'
import Dropdown from './common/Dropdown.vue'
import WorkspaceDropdown from './WorkspaceDropdown.vue'

export default {
  components: {
    WorkspaceDropdown,
    Dropdown
  },

  data: () => ({
    appName: window.hello2formsConfig.appName,
  }),

  computed: {
    wordPressUrl: () => window.hello2formsConfig.wordpress_location,
    form() {
      if (this.$route.name && this.$route.name.startsWith('forms.show_public')) {
        return this.$store.getters['open/forms/getBySlug'](this.$route.params.slug)
      }
      return null
    },
    workspace () {
      return this.$store.getters['open/workspaces/getCurrent']()
    },
    paidPlansEnabled() {
      return window.hello2formsConfig.paid_plans_enabled
    },
    showAuth() {
      return this.$route.name && !this.$route.name.startsWith('forms.show_public')
    },
    hasNavbar() {
      if (this.isIframe) return false

      if (this.$route.name && this.$route.name.startsWith('forms.show_public')) {
        if (this.form) {
          // If there is a cover, or if branding is hidden remove nav
          if (this.form.cover_picture || this.form.no_branding) {
            return false
          }
        } else {
          return false
        }
      }
      return !this.$root.navbarHidden
    },
    isIframe() {
      return window.location !== window.parent.location || window.frameElement
    },
    ...mapGetters({
      user: 'auth/user'
    }),
  },

  methods: {
    async logout() {
      // Log out the user.
      await this.$store.dispatch('auth/logout')

      // Reset store
      this.$store.dispatch('open/workspaces/resetState')
      this.$store.dispatch('open/forms/resetState')

      // Redirect to login.
      this.$router.push({name: 'login'})
    },
  }
}
</script>
