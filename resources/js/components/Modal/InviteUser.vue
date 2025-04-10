<template>
	<div class="inner-modal-container">

        <div class="user-photo-container">
            <div class="user-photo-avatar">
                <img v-if="photo" class="user-photo-avatar-img" :src="photo">
                <img v-else src='/assets/img/default.png' class='user-photo-avatar-img' />
            </div>
        </div>

        <div class="mt1 font-weight-600 font-20px">
            {{ $t('community.members.setting-modal.admin-settings.invite-signup-modal.title').replace('#sender_name#', getMemberName(inviteReferMember)) }}
        </div>

        <div class="mt-05 font-16px">
            {{ $t('community.members.setting-modal.admin-settings.invite-signup-modal.desc-1').replace('#community_name#', community.name) }}
        </div>

        <div class="flex mt1">
            <div class="flex-1">
                <p class="text-left input-label">{{ $t('common.first-name') }}</p>
                <input
                    class="input"
                    :placeholder="$t('common.first-name')"
                    v-model="firstname"
                    autofocus
                />
            </div>
        </div>

        <div class="flex mt1">
            <div class="flex-1">
                <p class="text-left input-label">{{ $t('common.last-name') }}</p>
                <input
                    class="input"
                    :placeholder="$t('common.last-name')"
                    v-model="lastname"
                />
            </div>
        </div>

        <div class="flex mt1">
          <div class="flex-1">
            <p class="text-left input-label">{{ $t('common.country') }}</p>
            <SelectCountry :country="country" @selected-country="updateCountry"/>
          </div>
        </div>

        <div class="flex mt1">
            <div class="flex-1">
                <p class="text-left input-label">{{ $t('common.email') }}</p>
                <input
                    class="input"
                    :placeholder="$t('common.email')"
                    v-model="email"
                />
            </div>
        </div>

        <div class="flex mt1">
            <div class="flex-1">
                <p class="text-left input-label">{{ $t('common.password') }}</p>
                <input
                    class="input"
                    :placeholder="$t('common.password')"
                    type="password"
                    v-model="password"
                />
            </div>
        </div>

        <button
            class="button is-medium w100 community-blue-btn text-uppercase mt2"
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
</template>

<script>

import getMemberName from "../../mixins/util";
import countries from "../../data/countries";
import SelectCountry from "../Forms/SelectCountry.vue";

export default {
	name: 'InviteUser',
  components: {SelectCountry},
    mixins: [
        getMemberName
    ],
    data() {
        return {
            countries: [],
            termLink: null,
            privacyLink: null
        }
    },
    mounted() {
        this.countries = countries;
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
            if (this.firstname !== '' &&
                this.lastname !== '' &&
                this.email !== '' &&
                this.validEmail &&
                this.country !== '' &&
                this.password !== '') {
                disabledConfirm = false;
            }

            return disabledConfirm;
        },

        firstname: {
          get() {
            return this.user.firstname;
          },
          set(v) {
            this.$store.commit('setUserProp', { key: 'firstname', v });
          }
        },

        lastname: {
          get() {
            return this.user.lastname;
          },
          set(v) {
            this.$store.commit('setUserProp', { key: 'lastname', v });
          }
        },

        email: {
          get() {
            return this.user.email;
          },
          set(v) {
            this.$store.commit('setUserProp', { key: 'email', v });
          }
        },

        country: {
          get() {
            return this.user.country;
          },
          set(v) {
            this.$store.commit('setUserProp', { key: 'country', v });
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
         * Get | Set password
         */
        password: {
            get () {
                return this.user.password;
            },
            set (v) {
                this.$store.commit('setUserProp', {
                    key: 'password',
                    v
                });
            }
        },

        /**
         * Returns refer member
         */
        inviteReferMember ()
        {
            return this.$store.state.communitycenter.inviteReferMember;
        },

        /**
         * Returns user photo
         */
        photo ()
        {
            return this.inviteReferMember ? this.inviteReferMember.photo : null;
        },
    },
	methods: {
    updateCountry(country) {
      this.country = country;
    },

        async doSignup()
        {
            await this.$store.dispatch('SIGN_UP', {
                firstname: this.firstname,
                lastname: this.lastname,
                email: this.email,
                country: this.country,
                password: this.password,
                referMemberId: this.inviteReferMember ? this.inviteReferMember.id : null,
                communityId: this.community ? this.community.id : null
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

    .user-photo-container {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 10px;
    }

    .user-photo-avatar {
        border: 5px solid #eee;
        border-radius: 50%;
        width: 110px;
        height: 110px;
        padding: 5px;
        position: relative;
    }

    .user-photo-avatar-img {
        border-radius: 50%;
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
</style>
