import axios from "axios";

export default axios.create({
  headers: {
    'X-WP-Nonce' : window.hello2formsConfig.nonce
  }
});
