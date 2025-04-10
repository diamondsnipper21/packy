<template>
    <div class="inner-modal-container form">

        <div v-if="photo" class="mt1">
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

        <div v-else class="mt1">
            <Dropzone filetype="user_photo" :selfobj="self" />
            <div class="mt-05 text-center font-14px">
                {{ uploadDesc }}
            </div>
        </div>

        <div class="flex mt1">
            <!-- First name -->
            <div class="mr-05 flex-1">
                <p class="text-left input-label">
                    {{ $t('common.first-name') }}
                </p>
                <input class="input" :placeholder="$t('common.first-name')" v-model="firstname" autofocus />
            </div>

            <!-- Last name -->
            <div class="ml-05 flex-1">
                <p class="text-left input-label">
                    {{ $t('common.last-name') }}
                </p>
                <input class="input" :placeholder="$t('common.last-name')" v-model="lastname" />
            </div>
        </div>

        <div class="flex mt1">
            <!-- Email -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('common.email') }}
                </p>
                <input class="input" :placeholder="$t('common.email')" v-model="email" />
            </div>
        </div>

        <div class="flex mt2">
            <!-- Tag -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('signup.tag') }}
                </p>
                <div class="input-group input">
                    <span class="input-prepend">@</span>
                    <input class="input-content" :placeholder="$t('signup.tag')" v-model="tag" />
                </div>
            </div>
        </div>

        <div class="flex mt1">
            <!-- link -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('signup.link') }}
                </p>
                <input class="input" :placeholder="$t('signup.link')" v-model="link" />
            </div>
        </div>

        <div class="flex mt1">
            <!-- country -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('signup.country') }}
                </p>
                <select v-model="country" class="input">
                    <option disabled v-bind:value="null">{{ $t('signup.country') }}</option>
                    <option value="FR">France</option>
                    <option value="BE">Belgium</option>
                    <option value="CH">Switzerland</option>
                    <option value="GB">United Kingdom</option>
                    <option value="IE">Ireland</option>
                    <option value="US">USA</option>
                    <option disabled v-bind:value="null">------</option>
                    <option v-for="item in countries" :value="item.value">
                        {{ item.text }}
                    </option>
                </select>
            </div>
        </div>

        <div class="flex mt1">
            <!-- Language -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('general.language.label') }}
                </p>
                <select v-model="language" class="input">
                    <option v-for="lang in languages" :value="lang.value">
                        {{ lang.label }}
                    </option>
                </select>
            </div>
        </div>

        <div class="flex mt1">
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.calendar.timezone') }}
                </p>

                <select v-model="timezone" class="input">
                    <option disabled v-bind:value="null">{{ $t('community.calendar.timezone') }}</option>
                    <option v-for="item in timezones" :value="item.key">
                        {{ item.val }}
                    </option>
                </select>
            </div>
        </div>

        <div class="flex mt1">
            <!-- Content -->
            <div class="flex-1">
                <p class="text-left input-label">{{ $t('signup.add-your-bio') }}</p>
                <textarea class="textarea" :placeholder="$t('signup.add-your-bio')" v-model="bio" rows="5" @input="inputContentHandler" />
                <p class="remained-length-message">{{ $tc('common.characters-left', remainedContentLength, {count: remainedContentLength}) }}</p>
            </div>
        </div>

        <div class="submit-button mt1">
            <button
                class="button is-medium community-blue-btn text-uppercase"
                :class="button"
                @click="updateProfile"
                :disabled="disabledConfirm">{{ $t('common.save') }}</button>
        </div>
    </div>
</template>

<script>

import Dropzone from "../../../../components/General/Dropzone";
import countries from './../../../../data/countries';
import languages from './../../../../data/languages';
import zones from './../../../../data/timezones';
import getUserTag from "../../../../mixins/util";

