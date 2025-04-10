import router from '../../../router';
import { notify } from "@kyvg/vue3-notification";
import {CommunityStatus, IncubateurStartStep} from "../../../data/enums";
import {loadStripe} from "@stripe/stripe-js";

export const actions = {
    /**
     * Create new community
     */
    async CREATE_COMMUNITY(context, payload) {
        console.log('create_community', payload);

        context.commit('setCommunityLoading', true);
        try {
            let incubateurStart = false;
            if (typeof payload.incubateurStart !== 'undefined') {
                incubateurStart = payload.incubateurStart;
            }

            const { data: result } = await axios.post('/c/communities', payload);
            console.log('create_community result', result);

            // 3DS check - OK
            let pending_setup_intent = result.pending_setup_intent;
            if (pending_setup_intent) {
                let stripeInstance = await loadStripe(stripeKey, {
                    stripeAccount: undefined,
                    apiVersion: undefined,
                    locale: 'auto',
                });

                if (payload.stripeInstance) {
                    stripeInstance = payload.stripeInstance;
                }

                const { result2, error } = await stripeInstance.confirmCardSetup(pending_setup_intent);
                if (error) {
                    notify({
                        text: error.message,
                        type: 'error'
                    });

                    await axios.post('/community/plan/cancel');

                    context.commit('setCommunityLoading', false);
                    return;
                }
            }

            if (result.success) {
                const result3 = await axios.post('/community/publish');

                context.commit('updateCommunities', result3.data);
                context.commit('hideModal');
                context.commit('showResetModal');

                // For only 'INCUBATEUR' community, we will go to 4th step
                if (incubateurStart) {
                    context.commit('setIncubateurStartStep', IncubateurStartStep.FORTH_STEP);
                } else {
                    router.push('/' + result3.data.url).catch(err => { });
                    await context.dispatch('GET_COMMUNITY', result3.data.url);
                }
            } else {
                notify({
                    text: result.message,
                    type: 'error'
                });
            }
        } catch (e) {
            console.log(e);

            const text = e.response?.data?.message || 'Unknown Error'
            notify({ text, type: 'error' });
        }
        context.commit('setCommunityLoading', false);
    },

    async UPDATE_COMMUNITY(context) {
        if (!context.state.clone.id) return
        context.commit('setCommunityLoading', true);

        if (context.state.tempMedia && typeof context.state.tempMedia.id === 'undefined') {
            context.state.clone.medias.unshift(context.state.tempMedia);
        }

        try {
            const { data: result } = await axios.put(`/c/communities/${context.state.clone.id}`, context.state.clone);
            if (result.success) {
                context.commit('updateCommunities', result.data);
                context.commit('hideModal');
                context.commit('showResetModal');
                router.push('/' + context.state.clone.url).catch(err => { });

                await context.dispatch('GET_COMMUNITY_DATA', { url: context.state.clone.url });
                context.commit('setAboutDescriptionView', 'view');

                notify({
                    text: result.message,
                    type: 'success'
                });
            }
        } catch (e) {
            const text = e.response?.data?.message || 'Unknown Error'
            notify({ text, type: 'error' });
        }
        context.commit('setCommunityLoading', false);
    },

    async GET_COMMUNITY_DATA(context, payload) {
        context.commit('setCommunityLoading', true);
        try {
            const { data: result } = await axios.get(`/c/communities/${payload.url}`,);
            console.log('get_community_data', result.data)

            context.commit('setCommunityData', result.data);

            let medias = JSON.parse(JSON.stringify(result.data.medias));
            if (medias.length > 0) {
                context.commit('selectCommunityMedia', medias[0]);
            }

            if (typeof result.data.notifications !== 'undefined') {
                context.commit('setCommunityNotifications', result.data.notifications);
            } else {
                context.commit('setNewPaymentNotification', false);
            }
            if (typeof result.data.verificationCode !== 'undefined') {
                context.commit('setVerificationCode', result.data.verificationCode);
            }
            context.commit('setAppName', result.app_name);
            context.commit('setCommunityLoading', false);
            return result.data
        } catch (e) {
            console.log('ERROR', e)
            context.commit('setCommunityData', {});
            context.commit('setCommunityLoading', false);
        }
    },

    async GET_COMMUNITY_PLAN(context, payload) {
        context.commit('setCommunityLoading', true);
        try {
            const communityId = payload?.id || context.state.community?.id
            const { data: result } = await axios.get(`/c/communities/${communityId}/plans`);

            console.log('get_community_plans', result.data);

            context.commit('setCommunityPlan', result.data || {});
        } catch (e) {
            context.commit('setCommunityPlan', {});
        }
        context.commit('setCommunityLoading', false);
    },

    async DOWNLOAD_COMMUNITY_PLAN_INVOICE(context, payload) {
        console.log('download_community_plan_invoice payload', payload)

        context.commit('setCommunityLoading', true);
        try {
            const communityId = payload?.id || context.state.community?.id
            const { data: result } = await axios.post(`/c/communities/${communityId}/plan/invoice/download`, {
                id: payload.id,
                invoice: payload.invoice
            });

            console.log('download_community_plan_invoice result', result);
            if (result.success && result.download_url) {
                console.log('result.download_url', result.download_url)
                window.open(result.download_url, '_blank');
            }
        } catch (e) {
            const text = e.response?.data?.message || 'Unknown Error'
            console.error('download_community_plan_invoice error', text);
        }
        context.commit('setCommunityLoading', false);
    },

    async UPDATE_PLAN_PAYMENT_METHOD(context, payload) {
        context.commit('setCommunityLoading', true);
        try {
            const { data: result } = await axios.post(`/c/communities/${payload.communityId}/plan/card/update`, payload);

            console.log('UPDATE_PLAN_PAYMENT_METHOD result', result)

            // 3DS check
            let pending_setup_intent = result.pending_setup_intent;
            if (pending_setup_intent && payload.stripeInstance) {
                const { result2, error } = await payload.stripeInstance.confirmCardSetup(pending_setup_intent);
                if (error) {
                    notify({
                        text: error.message,
                        type: 'error'
                    });
                    context.commit('setCommunityLoading', false);
                    return;
                }
            }

            notify({
                text: 'Credit card updated', // @todo - translation
                type: 'success'
            });

            context.commit('setCommunityPlan', result.data || {});
        } catch (e) {
            console.log('UPDATE_PLAN_PAYMENT_METHOD e', e)

            const text = e.response?.data?.message || 'Unknown Error'
            notify({ text, type: 'error' });
        }
        context.commit('setCommunityLoading', false);
        return {}
    },

    async CANCEL_PLAN(context, payload) {
        context.commit('setCommunityLoading', true);
        try {
            const { data: result } = await axios.delete(`/c/communities/${payload.communityId}/plans`, { data: { reason: payload.reason } });
            if (result.success) {
                context.commit('setCommunityPlan', result.data);
            }
        } catch (e) {
            const text = e.response?.data?.message || 'Unknown Error'
            notify({ text, type: 'error' });
        }
        context.commit('setCommunityLoading', false);
        return false
    },

    async GET_PLAN_INVOICES(context, payload) {
        context.commit('setCommunityLoading', true);
        let result = []
        try {
            const { data: resultData } = await axios.get(`/c/communities/${payload.communityId}/plans/invoices?page=${payload.page || 0}&limit=${payload.limit}`);
            if (resultData.success) {
                result = resultData.data
            }
        } catch (e) {
            const text = e.response?.data?.message || 'Unknown Error'
            notify({ text, type: 'error' });
        }
        context.commit('setCommunityLoading', false);
        return result
    },

    async REACTIVATE_COMMUNITY(context, payload) {
        console.log('reactivate_community', payload)

        context.commit('setCommunityLoading', true);
        try {
            const { data: result } = await axios.post(`/c/communities/${payload.communityId}/reactivate`, payload);
            console.log('reactivate_community result', result)

            let stripeInstance = await loadStripe(stripeKey, {
                stripeAccount: undefined,
                apiVersion: undefined,
                locale: 'auto',
            });

            // 3DS check - OK
            let pending_setup_intent = result.pending_setup_intent;
            if (pending_setup_intent) {
                if (payload.stripeInstance) {
                    stripeInstance = payload.stripeInstance;
                }

                const { result2, error } = await stripeInstance.confirmCardSetup(pending_setup_intent);
                if (error) {
                    notify({
                        text: error.message,
                        type: 'error'
                    });
                    await axios.post('/community/plan/cancel');
                    context.commit('setCommunityLoading', false);
                    return;
                }
            }

            let payment_intent_client_secret = result.payment_intent_client_secret;
            if (payment_intent_client_secret) {
                const { result2, error } = await stripeInstance.confirmPayment({
                    clientSecret: payment_intent_client_secret,
                    redirect: 'if_required'
                });

                if (error) {
                    notify({
                        text: error.message,
                        type: 'error'
                    });
                    context.commit('setCommunityLoading', false);
                    return;
                }

                result.data.status = 'active'
            }

            const result3 = await axios.post('/community/publish');

            console.log('result3', result3)

            context.commit('setCommunityPlan', result3.data.plan || {});
            context.commit('setCommunityStatus', CommunityStatus.PUBLISHED);

            notify({
                text: 'The community has been reactivated',
                type: 'success'
            });
        } catch (e) {
            console.log(e);

            const text = e.response?.data?.message || 'Unknown Error'
            notify({ text, type: 'error' });
        }
        context.commit('setCommunityLoading', false);
        return {}
    },

    async GET_REWARDS_LEVELS(context, payload) {
        context.commit('setCommunityLoading', true);
        try {
            const { data: result } = await axios.get(`/c/communities/${payload.communityId}/levels`);
            context.commit('setRewardsLevels', result.data || []);
        } catch (e) {
            const text = e.response?.data?.message || 'Unknown Error'
        }
        context.commit('setCommunityLoading', false);
        return {}
    },

    async GET_REWARD_LEVEL(context, payload) {
        context.commit('setCommunityLoading', true);
        try {
            const { data: result } = await axios.get(`/c/communities/${payload.communityId}/levels/${payload.id}`);
            context.commit('setRewardLevel', result.data || {});
        } catch (e) {
            const text = e.response?.data?.message || 'Unknown Error'
        }
        context.commit('setCommunityLoading', false);
        return {}
    },

    async UPDATE_REWARD_LEVEL(context, payload) {
        context.commit('setCommunityLoading', true);
        try {
            const { data: result } = await axios.put(`/c/communities/${payload.communityId}/levels/${payload.id}`, { name: payload.name });
            context.commit('setRewardLevel', result.data || {});
            context.commit('updateRewardsLevels', result.data || {});
        } catch (e) {
            const text = e.response?.data?.message || 'Unknown Error'
            notify({ text, type: 'error' });
        }
        context.commit('setCommunityLoading', false);
        return {}
    },

    async ADD_MEDIA_FOR_COMMUNITY(context, payload) {
        context.commit('setCommunityLoading', true);
        try {
            const { data: result } = await axios.post(`/c/communities/${payload.communityId}/media/save`, payload.body);
            if (result.success) {
                context.commit('updateCommunities', result.data);
                await context.dispatch('GET_COMMUNITY_DATA', { url: result.data.url });
            }
        } catch (e) {
            const text = e.response?.data?.message || 'Unknown Error'
            notify({ text, type: 'error' });
        }
        context.commit('setCommunityLoading', false);
        return {}
    },

    async REMOVE_COMMUNITY_MEDIA(context, payload) {
        context.commit('setCommunityLoading', true);
        try {
            const { data: result } = await axios.post(`/c/communities/${payload.communityId}/media/delete`, payload);
            if (result.success) {
                context.commit('hideModal');
                context.commit('updateCommunities', result.data);
                await context.dispatch('GET_COMMUNITY_DATA', { url: result.data.url });
            }
        } catch (e) {
            const text = e.response?.data?.message || 'Unknown Error'
            notify({ text, type: 'error' });
        }
        context.commit('setCommunityLoading', false);
        return {}
    },

    async CHANGE_COMMUNITY_MEDIA_ORDER(context, payload) {
        context.commit('setCommunityLoading', true);
        try {
            const { data: result } = await axios.post(`/c/communities/${payload.communityId}/media/update-order`, payload.body);
            if (result.success) {
                context.commit('changeAboutMediaOrder', payload.body);
            }
        } catch (e) {
            const text = e.response?.data?.message || 'Unknown Error'
            notify({ text, type: 'error' });
        }
        context.commit('setCommunityLoading', false);
        return {}
    },

    async SAVE_ABOUT_DESCRIPTION(context, payload) {
        context.commit('setCommunityLoading', true);
        try {
            const { data: result } = await axios.post(`/c/communities/${payload.communityId}/save-about-description`, payload.body);
            if (result.success) {
                context.commit('updateCommunities', result.data);
                await context.dispatch('GET_COMMUNITY_DATA', { url: result.data.url });
            }
        } catch (e) {
            const text = e.response?.data?.message || 'Unknown Error'
            notify({ text, type: 'error' });
        }
        context.commit('setCommunityLoading', false);
        return {}
    },

    async GET_MEMBER_SUMMARY(context, payload) {
        context.commit('setCommunityLoading', true);

        await new Promise((resolve) => setTimeout(() => { resolve(); }, 300));

        try {
            const { data: result } = await axios.get(`/c/communities/${payload.communityId}/member/${payload.memberId}`, {});
            if (result.success) {
                context.commit('setMentionedMemberInfo', result.data);
            }
        } catch (e) {
            const text = e.response?.data?.message || 'Unknown Error'
            notify({ text, type: 'error' });
        }
        context.commit('setCommunityLoading', false);
        return {}
    }
};