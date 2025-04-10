<template>
    <div style="position: relative; min-height: 100vh;">
        <div class="cc-outer-container">
          <notifications position="bottom left" :close-on-click="true"/>
          <modal v-if="showModal" />
          <div>
            <SuperAdminTopNav />
            <div class="inner-main-container">
              <router-view />
            </div>
          </div>
        </div>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/css/index.css'
import SuperAdminTopNav from "../SuperAdmin/SuperAdminTopNav.vue";
import Modal from '../../components/Modal/Modal'

export default {
    name: 'SuperAdminDashboard',
    components: {
      SuperAdminTopNav,
      Loading,
      Modal
    },
    data() {
        return {
            auth: false
        };
    },
    async created()
    {
        this.$i18n.locale = 'fr';

        const app = document.getElementById("app-super-admin");
        this.auth = app.getAttribute("auth");
        this.$store.commit('setLoggedIn', this.auth);

        await this.$store.dispatch('SUPER_ADMIN_GET_DATA');
    },
    computed: {
      loading() {
        return this.$store.state.superadmin.loading;
      },
      showModal() {
        return this.$store.state.modal.show;
      },
    },
    methods: {
        //
    }
}
</script>

<style scoped>
    .cc-outer-container {
        min-height: 100vh;
    }
    .inner-main-container {
      padding: 2em 0 5em 0;
    }
</style>
