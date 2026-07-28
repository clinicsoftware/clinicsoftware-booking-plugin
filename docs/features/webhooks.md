# Webhooks

## What is a webhook?

A webhook is a mechanism for allowing one application to provide other applications with real-time information. It's a way for one application to automatically notify another application when a certain event has occurred.

Here's how it typically works:

1. **Trigger Event:** An event occurs in one application. This could be anything from a new user signup, a file upload, a payment confirmation, or any other significant action.

2. **HTTP POST Request:** The application that detected the event sends an HTTP POST request to a pre-configured URL, which is the webhook endpoint. This request contains information about the event, often in JSON or XML format.

3. **Webhook Receiver:** The application that owns the webhook endpoint receives the HTTP POST request. It processes the information contained in the request and takes appropriate actions based on the event.

Webhooks are commonly used in various scenarios such as:

- **Integration between applications:** They allow different applications or services to communicate and synchronize data in real-time.

- **Automation:** Webhooks can trigger automated actions based on events, reducing the need for manual intervention.

- **Notifications:** They can be used to send alerts or notifications to external systems or users when specific events occur.

- **Data Synchronization:** Webhooks can be used to keep data synchronized between different systems or databases.

Overall, webhooks provide a flexible and efficient way for applications to communicate and interact with each other in real-time, enabling automation, integration, and timely notifications.

## What data do we send?

Below is an example of the date we send when a form is submitted:

