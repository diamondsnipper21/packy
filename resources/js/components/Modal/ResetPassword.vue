<template>
	<div class="inner-modal-container">
        <img src="/assets/logo/packie-logo.png" class="home-logo"/>

        <p class="title is-4 mt1 font-weight-600">
            {{ $t('forgot-password.title-4') }}
        </p>

        <div class="flex mt2">
            <!-- New password -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('forgot-password.new-password') }}
                </p>
                <input
                    class="input"
                    :placeholder="$t('forgot-password.new-password')"
                    type="password"
                    v-model="newPassword"
                />
            </div>
        </div>

        <div class="flex mt1">
            <!-- Repeat password -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('forgot-password.repeat-password') }}
                </p>
                <input
                    class="input"
                    :placeholder="$t('forgot-password.repeat-password')"
                    type="password"
                    v-model="repeatPassword"
                />
            </div>
        </div>

        <button
            class="button is-medium w100 community-blue-btn text-uppercase mt2"
            :class="button"
            @click="resetPassword"
            :disabled="disabledConfirm"
        >{{ $t('forgot-password.btn-4') }}</button>
	</div>
</template>

<script>
export default {
	name: 'ResetPassword',
    data () {
        return {
            newPassword: '',
            repeatPassword: '',
            processing: false
        };
    },
    computed: {
        /**
         * extra data of child modal
         */
        extraData ()
        {
            return this.$store.state.modal.extraData;
        },

        /**
         * Returns save button status
         */
        disabledConfirm ()
        {
            let disabledConfirm = true;
            if (this.newPassword !== '' && this.newPassword === this.repeatPassword) {
                disabledConfirm = false;
            }

            return disabledConfirm;
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
        async resetPassword()
        {
            this.processing = true;

            await new Promise((resolve)=>setTimeout(() => { resolve(); }, 1500));

            await this.$store.dispatch('RESET_PASSWORD', {
                password: this.newPassword,
                userId: this.extraData
            });

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
