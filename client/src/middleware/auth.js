import store from '~/store'

export default async (to, from, next) => {
  if (!store.getters['auth/check']
  ) {
    try {
      await store.dispatch('auth/fetchUser')
    } catch (e) {
      console.error(e, 'error')
    }
  }
  next()
}
