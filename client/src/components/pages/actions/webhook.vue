<template>
  <div>
    <text-input name="actionData.url" label="URL" :form="form" class="mt-4"/>
  </div>
</template>

<script>
import TextInput from "../../forms/TextInput.vue";

export default {
  name: 'Webhook',
  components: {TextInput},
  props: {
    form: {
      type: Object,
      required: true
    }
  },

}
</script>
