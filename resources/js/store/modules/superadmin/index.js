import { actions } from './actions'
import { mutations } from './mutations'

const state = {
    loading: true,
    payouts: {
        totals: {
            balance: 0,
            pending: 0,
            paid: 0
        },
        filters: {
            users: [],
            communities: [],
            statuses: []
        },
        data: [],
        pagination: {
            per_page: 10,
            total_rows: 0,
            current_page: 1,
            first_page: 1,
            last_page: 1
        }
    },
    selected_payout: null,

    selectedTab: 'payouts',
    stats: null,
}

export const superadmin = {
    state,
    actions,
    mutations
};
