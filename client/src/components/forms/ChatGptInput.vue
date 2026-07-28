<template>
  <div :class="wrapperClass">
    <label v-if="label" :for="id?id:name"
           :class="[theme.CodeInput.label,{'uppercase text-xs':uppercaseLabels, 'text-sm':!uppercaseLabels}]"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 required-dot">*</span>
    </label>
    <div v-if="help && helpPosition=='above_input'" class="flex mb-1">
      <small :class="theme.default.help" class="flex-grow">
        <slot name="help"><span class="field-help" v-html="help" /></slot>
      </small>
    </div>

    <textarea :id="id?id:name" v-model="message" :disabled="disabled"
              :class="[theme.default.input,{ '!ring-red-500 !ring-2': hasValidation && form.errors.has(name), '!cursor-not-allowed !bg-gray-200':disabled }]"
              class="resize-y"
              @keyup.enter="askGpt"
              :name="name" :style="inputStyle"
              placeholder="Ask here and then press ENTER to submit."
    />

    <div class="mt-4">
      <p v-if="reply === ''">
        Your reply will render here...
      </p>
      <p v-else>
        <b>Chat GPT: </b> {{ reply }}
      </p>
    </div>
    <div class="flex">
      <small v-if="help && helpPosition=='below_input'" :class="theme.default.help" class="flex-grow">
        <slot name="help"><span class="field-help" v-html="help" /></slot>
      </small>
      <small v-else class="flex-grow" />
      <small :class="theme.default.help">
        <a :class="theme.default.help" href="#" @click.prevent="clear">Clear</a>
      </small>
    </div>
    <has-error v-if="hasValidation" :form="form" :field="name" />
  </div>
</template>

<script>
import inputMixin from '~/mixins/forms/input.js'
import axios from '../../axios'

export default {
  name: 'ChatGptInput',
  components: {},
  mixins: [inputMixin],
  data() {
    return {
      message: '',
      reply: '',
    }
  },
  methods: {
    askGpt() {
      axios.post('/wp-json/hello2forms/v1/IntegrationsController/chat-gpt', { message: this.message })
        .then(response => {
          this.reply = response.data.reply
        })
        .catch(error => {
          console.error(error)
        })
    },
  }
}
</script>
