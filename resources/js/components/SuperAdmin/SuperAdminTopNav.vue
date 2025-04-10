<template>
    <nav class="navbar nav-container">
      <div class="container">
        <div class="navbar-brand-section">
          <div class="flex align-items-center">
            <img style="display: block; margin-left: auto; margin-right: auto; width: auto;height: 30px;" src="https://packie.io/assets/logo/packie-logo.png" alt="Packie logo"/>
          </div>
          <CommunityTopRightNav />
        </div>
      </div>
      <div class="tabs-container">
        <template v-for="tab in tabs">
          <p class="community-tab" :class="tabClass(tab)" @click="selectTab(tab)">{{ translate(tab) }}</p>
        </template>
      </div>
    </nav>
</template>

<script>
import CommunityTopRightNav from "./../CommunityTopRightNav.vue";

export default {
    name: 'SuperAdminTopNav',
    components: {CommunityTopRightNav},
    created ()
    {
      const params = this.$route.path.split("/");
      if (typeof params[1] !== 'undefined' && params[1] === 'super-admin' && typeof params[2] !== 'undefined') {
        if (this.tabs.includes(params[2])) {
          this.$store.commit('setSuperAdminTab', params[2]);
        }
      }
    },
    computed: {
        /**
         * Returns tabs
         */
        tabs() {
            return ['payouts', 'stats'];
        },

        /**
         * Return selected super admin tab
         */
        selectedTab() {
          let selectedTab = this.$store.state.superadmin.selectedTab;
          document.title = this.translate(selectedTab);

          return selectedTab;
        }
    },
    methods: {
      /**
       * Return class for selected or unselected tab
       */
      tabClass(tab) {
        return (tab === this.selectedTab)
            ? 'tab-selected'
            : '';
      },

      /**
       * Return translated value
       */
      translate(tab) {
        return this.$t(`community.super-admin-tabs.${tab}`);
      },

      /**
       * Select tab
       */
      selectTab(tab) {
        this.$router.push('/super-admin/' + tab).catch(() => {});
        this.$store.commit('setSuperAdminTab', tab);
      },
    }
}
</script>

<style scoped>
.nav-container {
  padding-top: 0px !important;
  padding-bottom: 0px !important;
}
.navbar-brand-section {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.tabs-container {
  display: flex;
  align-items: center;
  max-width: 1170px !important;
  margin: 0 auto;
  width: 100%;
  overflow: auto;
}
.community-tab {
  margin-right: 1em;
  cursor: pointer;
  color: #8a8a8a;
  padding-bottom: 0.5em;
  border-bottom: 1px solid transparent;
  display: flex;
  align-items: center;
  white-space: nowrap;
}
</style>
