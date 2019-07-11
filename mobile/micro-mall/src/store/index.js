import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

const store = new Vuex.Store({
    state: { userId: '' },
    mutations: {
        setUserId(state, id) { state.userId = id }
    }
})

export default store
