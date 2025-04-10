<template>
  <div class="container pl-0 pr-0">
    <loading v-if="loading" :active.sync="loading" :is-full-page="true" />

    <div class="columns" v-else>
      <div class="column">
        <div style="display: flex; gap: 15px;">
          <div class="box counter">
            <div class="counter-amount">
              <a :href="'https://dashboard.stripe.com/test/balance/overview'"
                 target="_blank"><b>{{ formatAmountWithCurrency('EUR', totals.balance / 100) }}</b></a>
            </div>
            <div class="counter-text">{{ $t('community.members.setting-modal.payouts.available-stripe-balance') }}</div>
          </div>

          <div class="box counter" :class="this.hasFilter ? 'filtered' : ''">
            <div class="counter-amount">
              <span v-if="loadingPayouts"><font-awesome-icon icon="fa fa-circle-notch" class="link-icon fa-spin" /></span>
              <a v-else @click="filterPayoutsByStatus('pending')">{{ formatAmountWithCurrency('EUR', totals.pending / 100) }}</a>
            </div>
            <div class="counter-text">{{ $t('community.members.setting-modal.payouts.pending-payouts') }}</div>
          </div>

          <div class="box counter" :class="this.hasFilter ? 'filtered' : ''">
            <div class="counter-amount" @click="filterPayoutsByStatus('pending')">
              <span v-if="loadingPayouts"><font-awesome-icon icon="fa fa-circle-notch" class="link-icon fa-spin" /></span>
              <a v-else @click="filterPayoutsByStatus('complete')">{{ formatAmountWithCurrency('EUR', totals.paid / 100) }}</a>
            </div>
            <div class="counter-text">{{ $t('community.members.setting-modal.payouts.paid-payouts') }}</div>
            </div>
        </div>

        <div class="box">
          <div class="mb-5" style="border-bottom: 1px solid #ddd; padding-bottom: 20px;">
            <div style="display: flex; flex-direction: row; gap: 10px;">
              <div style="display: flex; flex-direction: row; gap: 10px; flex: 1;">
                <div>
                  <span class="font-14px">{{ $t('common.date-start') }}</span><br/>
                  <input
                      class="input"
                      v-model="filter.date_start"
                      type="date"
                      placeholder="Date start"
                      :disabled="loadingPayouts || payouts.length === 0"
                      @change="applyFilter"/>
                </div>

                <div>
                  <span class="font-14px">{{ $t('common.date-end') }}</span><br/>
                  <input
                      class="input"
                      v-model="filter.date_end"
                      type="date"
                      placeholder="Date end"
                      :disabled="loadingPayouts || payouts.length === 0"
                      @change="applyFilter"/>
                </div>
              </div>

              <div style="flex: 1;">
                <span class="font-14px">{{ $t('common.search') }}</span><br/>
                <input
                    class="input"
                    v-model="filter.search"
                    type="text"
                    :placeholder="$t('common.search')"
                    :disabled="loadingPayouts"
                    @change="applyFilter"/>
              </div>

              <div style="flex: 1;">
                <span class="font-14px">{{ $t('common.user') }}</span><br/>
                <select
                    class="input tab-content-select"
                    v-model="filter.user"
                    :disabled="loadingPayouts || users.length === 0"
                    @change="applyFilter">
                  <option :value="null">–</option>
                  <template v-for="user in users">
                    <option v-if="user" :value="user.id">{{ user.name }}</option>
                  </template>
                </select>
              </div>

              <div style="flex: 1;">
                <span class="font-14px">{{ $t('common.status') }}</span><br/>
                <select
                    class="input tab-content-select"
                    v-model="filter.status"
                    :disabled="loadingPayouts"
                    @change="applyFilter">
                  <option :value="null">–</option>
                  <option value="pending">{{ $t('status.pending') }}</option>
                  <option value="complete">{{ $t('status.complete') }}</option>
                  <option value="failed">{{ $t('status.failed') }}</option>
                </select>
              </div>

              <div style="flex: 1;">
                <span class="font-14px">{{ $t('common.community') }}</span><br/>
                <select
                    class="input tab-content-select"
                    v-model="filter.community"
                    :disabled="loadingPayouts || communities.length === 0"
                    @change="applyFilter">
                  <option :value="null">–</option>
                  <template v-for="community in communities">
                    <option v-if="community" :value="community.id">{{ community.name }}</option>
                  </template>
                </select>
              </div>

              <div style="display: flex; gap: 10px; padding-top: 24px;">
                <button
                    class="button is-medium community-blue-btn"
                    @click="clearFilter"
                    :disabled="loadingPayouts"
                    v-if="hasFilter">Clear</button>
              </div>
            </div>
          </div>

          <div v-if="loadingPayouts">
            <loading :active.sync="loadingPayouts" style="position: relative; width: 100px; margin: 0 auto; height: 500px;"/>
          </div>
          <template v-else>
            <div v-if="payouts.length > 0">
              <div class="font-14px" style="margin-bottom: 10px;">
                {{ $t('community.members.setting-modal.payouts.payouts-found', { count: pagination.total_rows }) }}
              </div>

              <table class="table is-hoverable is-fullwidth pointer">
                <thead>
                  <tr>
                    <th @click="sortBy('id')">#</th>
                    <th @click="sortBy('status')">{{ $t('common.status') }}</th>
                    <th @click="sortBy('created_at')">{{ $t('common.date') }}</th>
                    <th @click="sortBy('user.name')">{{ $t('common.user') }}</th>
                    <th @click="sortBy('community.name')">{{ $t('common.community') }}</th>
                    <th @click="sortBy('amount')">{{ $t('common.amount') }}</th>
                    <th></th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="payout in payouts">
                    <td>{{ payout.id }}</td>
                    <td style="text-align: center;">
                      <div class="tag"
                           :class="'tag-' + payout.status"
                           style="margin-bottom: 0px;">{{ $t('status.' + payout.status) }}</div>
                    </td>
                    <td class="font-14px">{{ formatDate(payout.created_at) }}</td>
                    <td style="line-height: 20px;">
                      <template v-if="payout.user">
                        {{ payout.user.name }}<br/>
                        <span class="font-14px">{{ payout.user.email }}</span>
                      </template>
                    </td>
                    <td>
                      <a class="font-14px"
                         :href="'/'+payout.community.url"
                         target="_blank">{{ payout.community.id }} - {{ payout.community.name }}</a>
                    </td>
                    <td>{{ formatAmountWithCurrency(payout.currency, payout.amount / 100) }}</td>
                    <td style="line-height: 20px;">
                      <a class="font-14px"
                         :href="payout.stripe_transfer_link"
                         target="_blank"
                         v-if="payout.stripe_transfer_link">{{ payout.stripe_transfer_id }}</a>
                      <span v-else>–</span>
                    </td>
                    <td>
                      <template v-if="payout.status === 'pending'">
                        <button
                            class="button is-medium"
                            @click="pay(payout)"
                            style="float: right;"
                            :disabled="payingPayout"><font-awesome-icon icon="fa fa-check" class="link-icon" /></button>

                        <button
                            class="button is-medium"
                            v-if="payout.status === 'pending'"
                            @click="refuse(payout)"
                            style="float: right;"
                            :disabled="payingPayout"><font-awesome-icon icon="fa fa-times" class="link-icon" /></button>
                      </template>
                    </td>
                  </tr>
                </tbody>
              </table>

              <div v-if="pagination.last_page > 1" style="padding-top: 20px; border-top: 1px solid #ddd;">
                <div style="display: flex; gap: 10px; justify-content: space-between;">

                  <div style="display: flex; gap: 10px;">
                    <select class="input tab-content-select" v-model="perPage" @change="applyFilter" style="width: 50px;">
                      <option>10</option>
                      <option>25</option>
                      <option>50</option>
                    </select>
                    <div class="font-14px" style="width: 200px; padding: 7px 0px;">Items per page</div>
                  </div>

                  <div style="display: flex; gap: 10px;">
                    <div class="page-button" v-if="currentPage !== pagination.first_page"
                         @click="goToPage(currentPage - 1)">{{ $t('common.previous') }}</div>

                    <div class="page-button"
                         @click="goToPage(pagination.first_page)"
                         :class="currentPage === pagination.first_page ? 'current-page' : ''">{{ pagination.first_page }}</div>

                    <div class="page-button" v-if="(currentPage - pagination.first_page) > 2">...</div>

                    <template v-for="i in pagination.last_page">
                      <div class="page-button"
                           v-if="i >= 2 && i <= currentPage + 1 && i >= currentPage - 1 && i < pagination.last_page" :key="i"
                          :class="currentPage === i ? 'current-page' : ''"
                          @click="goToPage(i)">{{ i }}</div>
                    </template>

                    <div class="page-button" v-if="(pagination.last_page - currentPage) > 2">...</div>

                    <div class="page-button"
                         @click="goToPage(pagination.last_page)"
                         :class="currentPage === pagination.last_page ? 'current-page' : ''">{{ pagination.last_page }}</div>

                    <div class="page-button" v-if="currentPage !== pagination.last_page"
                         @click="goToPage(currentPage + 1)">{{ $t('common.next') }}</div>
                  </div>
                </div>

              </div>
            </div>

            <div v-else style="text-align: center">
              No payouts found
            </div>
          </template>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import formatAmountWithCurrency from "../../mixins/util";
