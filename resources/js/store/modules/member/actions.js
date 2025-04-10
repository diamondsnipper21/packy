import { notify } from "@kyvg/vue3-notification";

export const actions = {
    async GET_MEMBER_SETTINGS(context, payload) {
        context.commit('initMemberSettings');
        context.commit('setCommunityMember', { community_id: payload.id, member_id: payload.memberId });

        if (!(payload.id && payload.memberId)) return
        context.commit('setMemberLoading', true);
        try {
            const { data: result } = await axios.get(`/c/communities/${payload.id}/members/${payload.memberId}/settings`);
            if (result.success) {
                context.commit('setMemberSettings', result.data.setting);
                context.commit('setApiKey', result.data.api_key || {});
            }
        } catch (e) {
        }
        context.commit('setMemberLoading', false);
    },

    async GENERATE_API_KEY(context) {
        context.commit('setMemberLoading', true);
        try {
            const { data: result } = await axios.put(`/c/communities/${context.state.communityMember.community_id}/members/${context.state.communityMember.member_id}/settings/api_key`);
            if (result.success) {
                context.commit('setApiKey', result.data);
            }
        } catch (e) {
            const text = e.response?.data?.message || 'Unknown Error'
            notify({ text, type: 'error' });
        }
        context.commit('setMemberLoading', false);
    },

    /**
     * Save notification settings
     */
    async STORE_MEMBER_SETTINGS(context) {
        console.log('STORE_MEMBER_SETTINGS', context.state.communityMember)

        context.commit('setMemberLoading', true);
        try {
            const { data: result } = await axios.post(`/c/communities/${context.state.communityMember.community_id}/members/${context.state.communityMember.member_id}/settings`, {
                popular_interval: context.state.settings.popular_interval,
                unread_interval: context.state.settings.unread_interval,
                likes: context.state.settings.likes,
                reply: context.state.settings.reply,
                admin_announce: context.state.settings.admin_announce,
                event_reminder: context.state.settings.event_reminder
            })

            console.log('STORE_MEMBER_SETTINGS result', result)

            if (result.success) {
                context.commit('setMemberSettings', result.data);
                notify({
                    text: result.message,
                    type: 'success'
                });
                context.commit('hideModal');
            }
        } catch (e) {
            const text = e.response?.data?.message || 'Unknown Error'
            notify({ text, type: 'error' });
        }
        context.commit('setMemberLoading', false);
    },

    async GET_MEMBER(context, payload) {
        try {
            const { data: result } = await axios.get(`/c/communities/${payload.url}/members/me`);
            console.log('get_member response', result);
            context.commit('setMember', result.data || {});
            return result.data || {}
        } catch (e) {
            context.commit('setMember', {});
        }
        return {}
    },

    async GET_MEMBER_COMMUNITIES(context, payload) {
        try {
            const { data: result } = await axios.get(`/c/members/communities`);
            if (result.success) {
                context.commit('setMemberCommunities', result.data || []);
            }
        } catch (e) {
        }
    },

    async GET_COMMUNITY_MEMBER(context, payload) {
        try {
            const { data: result } = await axios.get(`/c/communities/${payload.community_id}/members/${payload.member_id}`);
            if (result.success) {
                context.commit('setCommunityMember', result.data || {});
            }
        } catch (e) {
            const text = e.response?.data?.message || 'Unknown Error'
            notify({ text, type: 'error' });
        }
    },

    async GET_ASSIGNABLE_MEMBERS(context, payload) {
        try {
            const { data: result } = await axios.get(`/c/communities/${context.rootState.community.data?.id}/members/assignable?query=${payload}`);
            return result.data;
        } catch (e) {
        }
        return [];
    }
};