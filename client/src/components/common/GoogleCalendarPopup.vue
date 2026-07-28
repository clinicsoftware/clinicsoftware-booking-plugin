<script>
import Modal from "../Modal.vue";

const CLIENT_ID = window.hello2formsConfig.integrations.google.calendar.client_id;
const API_KEY = window.hello2formsConfig.integrations.google.calendar.client_secret;

// Discovery doc URL for APIs used by the quickstart
const DISCOVERY_DOC = 'https://www.googleapis.com/discovery/v1/apis/calendar/v3/rest';

// Authorization scopes required by the API; multiple scopes can be
// included, separated by spaces.
const SCOPES = 'https://www.googleapis.com/auth/calendar';


let tokenClient;
let gapiIni = "";
let gapiInited = false;
let gisInited = false;

export default {
  name: 'GoogleCalendarPopup',
  components: {Modal},
  props: {
    eventName: {
      type: String,
      required: true
    },
    type: {
      type: String,
      required: true
    },
    extraData: {
      type: Object,
    }
  },
  data() {
    return {
      page_data: {},
      gapiInited: false,
      meeting_url: '',
      startDate: this.extraData.start_date ?? new Date(),
      endDate: this.extraData.end_date ?? new Date('+1 hour'),
    }
  },
  computed: {},
  watch: {},
  mounted() {
    const plugin1 = document.createElement("script");
    plugin1.setAttribute(
      "src",
      "https://accounts.google.com/gsi/client"
    );
    plugin1.async = true;
    plugin1.onload='gapiLoaded()'
    document.head.appendChild(plugin1);

    const plugin2 = document.createElement("script");
    plugin2.setAttribute(
      "src",
      "https://apis.google.com/js/api.js"
    );
    plugin2.async = true;
    plugin2.onload='gapiLoaded()'
    document.head.appendChild(plugin2);

    setTimeout( ()=>{ this.gapiLoaded();
      console.log('Google Calendar API Initialised....') },3000 )
  },
  methods: {
    gapiLoaded() {
      gapi.load('client', this.initializeGapiClient);
    },
    async initializeGapiClient() {  // initialize client
      await gapi.client.init({
        apiKey: API_KEY,
        discoveryDocs: [DISCOVERY_DOC],
      });
      gapiInited = true;
      this.gapiInited = true;
      this.gisLoaded();
      this.maybeEnableButtons();
    },
    gisLoaded() {
      tokenClient = google.accounts.oauth2.initTokenClient({
        client_id: CLIENT_ID,
        scope: SCOPES,
        callback: '', // defined later
      });
      gisInited = true;
      this.maybeEnableButtons();
    },
    maybeEnableButtons() { // your can ignore this method
      if (gapiInited && gisInited) {
        this.authorized = true;
        return;
      }
      this.authorized = false;
    },
    async handleAuthClickForInPerson() {
      this.getAuthToken(); // to generate Auth token for calender.
      this.createNewEvent().then(() =>
        // here you can show success message for successful task.
        console.log('Success Meeting created!'))
    },
    async getAuthToken() {
      if (!this.authorized) {
        console.log('Error', 'You have not permission to accept google calendar service!');
      }

      // Very first time you will not get token from here. so you have to call this again. next time you will get
      // token if user has given access to google.

      if (gapi.client.getToken() === null) {
        // Prompt the user to select a Google Account and ask for consent to share their data
        // when establishing a new session.

        // {prompt: 'consent'} :: will ask permission every time. so that i am not using....
        tokenClient.requestAccessToken({});

      } else {
        // Skip display of account chooser and consent dialog for an existing session.
        this.auth_token = gapi.client.getToken() ? gapi.client.getToken().access_token : "";
      }
    },
    generateString(length) {
      let pass = "";
      for (let l = 0; l < length; l++) {
        const rand = Math.random() * (126 - 33) + 33;
        pass += String.fromCharCode(~~rand);
      }
      return pass;
    },
    async createNewEvent() {
      let startDate = new Date(this.startDate);
      let interviewStartDate = new Date(startDate.getFullYear() + '-' + (startDate.getMonth() + 1) + '-' + startDate.getDate() + ' ' + this.startDate);
      let interviewEndDate = new Date(startDate.getFullYear() + '-' + (startDate.getMonth() + 1) + '-' + startDate.getDate() + ' ' + this.endDate);
      const timezone = new Date().getTimezoneOffset();

      const event = {
        "summary": this.eventName,
        "location": "Online",
        'start': {
          'dateTime': interviewStartDate.toISOString(),
          'timeZone': timezone
        },
        'end': {
          'dateTime': interviewEndDate.toISOString(),
          'timeZone': timezone
        },
      };

      const request = gapi.client.calendar.events.insert({
        'calendarId': 'primary',
        'resource': event,
        'sendNotifications': true // it is required to send instant email notification.
      });

      request.execute((event) => {
        if (this.type.toLocaleLowerCase() === 'add_to_google_meet') {
          const eventPatch = {
            conferenceData: {
              createRequest: {requestId: this.generateString(10)},
            }
          };

          gapi.client.calendar.events.patch({
            calendarId: "primary",
            eventId: event.id,
            resource: eventPatch,
            conferenceDataVersion: 1
          }).execute((conference) => {
            this.meeting_url = conference.hangoutLink;

            console.log("Conference created for event: %s", conference.hangoutLink);
          });

        }

      });
    }
  }
}
</script>

<template>
  <modal :show="gapiInited" @close="() => ''">
    <template #icon>
      <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-blue" fill="none" viewBox="0 0 24 24"
           stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.068.157 2.148.279 3.238.364.466.037.893.281 1.153.671L12 21l2.652-3.978c.26-.39.687-.634 1.153-.67 1.09-.086 2.17-.208 3.238-.365 1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"
        />
      </svg>
    </template>

    <template #title>
      <span>Get a reminder on your Google Calendar</span>
    </template>

    <p>
      Do you want to be kept up to date with this event? Add it to your Google Calendar and get a reminder.
    </p>

    <div class="flex mx-2" v-if="extraData && extraData.allowUserToSelect">
      <p class="text-gray-900 px-4">From</p>
      <input :type="'datetime-local'" id="start_date" v-model="startDate" name="start_date" data-date-format="YYYY-MM-DD" class="flex-grow border-transparent focus:outline-none "
      />
      <p class="text-gray-900 px-4">To</p>
      <input :type="'datetime-local'" id="endDate" v-model="endDate" name="start_date" data-date-format="YYYY-MM-DD" class="flex-grow border-transparent focus:outline-none "
      />
    </div>

    <button
      @click="handleAuthClickForInPerson"
      class="btn mt-2 px-8 mx-1 py-2 px-4 text-base transition ease-in duration-200 text-center font-semibold shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 rounded-lg filter hover:brightness-110 w-full mt-4"
      style="background-color: rgb(59, 130, 246); color: rgb(255, 255, 255); --tw-ring-color: #3B82F6;" type="submit" :form="{}">
      Add to Google Calendar
    </button>
  </modal>
</template>

<style scoped lang="scss">

</style>
