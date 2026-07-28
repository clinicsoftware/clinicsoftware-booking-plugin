import Vue from 'vue'
import axios from "../axios"

const assetUrl = window.hello2formsConfig.VITE_VAPOR_ASSET_URL ?? '';

Vue.mixin({
  methods: {
    asset(path) {
      return assetUrl + path
    },

    /**
     * Store a file in S3 and return its UUID, key, and other information.
     */
    async storeFile(file, options = {}) {
      if (typeof options.progress === 'undefined') {
        options.progress = () => {
        }
      }
      const cleanAxios = axios
      let formData = new FormData();
      formData.append('file', file);
      const response = await cleanAxios.post('/wp-json/hello2forms/v1/UploadController/upload', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        },
        onUploadProgress: (progressEvent) => {
          options.progress(progressEvent.loaded / progressEvent.total)
        }
      })
      response.data.extension = file.name.split('.').pop();
      return response.data
    }
  }
})
