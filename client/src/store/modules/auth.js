import axios from '../../axios'
import Cookies from 'js-cookie'
import * as types from '../mutation-types'

// state
export const state = {
  user: null,
  token: Cookies.get('token'),

  // For admin impersonation
  admin_token: Cookies.get('admin_token') ?? null
}

// getters
export const getters = {
  user: state => state.user,
  token: state => state.token,
  check: state => state.user !== null,
}

// mutations
export const mutations = {
  [types.SAVE_TOKEN] (state, { token, remember }) {
    state.token = token
    Cookies.set('token', token, { expires: remember ? 365 : null })
  },

  [types.FETCH_USER_SUCCESS] (state, { user }) {
    state.user = user
  },

  [types.FETCH_USER_FAILURE] (state) {
    state.token = null
    Cookies.remove('token')
  },

  [types.LOGOUT] (state) {
    state.user = null
    state.token = null

    Cookies.remove('token')
  },

  [types.UPDATE_USER] (state, { user }) {
    state.user = user
  },
}

// actions
export const actions = {
  saveToken ({ commit, dispatch }, payload) {
  },

  async fetchUser ({ commit }) {
    try {
      const { data } = await axios.get('/wp-json/hello2forms/v1/UserController/current')

      commit(types.FETCH_USER_SUCCESS, { user: data })
      return data
    } catch (e) {
      commit(types.FETCH_USER_FAILURE)
    }
  },

  updateUser ({ commit }, payload) {
  },

  async logout ({ commit }) {

  },

  async fetchOauthUrl (ctx, { provider }) {

  },
}
