<template>
  <div v-if="showSidebar"
        class="absolute shadow-lg shadow-blue-800/30 top-0 h-[calc(100vh-45px)] right-0 lg:shadow-none lg:relative bg-white w-full md:w-1/2 lg:w-2/5 border-l overflow-y-scroll md:max-w-[20rem] flex-shrink-0">

    <div class="p-4 border-b sticky top-0 z-10 bg-white">
      <div class="flex">
        <button class="text-gray-500 hover:text-gray-900 cursor-pointer" @click.prevent="closeSidebar">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                  stroke-linejoin="round"/>
          </svg>
        </button>
        <div class="font-semibold inline ml-2 truncate flex-grow truncate">
          Add Action
        </div>
      </div>
    </div>

    <div class="py-2 px-4">
      <div>
        <p class="text-gray-500 uppercase text-xs font-semibold mb-2">Actions</p>
        <div class="grid gap-2">
          <div v-for="(action, i) in actions" :key="i"
              class="bg-gray-50 border hover:bg-gray-100 dark:bg-gray-900 rounded-md dark:hover:bg-gray-800 py-2 flex flex-col"
              role="button" @click.prevent="addActionToList(action.id, action.type)"
          >
            <div class="mx-auto">
              {{ action.name}}
            </div>
            <p class="w-full text-xs text-gray-500 uppercase text-center font-semibold mt-1">{{defaultBlockNames[action.type]  }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {mapState} from 'vuex'
import Form from 'vform'
import clonedeep from 'clone-deep'
import axios from "../../../../../axios";

export default {
  name: 'AddFormActionSidebar',
  components: {},
  props: {},
  data() {
    return {
      blockForm: null,
      defaultBlockNames: {
        'payment.stripe': 'Stripe Payments',
        'webhook.clinic_software.cms': 'Send to ClinicSoftware CRM',
        'webhook': 'Webhook',
      },
      actions: [
        {
          name: 'stripe',
          title: 'Stripe Payment',
          icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>',
        },
        {
          name: 'webhook.clinic_software.cms',
          title: 'ClinicSoftware CRM',
          icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>',
        },
        {
          name: 'webhook',
          title: 'Webhook',
          icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>',
        },
      ]
    }
  },

  computed: {
    ...mapState({
      selectedFieldIndex: state => state['open/action_form'].selectedFieldIndex,
      showAddFieldSidebar: state => state['open/action_form'].showAddFieldSidebar
    }),
    form: {
      get() {
        return this.$store.state['open/working_form'].content
      },
      /* We add a setter */
      set(value) {
        this.$store.commit('open/working_form/set', value)
      }
    },
    showSidebar() {
      return (this.form && this.showAddFieldSidebar) ?? false
    },
  },

  watch: {},

  mounted() {
    axios
      .get('/wp-json/hello2forms/v1/ActionController')
      .then(({data: response}) => {
        this.actions = response.data
      });

    this.reset()
  },

  methods: {
    closeSidebar() {
      this.$store.commit('open/action_form/closeAddFieldSidebar')
    },
    reset() {
      this.blockForm = new Form({
        type: null,
        name: null,
      })
    },
    addActionToList(id,type) {
      if (this.form.actions === null || this.form.actions === undefined) {
        this.$set(this.form, 'actions', [])
      }

      this.blockForm.type = type;
      this.blockForm.name = this.defaultBlockNames[type];
      const newBlock = this.blockForm.data();

      // generate random number
      newBlock.id = id
      newBlock.type = type
      newBlock.data = {};

      if(this.selectedFieldIndex === null || this.selectedFieldIndex === undefined){
        const newFields = clonedeep(this.form.actions)
        newFields.push(newBlock)
        this.$set(this.form, 'actions', newFields)
        // this.$store.commit('open/action_form/openSettingsForField', this.form.actions.length-1)
      } else {
        const newFields = clonedeep(this.form.actions)
        newFields.splice(this.selectedFieldIndex+1, 0, newBlock)
        this.$set(this.form, 'actions', newFields)
        // this.$store.commit('open/action_form/openSettingsForField', this.selectedFieldIndex+1)
      }

      this.reset()
    }
  }
}
</script>
