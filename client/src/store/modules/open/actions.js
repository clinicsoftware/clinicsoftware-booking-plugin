import axios from '../../../axios'
import {workspaceEndpoint} from "./workspaces";

export const formsEndpoint = '/wp-json/hello2forms/v1/ActionController'
export const namespaced = true
export let currentPage = 1

// state
export const state = {
  content: [],
  loading: false,
}

// getters
export const getters = {
  getById: (state) => (id) => {
    if (state.content.length === 0) return null
    return state.content.find(item => item.id === id)
  }
}

// mutations
export const mutations = {
  set(state, items) {
    state.content = items
  },
  append(state, items) {
    state.content = state.content.concat(items)
  },
  addOrUpdate(state, item) {
    state.content = state.content.filter((val) => val.id !== item.id)
    state.content.push(item)
  },
  remove(state, item) {
    state.content = state.content.filter((val) => val.id !== item.id)
  },
  startLoading(state) {
    state.loading = true
  },
  stopLoading(state) {
    state.loading = false
  }
}

// actions
export const actions = {
  resetState(context) {
    context.commit('set', [])
    context.commit('stopLoading')
    currentPage = 1
  },
  load(context, actionId) {
    context.commit('startLoading')
    return axios.get(formsEndpoint  + '?page=' + currentPage).then((response) => {
      context.commit((currentPage == 1) ? 'set' : 'append', response.data.data)
      if (currentPage < response.data.meta.last_page) {
        currentPage += 1
        context.dispatch('load', actionId)
      } else {
        context.commit('stopLoading')
        currentPage = 1
      }
    })
  },
  loadIfEmpty(context, actionId) {
    if (context.state.content.length === 0) {
      return context.dispatch('load', actionId)
    }
    context.commit('stopLoading')
    return Promise.resolve()
  },
  delete ({ commit, dispatch, state }, id) {
    commit('startLoading')
    return axios.delete(formsEndpoint + '/' + id).then((response) => {
      commit('remove', response.data.action_id)
      commit('stopLoading')
    })
  }
}
