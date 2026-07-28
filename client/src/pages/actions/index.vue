<template>
  <div class="bg-white">
    <div class="flex bg-gray-50">
      <div class="w-full md:w-4/5 lg:w-3/5 md:mx-auto md:max-w-4xl px-4">
        <div class="pt-4 pb-0">
          <div class="flex w-100 justify-between">
            <div>
              <div class="flex">
                <h2 class="flex-grow text-gray-900">
                  Manage Actions
                </h2>
              </div>
              <ul class="flex text-gray-500">
                <li>{{ user.email }}</li>
              </ul>
            </div>
            <router-link :to="{
              name: 'actions.new'
            }">
              <v-button :loading="loading" class="mt-4">
                <svg class="inline text-white mr-1 h-4 w-4" viewBox="0 0 14 14" fill="none"
                     xmlns="http://www.w3.org/2000/svg">
                  <path d="M6.99996 1.16699V12.8337M1.16663 7.00033H12.8333" stroke="currentColor" stroke-width="1.67"
                        stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Create new action
              </v-button>
            </router-link>
          </div>
          <div class="mt-4 border-b border-gray-200 dark:border-gray-700">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
              <li class="mr-6" v-for="(tab, i) in tabsList" :key="i+1">
                <router-link :to="{ name: tab.route }"
                             class="hover:no-underline inline-block py-4 rounded-t-lg border-b-2 text-gray-500 hover:text-gray-600"
                             active-class="text-blue-600 hover:text-blue-900 dark:text-blue-500 dark:hover:text-blue-500 border-blue-600 dark:border-blue-500"
                >
                  {{ tab.name }}
                </router-link>
              </li>
            </ul>
          </div>

        </div>
      </div>
    </div>
    <div class="flex bg-white">
      <div class="w-full md:w-4/5 lg:w-3/5 md:mx-auto md:max-w-4xl px-4">
        <div class="mt-8 pb-0">
          <transition name="fade" mode="out-in">
            <router-view/>
          </transition>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {mapGetters} from 'vuex'

export default {
  middleware: 'auth',
  computed: {
    ...mapGetters({
      user: 'auth/user'
    }),
    tabsList() {
      return [
        {
          name: 'Lists',
          route: 'actions.list'
        },
      ]
    }
  },
}
</script>
