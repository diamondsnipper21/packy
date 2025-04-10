<template>
  <div class="wrapper">
    <div class="tab-content-title mb-05">
      {{ $t('community.members.setting-modal.membership-cancelled.title') }}
    </div>

    <div class="mt1">
      <p>{{ $t('community.members.setting-modal.membership-cancelled.description') }}</p>
      <ul class="mt1">
        <li>- {{ $t('community.members.setting-modal.membership-cancelled.consequence-1') }}</li>
        <li>- {{ $t('community.members.setting-modal.membership-cancelled.consequence-2') }}</li>
      </ul>
    </div>

    <div class="flex mt1">
      <button class="button is-medium mt1 community-blue-btn is-fullwidth" @click="backToMembership()">
        {{ $t('community.members.setting-modal.membership-cancelled.buttons.got-it') }}
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MembershipCancelled',
  computed: {},
  methods: {
    backToMembership() {
      this.$store.commit('setModalExtraData', 'membership');
    },
  }
}
</script>

<style scoped>

</style>
