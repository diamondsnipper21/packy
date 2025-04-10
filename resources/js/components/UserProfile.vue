<template>
    <div class="container p2">
        <div class="box">
            <div class="columns">
                <div class="column is-one-third" style="padding-right: 0;">
                    <div class="user-profile-avatar-container">
                        <div class="user-profile-avatar">
                            <img class="user-profile-avatar-img" :src="getMemberGravatar(currentMember)">
                        </div>
                    </div>

                    <div class="user-profile-name">
                        {{ getMemberName(currentMember) }}
                    </div>

                    <div class="user-profile-tag">
                        {{ tag }}
                    </div>
                </div>

                <div class="column is-two-thirds" style="padding-left: 0; flex: auto;">
                    <div class="user-profile-summary-info p1">
                        <div class="mt-05">
                            <div v-if="currentMember.online">
                                <font-awesome-icon icon="fa fa-circle" class="mr1 online-now" />
                                {{ $t('community.members.online-now') }}
                            </div>
                            <div v-else>
                                <font-awesome-icon icon="fa fa-clock" class="mr1" />
                                {{ $t('community.members.active') }} {{ getDiffTimeFromNow(currentMember.last_activity) }}
                            </div>
                        </div>

                        <div class="mt-05">
                            <font-awesome-icon icon="fa fa-location" class="mr1" />
                            {{ getCountryTxt(country) }}
                        </div>

                        <div class="mt-05">
                            <font-awesome-icon icon="fa fa-globe" class="mr1" />
                            {{ getLangTxt(language) }}
                        </div>
                    </div>

                    <div class="p1">
                        <div class="flex mt1">
                            <!-- Email -->
                            <div class="flex-1">
                                <p class="user-profile-label">
                                    {{ $t('common.email') }}
                                </p>
                                <div class="text-left">
                                    {{ email }}
                                </div>
                            </div>
                        </div>

                        <div class="flex mt1">
                            <!-- link -->
                            <div class="flex-1">
                                <p class="user-profile-label">
                                    {{ $t('signup.link') }}
                                </p>
                                <div class="text-left">
                                    {{ link }}
                                </div>
                            </div>
                        </div>

                        <div class="flex mt1">
                            <!-- Content -->
                            <div class="flex-1">
                                <p class="user-profile-label">{{ $t('signup.content') }}</p>
                                <div class="text-left user-profile-content">
                                    {{ content }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import moment from 'moment'
import getMemberName from "../mixins/util";
import getMemberTag from "../mixins/util";
import getMemberGravatar from "../mixins/util";

import countries from '../data/countries';
import languages from '../data/languages';

export default {
    name: 'UserProfile',
    mixins: [
        getMemberName,
        getMemberTag,
        getMemberGravatar
    ],
    data () {
        return {
            countries: [],
            languages: []
        };
    },
    mounted() {
        this.countries = countries;
        this.languages = languages;
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
         * Returns current member
         */
        currentMember ()
        {
            return this.$store.state.member.data;
        },

        /**
         * Returns user email
         */
        email ()
        {
            return this.user.email;
        },

        /**
         * Returns user photo
         */
        photo ()
        {
            return this.currentMember.photo;
        },

        /**
         * Returns user tag
         */
        tag ()
        {
            return this.getMemberTag(this.currentMember);
        },

        /**
         * Returns user link
         */
        link ()
        {
            return this.currentMember.link;
        },

        /**
         * Returns user country
         */
        country ()
        {
            return this.currentMember.country;
        },

        /**
         * Returns user language
         */
        language ()
        {
            return this.user.language;
        },

        /**
         * Returns user content
         */
        content ()
        {
            return this.currentMember.content;
        },
    },
    methods: {
        /**
         * Calculate diff time from now
         */
        getDiffTimeFromNow (date)
        {
            return moment(date).locale(this.$i18n.locale).fromNow();
        },

        /**
         * Get country text
         */
        getCountryTxt (value)
        {
            let countryTxt = '-';
            let selectedCountries = this.countries.filter(el => el.value === value);
            if (selectedCountries.length === 1) {
                countryTxt = selectedCountries[0].text;
            }

            return countryTxt;
        },

        /**
         * Get language text
         */
        getLangTxt (value)
        {
            let langTxt = '-';
            let selectedLangs = this.languages.filter(el => el.value === value);
            if (selectedLangs.length === 1) {
                langTxt = selectedLangs[0].label;
            }

            return langTxt;
        },
    }
}
</script>

<style scoped>
    .user-profile-avatar-container {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
    }

    .user-profile-avatar {
        border: 5px solid #eee;
        border-radius: 50%;
        width: 220px;
        height: 220px;
        padding: 5px;
        position: relative;
    }

    .user-profile-avatar-img {
        border-radius: 50%;
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .user-profile-name {
        font-size: 22px;
        font-weight: 600;
        padding: 10px;
        text-align: center;
    }

    .user-profile-tag {
        color: rgb(67, 87, 173);
        white-space: nowrap;
        font-weight: bold;
        font-style: normal;
        font-size: 16px;
        text-align: center;
    }

    .user-profile-summary-info {
        border-bottom: 1px solid #ddd;
        margin-top: 10px;
        padding-bottom: 15px;
    }

    .online-now {
        color: rgb(0, 158, 93);
    }

    .user-profile-label {
        text-align: left;
        font-size: 15px;
        color: #8a8a8a;
    }

    .user-profile-content {
        white-space: pre-wrap;
    }

</style>