export default {
    name: 'ProfileSetting',
    components: {
        Dropzone
    },
    mixins: [
        getUserTag
    ],
    data() {
        return {
            self: null,
            processing: false,
            countries: [],
            languages: [],
            timezones: [],
            langs: [{
                value: 'en',
                label: 'English',
                icon: '/assets/img/gb.svg'
            }, {
                value: 'fr',
                label: 'Français',
                icon: '/assets/img/fr.svg'
            }],

            uploadDesc: this.$t('signup.upload-photo'),
            maxContentLength: 256,
        };
    },
    created() {
        this.self = this;
    },
    mounted() {
        this.countries = countries;
        this.languages = languages;
        this.timezones = zones;
    },
    watch: {
        '$store.state.communitycenter.uploading': function () {
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
        user() {
            return this.$store.state.auth.user;
        },

        /**
         * Returns member
         */
        member() {
            return this.$store.state.member.data;
        },

        /**
         * Get | Set user language
         */
        language: {
            get() {
                return this.user && this.user.language ? this.user.language : 'fr';
            },
            set(v) {
                this.$store.commit('setUserProp', {
                    key: 'language',
                    v
                });
            }
        },

        /**
         * Get | Set user timezone
         */
        timezone: {
            get() {
                return this.user && this.user.timezone ? this.user.timezone : 'Europe/Paris';
            },
            set(v) {
                this.$store.commit('setUserProp', {
                    key: 'timezone',
                    v
                });
            }
        },

        /**
         * Get | Set firstname
         */
        firstname: {
            get() {
                return this.user.firstname;
            },
            set(v) {
                this.$store.commit('setUserProp', {
                    key: 'firstname',
                    v
                });
            }
        },

        /**
         * Get | Set lastname
         */
        lastname: {
            get() {
                return this.user.lastname;
            },
            set(v) {
                this.$store.commit('setUserProp', {
                    key: 'lastname',
                    v
                });
            }
        },

        /**
         * Get | Set email
         */
        email: {
            get() {
                return this.user.email;
            },
            set(v) {
                this.$store.commit('setUserProp', {
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
         * Get | Set password
         */
        password: {
            get() {
                return this.user.password;
            },
            set(v) {
                this.$store.commit('setUserProp', {
                    key: 'password',
                    v
                });
            }
        },

        /**
         * Returns user photo
         */
        photo() {
            return this.user ? this.user.photo : null;
        },

        /**
         * Get | Set tag
         */
        tag: {
            get() {
                return this.getUserTag(this.user).replace(/^@/, '');
            },
            set(v) {
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
            get() {
                return this.user ? this.user.link : null;
            },
            set(v) {
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
            get() {
                return this.user && this.user.country ? this.user.country : 'FR';
            },
            set(v) {
                this.$store.commit('setUserProp', {
                    key: 'country',
                    v
                });
            }
        },

        /**
         * Get | Set bio
         */
        bio: {
            get() {
                return this.user ? this.user.bio : null;
            },
            set(v) {
                this.$store.commit('setUserProp', {
                    key: 'bio',
                    v
                });
            }
        },

        /**
         * Returns button status class
         */
        button() {
            return this.processing ? ' is-loading' : '';
        },

        /**
         * Returns save button status
         */
        disabledConfirm() {
            let disabledConfirm = true;
            if (this.firstname !== '' && this.lastname !== '' && this.email !== '' && this.validEmail) {
                disabledConfirm = false;
            }

            return disabledConfirm;
        },

        /**
         * Returns length of remained bio
         */
        remainedContentLength ()
        {
            let remainedContentLength = this.maxContentLength;
            if (this.bio) {
                remainedContentLength = this.maxContentLength - this.bio.length;
            }
            
            return remainedContentLength;
        },
    },
    methods: {
        removeUserPhoto() {
            this.$store.commit('setUserProp', {
                key: 'photo',
                v: null
            });
        },

        inputContentHandler()
        {
            if (this.bio.length >= this.maxContentLength) {
                this.bio = this.bio.substring(0, this.maxContentLength);
            }
        },

        async updateProfile() {
            this.processing = true;

            await new Promise((resolve) => setTimeout(() => { resolve(); }, 1000));
            await this.$store.dispatch('UPDATE_PROFILE');

            localStorage.setItem('lang', this.language);
            if (this.$i18n) {
              this.$i18n.locale = this.language;
            }

            window.dispatchEvent(new CustomEvent('lang-localstorage-changed', {
                detail: {
                    storage: localStorage.getItem('lang')
                }
            }));

            this.processing = false;
        },
    }
}
</script>

<style scoped>
.home-logo {
    height: 40px;
}

.log-in-link {
    margin-left: 10px;
    cursor: pointer;
    color: #7957d5;
}

.log-in-link:hover {
    text-decoration: underline;
}
</style>
