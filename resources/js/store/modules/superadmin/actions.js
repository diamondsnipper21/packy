import router from '../../../router';
import {notify} from "@kyvg/vue3-notification";

export const actions =
  {
    async SUPER_ADMIN_GET_DATA(context) {
      await axios.post("/super-admin/data/get").then(async response => {
        console.log('super_admin_get_data response', response);
        if (response.data.success) {
          context.commit('setLoggedIn', response.data.success);
          context.commit('setUser', response.data.user);
        }
      }).catch(error => {
        console.log('super_admin_get_data error', error);
      });
    },

    async SUPER_ADMIN_GET_PAYOUTS(context, payout) {
      await axios.post("/super-admin/payouts/get", {
        filter: payout.filter,
        sort: payout.sort,
        perPage: context.state.payouts.pagination.per_page,
        currentPage: context.state.payouts.pagination.current_page,
      }).then(async response => {
        console.log('super_admin_get_payouts response', response);
        if (response.data.success) {
          context.commit('setSuperAdminPayoutsTotals', response.data.payouts.totals);
          context.commit('setSuperAdminPayoutsFilters', response.data.payouts.filters);
          context.commit('setSuperAdminPayoutsData', response.data.payouts.data);
          context.commit('setSuperAdminPayoutsPagination', response.data.payouts.pagination);
        }
      }).catch(error => {
        console.log('super_admin_get_payouts error', error);
      });
    },

    async SUPER_ADMIN_PAYOUT_COMPLETE(context, payout) {
      await axios.post("/super-admin/payout/complete", {
        id: payout.id
      }).then(async response => {
        console.log('super_admin_payout_complete response', response);
        if (response.data.success) {
          context.commit('setSuperAdminPayoutsTotals', response.data.totals);

          let payouts = context.state.payouts.data;
          const index = payouts.findIndex(payout => {
            return (response.data.payout.id === payout.id)
          })
          payouts.splice(index, 1, response.data.payout)
          context.commit('setSuperAdminPayoutsData', payouts);
          context.commit('hideModal');

          notify({
            text: response.data.message,
            type: 'success'
          });
        }
      }).catch(error => {
        console.log('super_admin_payout_complete error', error);
        notify({
          text: 'Failed',
          type: 'error'
        });
      });
    },

    async SUPER_ADMIN_PAYOUT_REFUSE(context, id) {
      await axios.post("/super-admin/payout/refuse", {
        id: id
      }).then(async response => {
        console.log('super_admin_payout_refuse response', response);
        if (response.data.success) {
          context.commit('setSuperAdminPayoutsTotals', response.data.totals);

          let payouts = context.state.payouts.data;
          const index = payouts.findIndex(payout => {
            return (response.data.payout.id === payout.id)
          })
          payouts.splice(index, 1, response.data.payout)
          context.commit('setSuperAdminPayoutsData', payouts);
          context.commit('hideModal');
        }
      }).catch(error => {
        console.log('super_admin_payout_refuse error');
        notify({
          text: 'Failed',
          type: 'error'
        });
      });
    },

    async SUPER_ADMIN_GET_STATS(context, payload) {
      context.commit('setSuperAdminLoading', true);
      await axios.post("/super-admin/stats/get", {
        url: payload.url
      }).then(async response => {
        console.log('super_admin_get_stats response', response);
        context.commit('setSuperAdminLoading', false);
        if (response.data.success) {
          context.commit('setSuperAdminStats', response.data.stats);
        }
      }).catch(error => {
        console.log('super_admin_get_stats error', error);
      });
    },
  }