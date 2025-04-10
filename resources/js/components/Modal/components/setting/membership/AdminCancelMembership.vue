<template>
  <div class="wrapper">
    <div class="tab-content-title mb-05">
      {{ $t('community.members.setting-modal.cancel-membership-admin.title') }}
    </div>

    <div class="mt1">
      <p>{{ $t('community.members.setting-modal.cancel-membership-admin.are-you-sure') }}</p>
    </div>

    <div class="mt1">
      <div>
        <button
            class="button is-medium mt1 community-blue-btn is-fullwidth"
            @click="cancel()"
            :disabled="processing">
          {{ $t('common.cancel') }}
        </button>
      </div>
      <div>
        <button
            class="button is-medium mt1 community-btn is-fullwidth"
            @click="confirm"
            :class="buttonClass">
          {{ $t('common.yes') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CancelMembershipAdmin',
  data() {
    return {
      processing: false
    }
  },
  computed: {
    /**
     * Returns member
     */
    member() {
      return this.$store.state.member.data;
    },

    /**
     * Returns button status class
     */
    buttonClass() {
      return this.processing ? ' is-loading' : '';
    },
  },
  methods: {
    cancel() {
      this.$store.commit('setModalExtraData', 'membership');
    },

    async confirm() {
      this.processing = true;
      await this.$store.dispatch('LEAVE_FROM_COMMUNITY');
      this.$store.commit('setModalExtraData', 'membership-cancelled');
      this.processing = false;
    },
  }
}
</script>

<style scoped>

</style>
