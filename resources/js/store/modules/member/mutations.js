export const mutations = {
    initMemberSettings(state) {
        state.settings = {
            community_id: 0,
            member_id: 0,
            popular_interval: 'weekly',
            unread_interval: 'hourly',
            likes: 1,
            reply: 1,
            admin_announce: 0,
            event_reminder: 1,
        };

        state.apiKey = {
            community_id: 0,
            member_id: 0,
            api_key: ''
        };
    },

    setMemberSettings(state, payload) {
        state.settings = payload;
    },

    setApiKey(state, payload) {
        state.apiKey = {
            ...state.apiKey,
            ...payload
        };
    },

    setMemberSettingsProperty(state, payload) {
        state.settings[payload.key] = payload.v;
    },

    setCommunityMember(state, payload) {
        state.communityMember = {
            ...state.communityMember,
            ...payload
        };
    },

    setMemberLoading(state, payload) {
        state.loading = payload
    },

    setMember(state, payload) {
        state.data = payload;
    },

    setMemberVatRate(state, payload) {
        state.data.vatRate = payload;
    },

    setComMember(state, payload)
    {
        state.data = {
            access: payload.access,
            id: payload.id,
            user_id: payload.user_id,
            firstname: payload.user.firstname,
            lastname: payload.user.lastname,
            tag: payload.user.tag,
            content: payload.user.bio,
            photo: payload.user.photo,
            link: payload.user.link,
            country: payload.user.country,
            role: payload.role
        };

        if (!state.data.tag) {
            let tag = payload.user.name.replace(' ', '_');
            state.data.tag = tag.replace(/^@+/, '');
        }
    },

    setMemberAccess(state, payload) {
        state.data.access = payload;
    },

    setMemberCommunities(state, payload) {
        state.communities = payload;
    },

    updateCommunities(state, payload) {
        let communities = JSON.parse(JSON.stringify(state.communities));
        let existed = false;
        communities = communities.map((community) => {
            if (community.id === payload.id) {
                community = payload;
                existed = true;
            }

            return { ...community };
        });

        if (!existed) {
            communities.unshift(payload);
        }

        state.communities = communities;
    },

    /**
     * Set membership settings property
     *
     * @param state
     * @param payload
     */
    setMembershipSettingsProperty(state, payload) {
        state.membershipSettings[payload.key] = payload.v;
    },

    /**
     * Set member settings member property
     *
     * @param state
     * @param payload
     */
    setMembershipSettingsMemberProperty(state, payload) {
        if (typeof state.membershipSettings.member !== 'undefined') {
            state.membershipSettings.member[payload.key] = payload.v;
        }
    }
}