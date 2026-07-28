<template>
  <div>
    <h3 class="font-semibold text-2xl text-gray-900">Actions</h3>
    <small class="text-gray-600">Manage your actions.</small>

    <div v-if="loading" class="w-full text-blue-500 text-center">
      <loader class="h-10 w-10 p-5"/>
    </div>
    <div v-else>
      <div v-for="action in actions" :key="action.id"
          class="mt-4 flex group bg-white dark:bg-notion-dark items-center"
      >
        <div class="flex space-x-4 flex-grow items-center cursor-pointer" role="button" >
          <div class="py-1">
            <div class="font-bold truncate">{{action.name}}</div>
            <p class="text-gray-500 text-sm">Type: {{action.type}}</p>
          </div>
        </div>

        <div class="flex items-center content-center">
          <router-link :to="{
            name: 'actions.edit',
            params: {id: action.id}
          }" class=" me-4 v-btn py-2 px-4
        bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 focus:ring-offset-blue-200
        text-white transition ease-in duration-200 text-center text-base font-medium focus:outline-none focus:ring-2
        focus:ring-offset-2 rounded-lg flex items-center hover:no-underline" role="button">
            Edit Action
          </router-link>

          <div class="block text-red-500 p-2 rounded hover:bg-red-50" role="button"
               @click="deleteAction(action)">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {mapActions, mapState} from 'vuex'
import SeoMeta from '../../mixins/seo-meta.js'

export default {
  components: {},
  scrollToTop: false,
  mixins: [SeoMeta],
  data: () => ({
    metaTitle: 'Actions',
  }),

  mounted() {
    this.loadActions()
  },

  computed: {
    ...mapState({
      actions: state => state['open/actions'].content,
      loading: state => state['open/actions'].loading
    }),
  },

  methods: {
    ...mapActions({
      loadActions: 'open/actions/loadIfEmpty'
    }),
    deleteAction(action) {
      this.alertConfirm('Do you really want to delete this action? All forms created in this action will be removed.', () => {
        this.$store.dispatch('open/actions/delete', action.id).then(() => {
          this.alertSuccess('action successfully removed.')
        })
      })
    },
  }
}
</script>
