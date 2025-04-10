<template>
	<div class="complete-profile-modal-container">
        <p class="title is-4 font-weight-600">
            {{ $t('signup.complete-title') }}
        </p>

        <div class="signup-desc mt1">
            {{ $t('signup.complete-desc-1') }}
        </div>
        <div class="signup-desc">
            {{ $t('signup.complete-desc-2') }}
        </div>

        <div v-if="photo" class="mt-05">
            <div class="user-photo-container">
                <div class="user-photo-avatar">
                    <img class="user-photo-avatar-img" :src="photo">
                    <div class="remove-setting-img">
                        <font-awesome-icon icon="fa fa-trash" class="mtba mr-05" @click.stop="removeUserPhoto" />
                    </div>
                </div>
            </div>
            <div class="mt-05 text-center font-14px">
                {{ $t('community.members.setting-modal.change-profile-photo') }}
            </div>
        </div>
        <div v-else class="mt-05">
            <Dropzone filetype="user_photo" :selfobj="self" />

            <div class="mt-05 text-center font-14px">
                {{ uploadDesc }}
            </div>
        </div>

        <div class="flex mt-05">
            <!-- Content -->
            <div class="flex-1">
                <p class="text-left input-label">{{ $t('signup.add-your-bio') }}</p>
                <textarea
                    class="textarea"
                    :placeholder="$t('signup.add-your-bio')"
                    v-model="bio"
                    rows="3"
                />
            </div>
        </div>

        <button
            class="button is-medium w100 community-blue-btn text-uppercase mt2"
            :class="button"
            @click="completeProfile"
            :disabled="disabledConfirm"
        >{{ $t('signup.complete') }}</button>
	</div>
</template>

<script>

import Dropzone from "../../components/General/Dropzone";
import countries from './../../data/countries';
import getUserTag from "../../mixins/util";

export default {
	name: 'CompleteProfile',
    components: {
        Dropzone
    },
    mixins: [
        getUserTag
    ],
    data () {
        return {
            self: null,
            countries: [],
            processing: false,
            uploadDesc: this.$t('signup.upload-photo')
        };
    },
    created ()
    {
        this.self = this;
        this.countries = countries;
    },
    watch: {
        '$store.state.communitycenter.uploading': function() {
            if (this.$store.state.communitycenter.uploading) {
                this.uploadDesc = this.$t('common.uploading');
            } else {
                this.uploadDesc = this.$t('signup.upload-photo');
            }
        },
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
         * Returns user photo
         */
        photo ()
        {
            return this.user ? this.user.photo : null;
        },

        /**
         * Get | Set tag
         */
        tag: {
            get () {
                return this.getUserTag(this.user);
            },
            set (v) {
                this.$store.commit('setUserProp', {
                    key: 'tag',
                    v
                });
            }
        },

        /**
         * Get | Set link
         */
        link: {
            get () {
                return this.user ? this.user.link : null;
            },
            set (v) {
                this.$store.commit('setUserProp', {
                    key: 'link',
                    v
                });
            }
        },

        /**
         * Get | Set country
         */
        country: {
            get () {
                return this.user ? this.user.country : 'FR';
            },
            set (v) {
                this.$store.commit('setUserProp', {
                    key: 'country',
                    v
                });
            }
        },

        /**
         * Get | Set content
         */
        bio: {
            get () {
                return this.user ? this.user.bio : null;
            },
            set (v) {
                this.$store.commit('setUserProp', {
                    key: 'content',
                    v
                });
            }
        },

        /**
         * Returns save button status
         */
        disabledConfirm ()
        {
            let disabledConfirm = true;
            if (this.photo || this.bio) {
                disabledConfirm = false;
            }

            return disabledConfirm;
        },

        /**
         * Get extra action
         */
        extraAction ()
        {
            return this.$store.state.modal.extraAction;
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
        removeUserPhoto ()
        {
            this.$store.commit('setUserProp', {
                key: 'photo',
                v: null
            });
        },

        async completeProfile()
        {
            this.processing = true;

            await this.$store.dispatch('COMPLETE_PROFILE');

            if (this.extraAction) {
                await this.$store.dispatch(this.extraAction);
            }

            this.processing = false;

            this.$store.commit('hideModal');
        }
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

    .complete-profile-modal-container {
        padding: 1em 2em;
        height: 100%;
        overflow: auto;
    }

    .complete-profile-modal-container::-webkit-scrollbar {
        background-color: #f4f5f8;
        width: 5px;
    }
    
    .complete-profile-modal-container::-webkit-scrollbar-thumb {
        background-color: #c0c0c0;
        border: 0.25em solid #c0c0c0;
        border-radius: 1em;
    }

    @media only screen and (max-width: 600px)
    {
        .complete-profile-modal-container {
            padding: 0.5em 1em;
        }

        .signup-desc {
            font-size: 14px;
        }
    }
</style>
