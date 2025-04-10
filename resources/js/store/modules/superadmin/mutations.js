export const mutations = {
    setSuperAdminLoading(state, payload) {
        state.loading = payload
    },
    setSuperAdminPayoutsTotals(state, payload) {
        state.payouts.totals = payload
    },
    setSuperAdminPayoutsFilters(state, payload) {
        state.payouts.filters = payload
    },
    setSuperAdminPayoutsData(state, payload) {
        state.payouts.data = payload
    },
    setSuperAdminPayoutsPagination(state, payload) {
        state.payouts.pagination = payload
    },
    setSuperAdminPayoutsPaginationPerPage(state, payload) {
        state.payouts.pagination.per_page = payload
    },
    setSuperAdminPayoutsPaginationCurrentPage(state, payload) {
        state.payouts.pagination.current_page = payload
    },
    setSuperAdminSelectedPayout(state, payload) {
        state.selected_payout = payload
    },

    /**
     * Set super admin tab
     */
    setSuperAdminTab(state, payload) {
        state.selectedTab = payload;
    },

    /**
     * Set super admin stats
     */
    setSuperAdminStats(state, payload) {
        state.stats = payload
    },
}