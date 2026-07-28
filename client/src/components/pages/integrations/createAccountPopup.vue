<template>
  <modal :show="show" max-width="lg">
    <template #icon>
      <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path
          d="M12 8V16M8 12H16M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </template>
    <template #title>
      Link Stripe Account
    </template>
    <div class="px-4">
      <form @submit.prevent="createAccount" @keydown="form.onKeydown($event)">
        <div>
          <text-input name="pk" class="mt-4" :form="form.account" :required="true"
                      help="Stripe's publishable key, you can retrieve this from your Stripe dashboard."
                      label="Publishable Key"
          />
        </div>
        <div class="w-full mt-6">
          <v-button :loading="form.busy" class="w-full my-3">Save Stripe Account</v-button>
        </div>
      </form>
    </div>
  </modal>

</template>

<script>
import Form from 'vform'

export default {
  components: {},
  props: {
    show: {
      type: Boolean,
      required: true,
      default: false
    },
    name: {
      type: String,
      required: true,
      default: ""
    }
  },
  data: () => ({
    form: new Form({
      account: {
        pk: ''
      },
      type: 'payment.stripe',
      name: ''
    }),
  }),
  mounted() {
  },
  computed: {
    wpToken() {
      return window.hello2formsConfig.nonce
    },
  },
  methods: {
    async createAccount() {
      this.$set(this.form, 'name', this.name)

      this.form.post('/wp-json/hello2forms/v1/IntegrationAccountsController', {
        headers: {
          'X-WP-NONCE': this.wpToken
        }
      }).then(() => {
        this.alertSuccess('Stripe account linked successfully.')
        this.form.reset()
        this.$emit('donePopup', true)
      }).catch((error) => {
        const message = error?.response?.data?.error || error?.response?.data?.message || 'Failed to link Stripe account.'
        this.alertError(message)
      })
    }
  }
}
</script>
