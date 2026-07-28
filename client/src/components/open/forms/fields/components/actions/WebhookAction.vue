<template>
  <div v-if="field">
    <!-- General -->
    <div class="border-b py-2 px-4">
      <h3 class="font-semibold block text-lg">
        General
      </h3>
      <p class="text-gray-400 mb-3 text-xs">
        Webhook settings
      </p>

        <text-input name="data.url"
                    :form="field"
                    label="URL"
                    help="The URL to send the data to."
                    :required="true"
        />
    </div>
  </div>
</template>

<script>
export default {
  name: 'WebhookAction',
  props: {
    field: {
      type: Object,
      required: false
    },
    form: {
      type: Object,
      required: false
    }
  },
  computed: {},
  mounted() {
  },
  methods: {}
}
</script>
