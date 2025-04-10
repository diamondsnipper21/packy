<template>
	<div class="inner-modal-container">
        <SignupElements />

        <div class="p-0-5">
            <button
                class="button is-medium w100 community-blue-btn text-uppercase mt1"
                @click="doSignup"
                :disabled="disabledConfirm"
            >{{ $t('signup.btn') }}</button>

            <div class="mt1 font-16px" v-html="$t('signup.terms-desc', { termLink: termLink, privacyLink: privacyLink })"></div>

            <div class="mt2 font-18px font-weight-600">
                {{ $t('signup.desc') }}
                <span class="log-in-link" @click="showLogin">
                    {{ $t('login.btn') }}
                </span>
            </div>
        </div>
	</div>
</template>

<script>

import SignupElements from "../General/Signup/SignupElements.vue";

export default {
	name: 'Signup',
    components: {
        SignupElements
    },
    data() {
        return {
            termLink: null,
            privacyLink: null
        }
    },
    mounted() {
        this.termLink = '<a href="/legal/terms" target="_blank">' + this.$t('signup.terms-conditions') + '</a>';
        this.privacyLink = '<a href="/legal/privacy" target="_blank">' + this.$t('signup.privacy-policy') + '</a>';
    },
    computed: {
        /**
         * Returns community
         */
        community ()
        {
            return this.$store.state.community.data;
        },

        /**
         * Returns user
         */
        user ()
        {
            return this.$store.state.auth.user;
        },

        /**
         * Returns save button status
         */
        disabledConfirm ()
        {
            let disabledConfirm = true;
            if (this.user.firstname !== '' && this.user.lastname !== '' && this.user.email !== '' && this.user.country !== '' && this.validEmail && this.user.password !== '') {
                disabledConfirm = false;
            }

            return disabledConfirm;
        },

        /**
         * Prevent special characters from inputing
         */
        validEmail ()
        {
            const reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
            if (reg.test(this.user.email) == false) 
            {
                return false;
            }

            return true;
        },

        /**
         * Get extra action
         */
        extraAction ()
        {
            return this.$store.state.modal.extraAction;
        },
    },
	methods: {
        async doSignup()
        {
            await this.$store.dispatch('SIGN_UP', {
                firstname: this.user.firstname,
                lastname: this.user.lastname,
                country: this.user.country,
                email: this.user.email,
                password: this.user.password,
                communityId: this.community ? this.community.id : null,
                extraAction: this.extraAction
            });
        },

        /**
         * Show login modal
         */
        showLogin ()
        {
            this.$store.commit('hideModal');

            setTimeout(() => {
                this.$store.commit('resetUser');
                this.$store.commit('showModal', {
                    type: 'Login',
                    transparent: true
                });
            }, 500);
        },
	}
}
</script>

<style scoped>
    .log-in-link {
        margin-left: 10px;
        cursor: pointer;
        color: #7957d5;
    }
    .log-in-link:hover {
        text-decoration: underline;
    }
</style>
