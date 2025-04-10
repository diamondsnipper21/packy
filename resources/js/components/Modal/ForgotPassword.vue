<template>
	<div class="inner-modal-container">
        <img src="/assets/logo/packie-logo.png" class="home-logo"/>

        <p class="title is-4 mt1 font-weight-600">
            {{ $t('forgot-password.title') }}
        </p>

        <p class="mt1">
            {{ $t('forgot-password.desc-1') }}
        </p>

        <div class="flex mt2">
            <!-- Email -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('common.email') }}
                </p>
                <input
                    class="input"
                    :placeholder="$t('common.email')"
                    v-model="email"
                    autofocus
                />
            </div>
        </div>

        <button
            class="button is-medium w100 community-blue-btn text-uppercase mt2"
            :class="button"
            @click="emailMe"
            :disabled="disabledConfirm"
        >{{ $t('forgot-password.btn-1') }}</button>
	</div>
</template>

<script>
export default {
	name: 'ForgotPassword',
    data () {
        return {
            processing: false
        };
    },
    computed: {
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
            if (this.email !== '' && this.validEmail) {
                disabledConfirm = false;
            }

            return disabledConfirm;
        },

        /**
         * Get | Set email
         */
        email: {
            get () {
                return this.user.email;
            },
            set (v) {
                this.$store.commit('setUserProp', {
                    key: 'email',
                    v
                });
            }
        },

        /**
         * Prevent special characters from inputing
         */
        validEmail ()
        {
            const reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
            if (reg.test(this.email) == false) 
            {
                return false;
            }

            return true;
        },

        /**
         * Return url valid status
         */
        validUrl ()
        {
            return this.$store.state.auth.validUrl;
        },

        /**
         * Returns button status class
         */
        button ()
        {
            return this.processing ? ' is-loading' : '';
        },
    },
	methods: {
        async emailMe()
        {
            this.processing = true;

            await new Promise((resolve)=>setTimeout(() => { resolve(); }, 1500));

            await this.$store.dispatch('SEND_RESET_PASSWORD_EMAIL', {
                email: this.email,
                url: this.validUrl
            });

            this.$store.commit('hideModal');

            setTimeout(() => {
                this.$store.commit('showModal', {
                    type: 'CheckYourEmail',
                    transparent: true
                });
            }, 500);

            this.processing = false;
        }
	}
}
</script>

<style scoped>
    .home-logo {
        height: 40px;
    }
</style>
