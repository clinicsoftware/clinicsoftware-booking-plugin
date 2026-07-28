<template>
  <div class="px-4">
    <form @submit.prevent="createAction" @keydown="form.onKeydown($event)">
      <div>
        <text-input name="name" class="mt-4" :form="form" :required="true"
                    label="Action Name"
        />

        <select-input name="type" class="mt-4" :form="form" :required="true"
                      label="Action Type"
                      :options="actionTypes"/>
      </div>

      <payment v-if="form.type === 'payment.stripe'" :form="form"/>
      <webhook v-if="form.type === 'webhook'" :form="form"/>
      <webhook-clinic-software-cms v-if="form.type === 'webhook.clinic_software.cms'" :form="form"/>

      <div class="w-full mt-6">
        <v-button :loading="form.busy" class="w-full my-3">Save Action</v-button>
      </div>
    </form>
  </div>
</template>

<script>
import Form from 'vform'
import {mapActions, mapState} from 'vuex'
import SeoMeta from '../../mixins/seo-meta.js'
import Payment from "../../components/pages/actions/payment.vue";
import toDotNot from "../../functions/toDotNot";
import Webhook from "../../components/pages/actions/webhook.vue";
import WebhookClinicSoftwareCms from "../../components/pages/actions/webhook-clinic-software-cms.vue";

export default {
  components: {WebhookClinicSoftwareCms, Webhook, Payment},
  scrollToTop: false,
  mixins: [SeoMeta],
  data: () => ({
    metaTitle: 'Actions',
    form: new Form({
      name: '',
      type: '',
      'actionData.url': '',
      'actionData.account': null,
      'actionData.marketingList': '',
      'actionData.lineItems': {},
      'actionData.disableAdvancedFraudDetection': false,
      'actionData.customerEmail': "",
      'actionData.billingAddressCollection': "auto",
      'actionData.shippingAddressCollection': "auto",
      'actionData.submitType': "auto",
      'actionData.successUrl': "",
      'actionData.cancelUrl': "",
    }),
  }),
  async mounted() {
    await this.loadActions()

    const action = this.action
    if (action) {
      action.actionData = action.data
      this.form = new Form(toDotNot(action));
    }
  },
  computed: {
    action() {
      return this.$store.getters['open/actions/getById'](parseInt(this.$route.params.id));
    },
    actionTypes() {
      return [
        {value: 'payment.stripe', name: 'Payment'},
        {value: 'webhook', name: 'Webhook'},
        {value: 'webhook.clinic_software.cms', name: 'Clinic Software CRM'},
      ]
    },
    ...mapState({
      actions: state => state['open/actions'].content,
      loading: state => state['open/actions'].loading,
    }),
    wpToken() {
      return window.hello2formsConfig.nonce
    },
  },

  methods: {
    ...mapActions({
      loadActions: 'open/actions/loadIfEmpty',
    }),
    async createAction() {
      const {data} = await this.form.post('/wp-json/hello2forms/v1/ActionController', {
        headers: {
          'X-WP-NONCE': this.wpToken
        }
      })
      this.alertSuccess(data.message || 'Action saved successfully.')
      await this.$store.dispatch('open/actions/load')
      this.$router.push({name: 'actions.list'})
    }
  }
}
</script>
