<template>
  <div class="container pl-0 pr-0">
    <loading v-if="loading" :active.sync="loading" :is-full-page="true" />

    <div class="columns" v-else>
      <div class="column">
        <div class="font-weight-700">
            {{ $t('community.members.setting-modal.admin-settings.stats-content.audience') }}
            <span class="font-14px">
                ({{ $t('community.members.setting-modal.admin-settings.stats-content.last-7-days') }})
            </span>
        </div>

        <div class="traffic-container">
          <div v-for="trafficItem, i in trafficItems" :key="trafficItem.key" class="box counter">
            <div class="counter-amount">
              <b>{{ trafficItem.value }}</b>
            </div>
            <div class="counter-text">
              {{ $t(`community.members.setting-modal.admin-settings.stats-content.${trafficItem.key}`) }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import moment from "moment";
import Loading from 'vue-loading-overlay'

export default {
  name: 'SuperAdminStats',
  components: {
    Loading
  },
  data() {
    return {

    }
  },
  async created()
  {
    await this.$store.dispatch('SUPER_ADMIN_GET_STATS', {
      url: 'incubateur'
    });
  },
  computed: {
    /**
     * Return selected super admin tab
     */
    loading() {
      return this.$store.state.superadmin.loading;
    },

    /**
     * Returns stats data
     */
    stats ()
    {
        return this.$store.state.superadmin.stats;
    },

    /**
     * Returns traffic data
     */
    traffic ()
    {
      return this.stats?.traffic || null;
    },

    /**
     * Returns count of visitors
     */
    visitorCnt ()
    {
      return this.traffic?.about_page_visitors || 0;
    },

    /**
     * Returns count of sign up visitors
     */
    signupCnt ()
    {
      return this.traffic?.signups || 0;
    },

    /**
     * Returns conversion rate
     */
    conversionRate ()
    {
      let conversionRate = 0;
      if (this.visitorCnt > 0) {
        conversionRate = parseFloat(this.signupCnt * 100 / this.visitorCnt);
      }

      return conversionRate;
    },

    /**
     * Returns traffic items
     */
    trafficItems ()
    {
      return [{
        key: "about_page_visitors",
        value: this.visitorCnt
      }, {
        key: "signups",
        value: this.signupCnt
      }, {
        key: "conversion_rate",
        value: this.conversionRate.toFixed(2) + '%'
      }];
    },
  },
  methods: {
  }
}
</script>

<style scoped>
  .traffic-container {
    display: flex; 
    gap: 15px;
    margin-top: 15px;
  }
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
</style>