<template>
	<div class="inner-modal-container">
        <img src="/assets/logo/packie-logo.png" class="home-logo"/>

        <p class="title is-4 mt1 font-weight-600">
            {{ $t('forgot-password.title-2') }}
        </p>

        <p class="mt1">
            {{ $t('forgot-password.desc-2') }}
        </p>

        <button
            class="button is-medium w100 community-blue-btn text-uppercase mt2"
            @click="backToLogin"
        >{{ $t('forgot-password.btn-2') }}</button>
	</div>
</template>

<script>
export default {
	name: 'CheckYourEmail',
    computed: {
        /**
         * Returns button status class
         */
        button ()
        {
            return this.processing ? ' is-loading' : '';
        },
    },
	methods: {
        backToLogin()
        {
            this.$store.commit('hideModal');

            setTimeout(() => {
                this.$store.commit('resetUser');
                this.$store.commit('showModal', {
                    type: 'Login',
                    transparent: true
                });
            }, 500);
        }
	}
}
</script>

<style scoped>
    .home-logo {
        height: 40px;
    }
</style>
