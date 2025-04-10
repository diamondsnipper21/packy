<template>
	<div class="inner-modal-container">
        <img src="/assets/logo/packie-logo.png" class="home-logo"/>

        <p class="title is-4 mt1 font-weight-600">
            {{ $t('login.title') }}
        </p>

        <div class="flex mt1">
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
                    @keyup.enter="doLogin"
                />
            </div>
        </div>

        <div class="flex mt1">
            <!-- Password -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('common.password') }}
                </p>
                <input
                    class="input"
                    :placeholder="$t('common.password')"
                    type="password"
                    v-model="password"
                    @keyup.enter="doLogin"
                />
            </div>
        </div>

        <p class="text-left input-label mt1 forgot-password-link" @click="showForgotPassword">
            {{ $t('forgot-password.desc') }}
        </p>

        <button
            class="button is-medium w100 community-blue-btn text-uppercase mt2"
            :class="button"
            @click="doLogin"
            :disabled="disabledConfirm"
        >{{ $t('login.btn') }}</button>

        <div class="mt1 flex align-items-center jc">
            <div class="mr-05">
                {{ $t('login.desc') }}
            </div>
            <div class="action-link" @click="showSignup">
                {{ $t('signup.btn') }}
            </div>
        </div>
	</div>
</template>

<script>
export default {
	name: 'Login',
    data () {
        return {
            processing: false,
            email: '',
            password: '',
        };
    },
    computed: {
        /**
         * Returns save button status
         */
        disabledConfirm ()
        {
            let disabledConfirm = true;
            if (this.email !== '' && this.validEmail && this.password !== '') {
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
            if (reg.test(this.email) == false) 
            {
                return false;
            }

            return true;
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
        async doLogin()
        {
            this.processing = true;

            if (!this.disabledConfirm) {
                await new Promise((resolve)=>setTimeout(() => { resolve(); }, 1500));

                // For only 'INCUBATEUR' start process
                let params = this.$route.path.split("/");
                let incubateurStart = false;
                if (typeof params[1] !== 'undefined' && params[1].toUpperCase() === 'INCUBATEUR' && typeof params[2] !== 'undefined' && params[2] === 'start') {
                    incubateurStart = true;
                }

                await this.$store.dispatch('LOGIN', {
                    email: this.email,
                    password: this.password,
                    incubateurStart
                });
            }

            this.processing = false;
        },

        /**
         * Show signup modal
         */
        showSignup ()
        {
            this.$store.commit('hideModal');

            setTimeout(() => {
                this.$store.commit('resetUser');
                this.$store.commit('showModal', {
                    type: 'Signup',
                    transparent: true
                });
            }, 500);
        },

        /**
         * Show forgot password
         */
        showForgotPassword ()
        {
            this.$store.commit('hideModal');

            setTimeout(() => {
                this.$store.commit('resetUser');
                this.$store.commit('showModal', {
                    type: 'ForgotPassword',
                    transparent: true
                });
            }, 500);
        },
	}
}
</script>

<style scoped>
    .home-logo {
        height: 40px;
    }

    .action-link {
        cursor: pointer;
        color: #7957d5;
    }

    .action-link:hover {
        text-decoration: underline;
    }

    .forgot-password-link {
        color: #9198FF;
        cursor: pointer;
    }

    .forgot-password-link:hover {
        color: #7957d5;
        text-decoration: underline;
    }
</style>