```json
{
  "id": "1",
  "workspace_id": "1",
  "title": "My Form",
  "slug": "my-form",
  "properties": [
    {
      "name": "Name",
      "type": "text",
      "hidden": false,
      "required": true,
      "id": "4c1ae46b-18fc-4f74-a3fd-322f4659d50a",
      "placeholder": null,
      "prefill": null,
      "help": null,
      "help_position": "below_input"
    },
    {
      "name": "Email",
      "type": "email",
      "hidden": false,
      "id": "34ca1adc-e8f9-4188-8d48-f78157e412c8",
      "placeholder": null,
      "prefill": null,
      "help": null,
      "help_position": "below_input"
    },
    {
      "name": "Message",
      "type": "text",
      "hidden": false,
      "multi_lines": true,
      "id": "904da3de-734c-4a45-9bc1-c73cf597f60d",
      "placeholder": null,
      "prefill": null,
      "help": null,
      "help_position": "below_input"
    }
  ],
  "created_at": "2024-03-14 06:32:20",
  "updated_at": "2024-03-14 06:32:20",
  "notifies": "0",
  "description": null,
  "submit_button_text": "Submit",
  "re_fillable": true,
  "re_fill_button_text": "Fill Again",
  "color": "#3B82F6",
  "uppercase_labels": true,
  "no_branding": true,
  "hide_title": true,
  "submitted_text": "<p>Amazing, we saved your answers. Thank you for your time and have a great day!<\/p>",
  "dark_mode": "auto",
  "webhook_url": null,
  "send_submission_confirmation": "0",
  "logo_picture": null,
  "cover_picture": null,
  "redirect_url": null,
  "custom_code": null,
  "notification_emails": null,
  "theme": "default",
  "database_fields_update": [
    "4c1ae46b-18fc-4f74-a3fd-322f4659d50a"
  ],
  "width": "centered",
  "transparent_background": true,
  "closes_at": null,
  "closed_text": "This form has now been closed by its owner and does not accept submissions anymore.",
  "notification_subject": "We saved your answers",
  "notification_body": "Hello there \ud83d\udc4b <br>This is a confirmation that your submission was successfully saved.",
  "notifications_include_submission": "1",
  "use_captcha": "0",
  "can_be_indexed": "1",
  "password": null,
  "notification_sender": "Hello2Forms",
  "tags": [],
  "deleted_at": null,
  "creator_id": "1",
  "removed_properties": [],
  "max_submissions_count": null,
  "max_submissions_reached_text": "This form has now reached the maximum number of allowed submissions and is now closed.",
  "slack_webhook_url": null,
  "visibility": "public",
  "editable_submissions": true,
  "discord_webhook_url": null,
  "editable_submissions_button_text": "Edit submission",
  "confetti_on_submission": true,
  "seo_meta": {
    "page_title": null,
    "page_description": null,
    "page_thumbnail": null
  },
  "notification_settings": [],
  "submission_mode": null,
  "submission_extra_data": [],
  "creator": {
    "id": 1,
    "name": "leonardo",
    "email": "leonard@clinicsoftware.com",
    "email_verified_at": null,
    "created_at": "2024-02-12T18=>21=>57.000000Z",
    "updated_at": "2024-02-12T18=>21=>57.000000Z",
    "stripe_id": null,
    "pm_type": null,
    "pm_last_four": null,
    "trial_ends_at": null,
    "workspaces_count": 1,
    "photo_url": "http:\/\/0.gravatar.com\/avatar\/fc626f370fde04c7be8154e8313e3411?s=96&d=mm&r=g",
    "pivot": [],
    "subscriptions": [],
    "template_editor": true
  },
  "views": [],
  "share_url": "http:\/\/wordpress.lndo.site\/form\/#\/forms\/my-form",
  "views_count": "0",
  "submissions": [],
  "submissions_count": "0",
  "last_edited_human": "12 hours ago",
  "extra": {
    "loadedWorkspace": {
      "id": "1",
      "created_at": "2024-03-14 05:03:14",
      "updated_at": "2024-03-14 05:03:14",
      "name": "leonardo's Personal Workspace",
      "icon": "\ud83d\udce5",
      "owners": [
        {
          "id": 1,
          "name": "leonardo",
          "email": "leonard@clinicsoftware.com",
          "email_verified_at": null,
          "created_at": "2024-02-12T18=>21=>57.000000Z",
          "updated_at": "2024-02-12T18=>21=>57.000000Z",
          "stripe_id": null,
          "pm_type": null,
          "pm_last_four": null,
          "trial_ends_at": null,
          "workspaces_count": 1,
          "photo_url": "http:\/\/0.gravatar.com\/avatar\/fc626f370fde04c7be8154e8313e3411?s=96&d=mm&r=g",
          "pivot": [],
          "subscriptions": [],
          "template_editor": true
        }
      ],
      "is_enterprise": false,
      "is_pro": false,
      "pivot": [
        {
          "id": "1",
          "workspace_id": "1",
          "user_id": "1",
          "created_at": "2024-03-14 05:03:14",
          "updated_at": "2024-03-14 05:03:14",
          "role": "admin"
        }
      ]
    },
    "workspaceIsPro": false,
    "userIsOwner": true,
    "cleanings": []
  },
  "workspace": {
    "id": "1",
    "created_at": "2024-03-14 05:03:14",
    "updated_at": "2024-03-14 05:03:14",
    "name": "leonardo's Personal Workspace",
    "icon": "\ud83d\udce5",
    "owners": [
      {
        "id": 1,
        "name": "leonardo",
        "email": "leonard@clinicsoftware.com",
        "email_verified_at": null,
        "created_at": "2024-02-12T18=>21=>57.000000Z",
        "updated_at": "2024-02-12T18=>21=>57.000000Z",
        "stripe_id": null,
        "pm_type": null,
        "pm_last_four": null,
        "trial_ends_at": null,
        "workspaces_count": 1,
        "photo_url": "http:\/\/0.gravatar.com\/avatar\/fc626f370fde04c7be8154e8313e3411?s=96&d=mm&r=g",
        "pivot": [],
        "subscriptions": [],
        "template_editor": true
      }
    ],
    "is_enterprise": false,
    "is_pro": false,
    "pivot": [
      {
        "id": "1",
        "workspace_id": "1",
        "user_id": "1",
        "created_at": "2024-03-14 05:03:14",
        "updated_at": "2024-03-14 05:03:14",
        "role": "admin"
      }
    ]
  }
}
```