import moment from "moment";
import Loading from 'vue-loading-overlay'

export default {
  name: 'SuperAdminPayouts',
  mixins: [formatAmountWithCurrency],
  components: {
    Loading
  },
  data() {
    return {
      loadingPayouts: true,
      payingPayout: false,
      filter: {
        search: null,
        date_start: null,
        date_end: null,
        user: null,
        status: null,
        community: null
      },
      sort: {
        column: null,
        direction: null
      }
    }
  },
  async created()
  {
    await this.$store.dispatch('SUPER_ADMIN_GET_PAYOUTS', {
      filter: this.filter,
      sort: this.sort
    });
    this.loading = false;
    this.loadingPayouts = false;
  },
  computed: {
    loading: {
      get() {
        return this.$store.state.superadmin.loading;
      },
      set(v) {
        this.$store.commit('setSuperAdminLoading', v);
      }
    },

    perPage: {
      get() {
        return this.pagination.per_page;
      },
      set(v) {
        this.$store.commit('setSuperAdminPayoutsPaginationPerPage', v);
      }
    },

    currentPage: {
      get() {
        return this.pagination.current_page;
      },
      set(v) {
        this.$store.commit('setSuperAdminPayoutsPaginationCurrentPage', v);
      }
    },

    hasFilter() {
      return this.filter['search']
          || this.filter['date_start']
          || this.filter['date_end']
          || this.filter['user']
          || this.filter['status']
          || this.filter['community'];
    },

    payouts() {
      return this.$store.state.superadmin.payouts.data;
    },

    totals() {
      return this.$store.state.superadmin.payouts.totals;
    },

    users() {

      return this.$store.state.superadmin.payouts.filters.users;
    },

    communities() {
      return this.$store.state.superadmin.payouts.filters.communities;
    },

    pagination() {
      return this.$store.state.superadmin.payouts.pagination;
    },

    selectedPayout: {
      get() {
        return this.$store.state.superadmin.selected_payout;
      },
      set(v) {
        this.$store.commit('setSuperAdminSelectedPayout', v);
      }
    },

    verificationCode() {
      return this.$store.state.communitycenter.verificationCode;
    }
  },
  methods: {
    formatDate(date) {
      return moment(date).format("L") + ' ' + moment(date).format("LT")
    },

    async applyFilter() {
      this.loadingPayouts = true;
      await this.$store.dispatch('SUPER_ADMIN_GET_PAYOUTS', {
        filter: this.filter,
        sort: this.sort
      });
      this.loadingPayouts = false;
    },

    clearFilter() {
      this.filter = {
        search: null,
        date_start: null,
        date_end: null,
        user: null,
        status: null,
        community: null
      };

      this.applyFilter();
    },

    filterPayoutsByStatus(status) {
      this.filter.status = status;
      this.applyFilter();
    },

    async pay(payout) {
      if (!this.verificationCode) {
        await this.$store.dispatch('TWO_FACTOR_AUTHENTICATION_INIT');
      }

      this.selectedPayout = payout
      this.$store.commit('showModal', {
        type: 'CompletePayout',
        transparent: true
      });
    },

    async refuse(payout)
    {
      this.payingPayout = true;
      await this.$store.dispatch('SUPER_ADMIN_PAYOUT_REFUSE', payout.id);
      this.payingPayout = false;
    },

    goToPage(page) {
      this.currentPage = page;
      this.applyFilter();
    },

    sortBy(column) {
      this.sort.column = column;
      if (this.sort.direction === 'asc') {
        this.sort.direction = 'desc'
      } else {
        this.sort.direction = 'asc';
      }
      this.applyFilter();
    }
  }
}
</script>

