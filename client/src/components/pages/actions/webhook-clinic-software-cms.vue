<template>
  <div>
    <div>
      Configure your clinic software experience:
    </div>

    <select-input name="actionData.account" label="ClinicSoftware Account" :form="form" class="mt-3 mb-6"
                  :uppercase-labels="false"
                  :required="true"
                  placeholder="Select a ClinicSoftware Account"
                  help="Select the ClinicSoftware Account you want to use for this request.
                  If you don't see the account you want, you can create a new one by typing something in the search box."
                  :allowCreation="true"
                  :customUpdateOptionFunction="true"
                  @update-options="updateAccountOptions"
                  :options="allAccountOptions">
      <template #option="{option, selected}">
        <span class="flex items-center group-hover:text-white">
          <button type="button"
                  class="opacity-0 group-hover:opacity-100 text-gray-400 group-hover:text-white hover:text-red-500 mr-3 flex-shrink-0"
                  title="Remove account"
                  @click.stop.prevent="removeAccount(option)">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd"/>
            </svg>
          </button>
          <p class="flex-grow group-hover:text-white">
            {{ option.name }}
          </p>
          <span v-if="selected" class="absolute inset-y-0 right-0 flex items-center pr-4 dark:text-white">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                    clip-rule="evenodd"/>
            </svg>
          </span>
        </span>
      </template>
    </select-input>

    <template v-if="form['actionData.account']">
      <select-input name="actionData.marketingList" label="Marketing Lists" :form="form" class="mt-3 mb-6"
                    :uppercase-labels="false"
                    :required="true"
                    placeholder="Select a Marketing List"
                    help="Select the marketing list to use, or type a new list name to create it for this action."
                    :allowCreation="true"
                    :customUpdateOptionFunction="true"
                    @update-options="addMarketingList"
                    :options="marketingLists"/>
    </template>

    <create-clinic-software-account-popup :name="createAccountPopup.name" :show="createAccountPopup.show"
                                          @donePopup="hidePopupShow"/>
  </div>
</template>

<script>
import VSelect from "../../forms/components/VSelect.vue";
import SelectInput from "../../forms/SelectInput.vue";
import ToggleSwitchInput from "../../forms/ToggleSwitchInput.vue";
import TextInput from "../../forms/TextInput.vue";
import axios from "../../../axios";
import CreateClinicSoftwareAccountPopup from "../integrations/createClinicSoftwareAccountPopup.vue";

export default {
  name: 'WebhookClinicSoftwareCms',
  components: {CreateClinicSoftwareAccountPopup, TextInput, ToggleSwitchInput, SelectInput, VSelect},
  props: {
    form: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      allAccountOptions: [],
      marketingLists: this.savedMarketingListOption(),
      createAccountPopup: {
        show: false,
        name: ''
      }
    }
  },
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
      axios.get('/wp-json/hello2forms/v1/IntegrationAccountsController/webhook.clinic_software.cms').then(response => {
        this.allAccountOptions = response.data.data;
      });
    },
    removeAccount(option) {
      if (!confirm('Remove this ClinicSoftware account?')) {
        return;
      }
      axios.delete('/wp-json/hello2forms/v1/IntegrationAccountsController/' + option.value).then(() => {
        this.allAccountOptions = this.allAccountOptions.filter((account) => account.value !== option.value);
        if (this.form['actionData.account'] === option.value) {
          this.$set(this.form, 'actionData.account', null);
        }
      });
    },
    savedMarketingListOption() {
      const current = this.form['actionData.marketingList'];
      return current ? [{value: String(current), name: String(current)}] : [];
    },
    addMarketingList(newItem) {
      if (!this.marketingLists.find((option) => String(option.value) === String(newItem.value))) {
        this.marketingLists.push(newItem);
      }
    },
    setMarketingLists(data) {
      const current = String(this.form['actionData.marketingList'] || '');
      const list = (data || []).map((option) => ({
        value: String(option.value),
        name: option.name || String(option.value)
      }));
      if (current && !list.find((option) => option.value === current)) {
        list.push({value: current, name: current});
      }
      this.marketingLists = list;
    },
    fetchMarketingLists(accountId) {
      if (!accountId) {
        this.marketingLists = this.savedMarketingListOption();
        return;
      }
      axios.get('/wp-json/hello2forms/v1/IntegrationsController/crm/marketing-list/' + accountId)
        .then(response => {
          this.setMarketingLists(response.data.data);
        })
        .catch(() => {
          // Keep the saved value visible even if the CRM fetch fails.
          this.marketingLists = this.savedMarketingListOption();
        });
    }
  },
  mounted() {
    this.fetchMarketingLists(this.form['actionData.account']);

    this.$watch(() => this.form['actionData.account'], (newVal) => {
      this.fetchMarketingLists(newVal);
    }, {immediate: true})

    this.loadAccounts();
  },
}
</script>
