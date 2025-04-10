import { actions } from './actions'
import { mutations } from './mutations'

const state = {
    loggedIn: false,
    validUrl: null,
    currency: 'EUR', // @todo - get currency from env file
    stripeConnected: false,
    stripeLoginLink: null,
    user: {
        id: 0,
        firstname: '',
        lastname: '',
        country: '',
        email: '',
        password: '',
        language: 'fr'
    }
}

export const auth = {
    state,
    actions,
    mutations
};
