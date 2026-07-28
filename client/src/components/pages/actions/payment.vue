<template>
  <div>
    <div>
      Configure your stripe checkout experience:
    </div>

    <select-input name="actionData.account" label="Stripe Account" :form="form" class="mt-3 mb-6"
                  :uppercase-labels="false"
                  :required="true"
                  placeholder="Select a stripe account"
                  help="Select the stripe account you want to use for this payment.
                  If you don't see the account you want, you can create a new one by typing something in the search box."
                  :allowCreation="true"
                  :customUpdateOptionFunction="true"
                  @update-options="updateAccountOptions"
                  :options="allAccountOptions"/>

    <select-input name="actionData.lineItems"
                  label="Products"
                  :form="form"
                  class="mt-4"
                  :multiple="true"
                  option-key="default_price"
                  emit-key="default_price"
                  help="These items are shown as line items in the Checkout interface and make up the total amount to be collected by Checkout.
              Leaving this empty will allow you to choose the items from the builder."
                  :options="products"/>

    <toggle-switch-input name="actionData.disableAdvancedFraudDetection" :form="form" class="mt-4"
                         :uppercase-labels="false"
                         label="Disable Advanced Fraud Detection"/>

    <select-input name="actionData.billingAddressCollection" label="Billing Address Collection" :form="form"
                  class="mt-4"
                  :uppercase-labels="false"
                  help="If set to required, Checkout will attempt to collect the customer’s billing address.
              If not set or set to auto Checkout will only attempt to collect the billing address when necessary."
                  :options="[
                {value: 'auto', name: 'Auto'},
                {value: 'required', name: 'Required'},
              ]"/>

    <select-input name="actionData.shippingAddressCollection" label="Shipping Address Collection" :form="form"
                  class="mt-4"
                  :uppercase-labels="false"
                  help="If set to required, Checkout will attempt to collect the customer’s shopping address.
              If not set or set to auto Checkout will only attempt to collect the shopping address when necessary."
                  :options="[
                {value: 'auto', name: 'Auto'},
                {value: 'required', name: 'Required'},
              ]"/>

    <select-input name="actionData.submitType" label="Submit Type" :form="form" class="mt-4" :uppercase-labels="false"
                  :options="[
                {value: 'auto', name: 'Auto'},
                {value: 'pay', name: 'Pay'},
                {value: 'book', name: 'Book'},
                {value: 'donate', name: 'Donate'},
              ]"/>

    <text-input name="actionData.successUrl" label="Success URL" :form="form" class="mt-4"/>

    <text-input name="actionData.cancelUrl" label="Cancel URL" :form="form" class="mt-4"/>

    <create-account-popup :name="createAccountPopup.name" :show="createAccountPopup.show"
                          @donePopup="hidePopupShow"/>
  </div>
</template>

<script>
import VSelect from "../../forms/components/VSelect.vue";
import SelectInput from "../../forms/SelectInput.vue";
import ToggleSwitchInput from "../../forms/ToggleSwitchInput.vue";
import TextInput from "../../forms/TextInput.vue";
import CreateAccountPopup from "../integrations/createAccountPopup.vue";
import axios from "../../../axios";

export default {
  name: 'Payment',
  components: {CreateAccountPopup, TextInput, ToggleSwitchInput, SelectInput, VSelect},
  props: {
    form: {
      type: Object,
      required: true
    }
  },
  data: () => ({
    allAccountOptions: [
      // {value: 'test', name: 'Test'},
    ],
    createAccountPopup: {
      show: false,
      name: ''
    },
    products: []
  }),
  methods: {
    updateAccountOptions(searchTerm) {
      this.createAccountPopup.name = (searchTerm && typeof searchTerm === 'object') ? searchTerm.name : searchTerm;
      this.createAccountPopup.show = true;
    },
    hidePopupShow(value) {
      this.createAccountPopup.show = false
      if (value) {
        this.loadAccounts()
      }
    },
    loadAccounts() {
      axios.get('/wp-json/hello2forms/v1/IntegrationAccountsController/payment.stripe').then(response => {
        this.allAccountOptions = response.data.data;
      });
    }
  },
  mounted() {
    this.loadAccounts();

    axios.get('/wp-json/hello2forms/v1/IntegrationsController/stripe/products').then(response => {
      this.products = response.data.data;
    })
  },
}
</script>
