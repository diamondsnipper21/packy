<template>
    <div class="inner-modal-container">

        <p class="new-community-title">
            {{ $t('community.community.create-temproray-modal.title') }}
        </p>

        <div class="new-community-desc">
            {{ $t('community.community.create-temproray-modal.desc') }}
        </div>

        <div class="flex mt1">
            <!-- Group Name -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.community.create-temproray-modal.group') }}
                </p>
                <input class="input" required :placeholder="$t('community.community.create-temproray-modal.group')"
                    v-model="name" ref="name" />
            </div>
        </div>

        <div class="flex mt1">
            <!-- Group topic -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.members.setting-modal.admin-settings.topic') }}
                </p>
                <input class="input" :placeholder="$t('community.members.setting-modal.admin-settings.topic')"
                    v-model="url" @keypress="validateUrl($event)" />
            </div>
        </div>

        <div class="flex mt1">
            <!-- Email -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.community.create-temproray-modal.email') }}
                </p>
                <input class="input" required :placeholder="$t('community.community.create-temproray-modal.email')"
                    v-model="email" />
            </div>
        </div>

        <div class="terms-section">
            {{ $t('community.community.create-temproray-modal.desc-2') }}
        </div>

        <button :class="btnClass" @click="saveTempCommunity" :disabled="disabledConfirm" class="mt2">{{
            $t('community.community.create-temproray-modal.button') }}</button>
    </div>
</template>

<script>

import moment from 'moment'
import validateUrl from "../../mixins/util";
export default {
    name: 'NewTempCommunity',
    mixins: [validateUrl],
    data() {
        return {
            btnClass: 'button is-medium community-blue-btn w100',
            selected: 1,
            cardNumber: ''
        }
    },
    computed: {
        /**
         * Returns community
         */
        community() {
            return this.$store.state.community.clone;
        },

        /**
         * Get | Set name
         */
        name: {
            get() {
                return this.community.name;
            },
            set(v) {
                this.$store.commit('setCommunityProperty', {
                    key: 'name',
                    v
                });
            }
        },

        /**
         * Get | Set url
         */
        url: {
            get() {
                return this.community.url;
            },
            set(v) {
                this.$store.commit('setCommunityProperty', {
                    key: 'url',
                    v
                });
            }
        },

        /**
         * Get | Set email
         */
        email: {
            get() {
                return this.community.email;
            },
            set(v) {
                this.$store.commit('setCommunityProperty', {
                    key: 'email',
                    v
                });
            }
        },

        /**
         * Prevent special characters from inputing
         */
        validEmail() {
            const reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
            if (reg.test(this.email) == false) {
                return false;
            }

            return true;
        },

        /**
         * Returns disabledConfirm
         */
        disabledConfirm() {
            let disabledConfirm = true;
            if (this.name !== null && this.name !== '' && this.url !== null && this.url !== '' && this.email !== null && this.email !== '' && this.validEmail) {
                disabledConfirm = false;
            }

            return disabledConfirm;
        },
    },
    methods: {

        /**
         * Add community
         */
        async saveTempCommunity() {
            await this.$store.dispatch('SAVE_TEMP_COMMUNITY');
        }
    }
}
</script>

<style scoped>
.new-community-title {
    font-size: 24px;
    font-weight: bold;
}

.terms-section {
    font-size: 14px;
    text-align: left;
    margin-top: 20px;
}
</style>
