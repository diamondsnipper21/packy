import { actions } from './actions'
import { mutations } from './mutations'

const state = {
    communityMember: {
        community_id: 0,
        member_id: 0,
        access: -1,
    },
    data: {
        id: 0,
        vatRate: 23
    },
    settings: {
        community_id: 0,
        member_id: 0,
        popular_interval: 'weekly',
        unread_interval: 'hourly',
        likes: 1,
        reply: 1,
        admin_announce: 0,
        event_reminder: 1,
        api_key: ''
    },
    apiKey: {
        community_id: 0,
        member_id: 0,
        api_key: '',
    },
    communities: [],

    membershipSettings: {
        contentLoading: false,
        member: {}
    },

    loading: false
}

export const member = {
    state,
    actions,
    mutations
};
