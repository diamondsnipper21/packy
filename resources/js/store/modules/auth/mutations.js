export const mutations = {
    setLoggedIn(state, payload) {
        state.loggedIn = payload;
    },

    /**
     * set url valid status
     */
    setValidUrl(state, payload) {
        state.validUrl = payload;
    },

    /**
     * Change the frontend language used to display Wolfeo
     */
    changeUserFrontendLanguage(state, payload) {
        state.user.language = payload;
    },

    /**
     * Reset user / member
     */
    resetUser(state) {
        state.user = {
            id: 0,
            firstname: '',
            lastname: '',
            country: '',
            email: '',
            password: '',
            language: 'fr'
        };
    },

    /**
     * Set user
     */
    setUser(state, payload) {
        if (payload) {
            state.user = payload;
        }
    },

    /**
     * Set user property
     */
    setUserProp(state, payload) {
        state.user[payload.key] = payload.v;
    },

    setStripeConnected(state, payload) {
        state.stripeConnected = payload;
    },

    setStripeLoginLink(state, payload) {
        state.stripeLoginLink = payload;
    },
}