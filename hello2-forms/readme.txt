=== Hello2 Forms ===
Contributors: infiniteconsultancy, clinicsoftware
Donate link: https://clinicsoftware.com
Tags: forms, contact form, booking, leads, appointments
Requires at least: 5.9
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Builder for Stylish & Smart Forms (Bookings, Marketing, Leads, Appointments).

== Description ==

Hello2 Forms is a powerful form builder for WordPress. Create beautiful, smart forms for bookings, marketing, leads and appointments with an easy-to-use interface.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/hello2-forms`, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the "Plugins" screen in WordPress.
3. Use the Hello2 Forms menu item to create and manage your forms.

== Frequently Asked Questions ==

= Does the plugin require PHP 8.1? =

Yes, Hello2 Forms requires PHP 8.1 or newer.

== External services ==

This plugin connects to several third-party services to deliver its features. Each service below is used only when the corresponding feature is enabled in the plugin settings or in a specific form.

= hCaptcha =
Used to protect forms against spam and automated submissions.
When a form with hCaptcha enabled is submitted, the plugin loads `https://hcaptcha.com/1/api.js` and sends the user's hCaptcha response token to hCaptcha for verification.
Service provider: hCaptcha (https://www.hcaptcha.com)
Terms of service: https://www.hcaptcha.com/terms
Privacy policy: https://www.hcaptcha.com/privacy

= Google Calendar / Google Identity Services =
Used to let form authors connect a Google Calendar account and create calendar events from form submissions, and to let end-users sign in with Google.
When this feature is enabled, the plugin loads `https://accounts.google.com/gsi/client` and `https://apis.google.com/js/api.js` in the user's browser, and the Google Calendar API (`https://www.googleapis.com/calendar/v3/`) is called on the server using OAuth credentials entered by the site admin. Data sent: the OAuth access/refresh tokens, the calendar event payload created from the form submission (title, description, start/end times, attendee email addresses), and standard API request metadata.
Service provider: Google (https://www.google.com)
Terms of service: https://policies.google.com/terms
Privacy policy: https://policies.google.com/privacy

= Stripe =
Used to collect payments on forms.
When a form includes a Stripe payment field, the plugin loads Stripe.js from `https://js.stripe.com/v3` in the user's browser and uses the site's Stripe API key on the server to call the Stripe API (`https://api.stripe.com`) for product listing, payment intents, etc. Card details are sent directly from the browser to Stripe and are not stored on the WordPress server.
Service provider: Stripe (https://stripe.com)
Terms of service: https://stripe.com/legal/ssa
Privacy policy: https://stripe.com/privacy

= Slack =
Used to send a notification to a Slack channel when a form is submitted.
When a form has a Slack webhook URL configured, the plugin POSTs the form submission data (configurable per form) directly to that webhook URL at `https://hooks.slack.com/...`. The webhook URL is supplied by the site admin in the plugin settings and is never sent to the plugin author.
Service provider: Slack (https://slack.com)
Terms of service: https://slack.com/terms-of-service
Privacy policy: https://slack.com/trust/privacy/privacy-policy

= OpenAI =
Used by the optional "AI reply" form field to generate a chatbot-style response to the user's input.
When an end-user submits a message in a form that has the OpenAI field enabled, the plugin sends the message text (and the surrounding conversation context, if any) to `https://api.openai.com/v1/chat/completions` from the WordPress server, authenticated with the API key the site admin entered in the plugin settings. The response text is returned to the end-user. No end-user identifying information is added by this plugin beyond what the form itself collected.
Service provider: OpenAI (https://openai.com)
Terms of service: https://openai.com/policies/row-terms-of-use
Privacy policy: https://openai.com/policies/row-privacy-policy


== Changelog ==

= 1.0.0 =
* Initial release.

== Source ==

Source code and issue tracker: https://github.com/clinicsoftware/clinicsoftware-booking-plugin
