const pages = import.meta.glob('../pages/**')

function page (path) {
  return pages[`../pages/${path}`]
}

export default [
  // Logged Users
  { path: '/', name: 'home', component: page('home.vue') },

  // Forms
  { path: '/forms/create', name: 'forms.create', component: page('forms/create.vue') },
  { path: '/forms/create/guest', name: 'forms.create.guest', component: page('forms/create-guest.vue') },
  { path: '/forms/:slug/edit', name: 'forms.edit', component: page('forms/edit.vue') },
  {
    path: '/forms/:slug/show',
    component: page('forms/show/index.vue'),
    children: [
      { path: '', redirect: { name: 'forms. show' } },
      { path: 'submissions', name: 'forms.show', component: page('forms/show/submissions.vue') },
      { path: 'share', name: 'forms.show.share', component: page('forms/show/share.vue') },
      { path: 'analytics', name: 'forms.show.analytics', component: page('forms/show/stats.vue') }
    ]
  },

  // Settings
  {
    path: '/settings',
    component: page('settings/index.vue'),
    children: [
      { path: '', redirect: { name: 'settings.workspaces' } },
      { path: 'workspaces', name: 'settings.workspaces', component: page('settings/workspace.vue') },
    ]
  },

  // Actions
  {
    path: '/actions',
    component: page('actions/index.vue'),
    children: [
      { path: '', name: 'actions.list', component: page('actions/actions.vue') },
      { path: 'edit/:id', name: 'actions.edit', component: page('actions/edit.vue') },
      { path: 'new', name: 'actions.new', component: page('actions/edit.vue') },
    ]
  },

  // Guest Routes
  { path: '/forms/:slug', name: 'forms.show_public', component: page('forms/show-public.vue') },

  // Templates
  { path: '/form-templates', name: 'templates', component: page('templates/templates.vue') },
  { path: '/form-templates/:slug', name: 'templates.show', component: page('templates/show.vue') },

  { path: '*', component: page('errors/404.vue') }
]