<style scoped>
  .counter {
    display: flex;
    flex-direction: column;
    row-gap: 3px;
    border-radius: 5px;
    background-color: #fff;
    padding: 15px 20px;
    text-align: center;
    width: 240px;

    .counter-amount {
      font-size: 22px
    }

    .counter-text {
      font-size: 14px
    }
  }

  table > tbody > tr > td {
    vertical-align: middle
  }

  .tag {
    background: #000;
    border-radius: 4px;
    padding: 5px 10px;
    width: auto;
    color: white;
  }
  .tag-pending {
    background: #fdac20;
  }
  .tag-complete {
    background: #4cd35d;
  }
  .tag-failed {
    background: #ff0000;
  }
  .page-button:hover {
    cursor: pointer;
    color: #1e90ff
  }
  .page-button {
    padding: 5px 10px;
  }
  .page-button.current-page {
    font-weight: bold;
    color: #1e90ff
  }
  .counter.filtered {
    background-color: #9198FF;
    color: white;

    .counter-amount a {
      color: white
    }
  }
  .fa-spin {
    -webkit-animation: fa-spin 2s infinite linear;
    animation: fa-spin 2s infinite linear;
  }

  @keyframes fa-spin {
    from {
      -webkit-transform: rotate(0deg);
      transform: rotate(0deg);
    }
    to {
      -webkit-transform: rotate(359deg);
      transform: rotate(359deg);
    }
  }
</style>