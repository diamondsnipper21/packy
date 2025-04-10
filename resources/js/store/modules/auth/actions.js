import router from '../../../router';
import { notify } from "@kyvg/vue3-notification";
import { IncubateurStartStep } from '../../../data/enums';

export const actions = {
    /**
     * Change the frontend language display the User uses to interact with Packy
     */
    async CHANGE_USER_LANGUAGE(context, payload) {
        await axios.post('/lang/update', {
            language: payload.lang
        }).then(response => {
            console.log('change_user_language', response);
            if (response.data.success) {
                context.commit('changeUserFrontendLanguage', payload.lang);
                if (payload.reload) {
                    location.reload();
                }
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.log('change_user_language', error);
        });
    },

    /**
     * Try to log the user in
     */
    async LOGIN(context, payload) {
        context.commit('communityLoading', true);
        const inviteToken = localStorage.getItem('inviteToken') || '';

        let incubateurStart = false;
        if (typeof payload.incubateurStart !== 'undefined') {
            incubateurStart = payload.incubateurStart;
        }

        await axios.post('/login', { ...payload, inviteToken }).then(async response => {
            console.log('login response', response.data);
            context.commit('setLoggedIn', response.data.success);
            if (response.data.success) {
                localStorage.removeItem('inviteToken');
                context.commit('hideModal');
                context.commit('setUser', response.data.user);
                context.commit('communityLoading', false);

                if (incubateurStart && !response.data.incubateurMemberExist) {
                    context.commit('setIncubateurStartStep', IncubateurStartStep.SECOND_STEP);
                } else {
                    let validUrl = context.state.validUrl;
                    if (validUrl) {
                        await context.dispatch('GET_COMMUNITY', validUrl);

                        if (incubateurStart) {
                            let tab = 'about';
                            let path = '/' + validUrl + '/' + tab;
                            router.push(path).catch(err => { });
                            context.commit('setCommunityTab', tab);
                        }
                    } else {
                        validUrl = response.data.validUrl;
                        context.commit('setValidUrl', validUrl);
                        localStorage.setItem('communityUrl', validUrl);

                        window.location.href = '/' + validUrl;
                    }
                }
            } else {
                context.commit('communityLoading', false);

                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.log('login error', error);
        });
    },

    /**
     * Try to sign up the user
     */
    async SIGN_UP(context, payload) {
        context.commit('communityLoading', true);

        if (context.rootState.communitycenter.selectedTab) {
            payload.selectedTab = context.rootState.communitycenter.selectedTab;
            payload.communityId = context.rootState.community.data.id;
        }

        const inviteToken = localStorage.getItem('inviteToken') || '';
        await axios.post('/signup', { ...payload, inviteToken }).then(async response => {
            console.log('signup response', response.data);

            context.commit('communityLoading', false);

            let extraAction = null;
            if (typeof payload.extraAction !== 'undefined') {
                extraAction = payload.extraAction;
            }

            let incubateurStart = null;
            if (typeof payload.incubateurStart !== 'undefined') {
                incubateurStart = payload.incubateurStart;
            }

            if (response.data.success) {
                context.commit('hideModal');
                context.commit('setLoggedIn', true);
                context.commit('setUser', response.data.user);

                let validUrl = context.state.validUrl;
                if (!validUrl) {
                    validUrl = response.data.validUrl;
                    context.commit('setValidUrl', validUrl);
                    localStorage.setItem('communityUrl', validUrl);
                }
                localStorage.removeItem('inviteToken');

                if (!incubateurStart) {
                    await context.dispatch('GET_COMMUNITY', validUrl);
                    router.push('/'+validUrl).catch(()=>{});
                }
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.log('signup error', error);
        });
    },

    /**
     * Try to complete signup user
     */
    async COMPLETE_PROFILE(context) {
        context.commit('communityLoading', true);

        let user = context.state.user;

        const inviteToken = localStorage.getItem('inviteToken') || ''

        await axios.post('/profile/complete', { ...user, inviteToken }).then(async response => {
            console.log('complete_profile response', response.data);

            if (response.data.success) {
                context.commit('setLoggedIn', true);

                context.commit('setUser', response.data.user);

                let validUrl = context.state.validUrl;
                if (validUrl) {
                    await context.dispatch('GET_COMMUNITY', validUrl);
                } else {
                    validUrl = response.data.validUrl;
                    context.commit('setValidUrl', validUrl);
                    localStorage.setItem('communityUrl', validUrl);
                }
                window.location.href = '/' + validUrl;

                context.commit('communityLoading', false);
            } else {
                context.commit('communityLoading', false);

                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.log('complete_profile error', error);
        });
    },

    /**
     * Send email for resetting password
     */
    async SEND_RESET_PASSWORD_EMAIL(context, payload) {
        await axios.post('/send-reset-password', payload).then(async response => {
            console.log('send_reset_password response', response.data);

            if (response.data.success) {
                notify({
                    text: response.data.message,
                    type: 'success'
                });
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.log('send_reset_password error', error);
        });
    },

    /**
     * Reset password
     */
    async RESET_PASSWORD(context, payload) {
        await axios.post('/reset-password', payload).then(async response => {
            console.log('reset_password response', response.data);

            if (response.data.success) {
                let from = '';
                if (typeof payload.from !== 'undefined') {
                    from = payload.from;
                }

                notify({
                    text: response.data.message,
                    type: 'success'
                });

                context.commit('hideModal');

                if (from !== 'settings') {
                    setTimeout(() => {
                        context.commit('resetUser');
                        context.commit('showModal', {
                            type: 'Login',
                            transparent: true
                        });
                    }, 500);
                }
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.log('reset_password error', error);
        });
    },

    /**
     * Validate community url
     */
    async VALIDATE_URL(context, payload) {
        console.log('validate_url', payload);

        await axios.post("/validate-url", {
            url: payload
        }).then(async response => {
            console.log('validate_url response', response);

            if (response.data.success) {
                context.commit('setValidUrl', payload);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.log('validate_url error', error);
        });
    },

    /**
     * View profile
     */
    async VIEW_PROFILE(context, payload) {
        console.log('view_profile', payload);

        await axios.post("/get-profile", {
            tag: payload
        }).then(async response => {
            console.log('view_profile response', response);

            if (response.data.success) {
                context.commit('setUser', response.data.user);
                context.commit('setComMember', response.data.member);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.log('view_profile error', error);
        });
    },


    /**
     * Try to log in as another user
     */
    async ADMIN_LOG_IN_AS_USER(context, payload) {
        await axios.get('/user/log/' + payload).then(response => {
            console.log('admin_log_in_as_user', response);

            context.commit('setLoggedIn', response.data.success);

            if (response.data.success) {
                context.commit('hideModal');

                context.commit('setUser', response.data.user);

                let validUrl = response.data.validUrl;
                context.commit('setValidUrl', validUrl);
                localStorage.setItem('communityUrl', validUrl);

                window.location.href = '/' + validUrl;
            } else {
                context.commit('communityLoading', false);

                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.error('admin_log_in_as_user', error);
        });
    },

    /**
     * Get one user
     */
    async GET_USER(context, payload) {
        console.log('get_user', payload)

        await axios.get('/user/get/' + payload).then(response => {
            console.log('get_current_user', response);

            if (response.data.success) {
                context.commit('setUser', response.data.user);
                if (response.data.user.member) {
                    context.commit('setComMember', response.data.user.member);
                }
                context.commit('setUnreadChatsCnt', response.data.unreadChatsCnt);
            } else {
                context.commit('communityLoading', false);

                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.error('get_current_user', error);
        });
    }
};