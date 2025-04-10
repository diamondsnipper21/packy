<template>
    <div :id="id">
        <div class="columns" v-if="mentionedMemberInfo">
            <div class="column is-two-fifths mention-left-content">
                <div class="mention-leaderboard-avatar-container">
                    <div class="mention-leaderboard-avatar">
                        <img
                            class="mention-leaderboard-avatar-img" 
                            :src="getMemberGravatar(mentionedMemberInfo)" 
                        />
                    </div>
                </div>

                <div class="mention-leaderboard-level">
                    {{ $t('community.leaderboard.level') + ' ' + mentionedMemberInfo.level }}
                </div>
            </div>
            <div class="column is-three-fifths mention-right-content">
                <div class="w100">
                    <div class="mention-member-name">
                        {{ getMemberName(mentionedMemberInfo) }}
                    </div>

                    <div class="mention-member-tag">
                        {{ getMemberTag(mentionedMemberInfo) }}
                    </div>

                    <div class="mention-member-online">
                        <template v-if="mentionedMemberInfo.online">
                            <font-awesome-icon icon="fa fa-circle" class="mr-05 online-now" />
                            {{ $t('community.members.online-now') }}
                        </template>
                        <template v-else>
                            <font-awesome-icon icon="fa fa-clock" class="mr-05" />
                            {{ $t('community.members.active') }} {{ getDiffTimeFromNow(mentionedMemberInfo.last_activity) }}
                        </template>
                    </div>

                    <div class="mention-member-joined">
                        <font-awesome-icon icon="fa fa-calendar" class="mr-05" />
                        {{ $t('community.members.joined') }} {{ getMemberJoinedInfo(mentionedMemberInfo) }}
                    </div>

                    <div class="mention-member-location">
                        <font-awesome-icon icon="fa fa-location" class="mr-05" />
                        {{ getCountryTxt(mentionedMemberInfo.country) }}
                    </div>

                    <div class="mention-member-desc">
                        {{ mentionedMemberInfo.content }}
                    </div>
                </div>

                <div class="mention-tooltip-box-action" v-if="auth">
                    <button 
                        v-if="currentMember && currentMember.id !== mentionedMemberInfo.id" 
                        class="button is-medium community-blue-btn mr-05" 
                        @click="showChatModal(mentionedMemberInfo)"
                    >
                        {{ $t('community.members.chat') }} <font-awesome-icon icon="fa fa-comment" :class="'ml-05'" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import moment from 'moment';
import countries from '../../../data/countries';

import getMemberName from "../../../mixins/util";
import getMemberTag from "../../../mixins/util";
import getMemberGravatar from "../../../mixins/util";

export default {
    name: 'MentionTooltip',
    mixins: [
        getMemberName,
        getMemberTag,
        getMemberGravatar
    ],
    props: [
        'id'
    ],
    data() {
        return {
            countries: []
        };
    },
    created() {
        moment.locale(this.$i18n.locale);
        this.countries = countries;
    },
    computed: {
        /**
         * Return auth status
         */
        auth() {
            return this.$store.state.auth.loggedIn;
        },

        /**
         * Returns mentioned member info
         */
        mentionedMemberInfo ()
        {
            return this.$store.state.community.mentionedMemberInfo;
        },

        /**
         * Returns mentioned member level
         */
        level ()
        {
            return this.mentionedMemberInfo.level;
        },

        /**
         * Returns current member
         */
        currentMember ()
        {
            return this.$store.state.member.data;
        },

        /**
         * Returns user
         */
        user() {
            return this.$store.state.auth.user;
        },

        /**
         * Returns community
         */
        community() {
            return this.$store.state.community.data;
        },
    },
    methods: {
        /**
         * Calculate diff time from now
         */
        getDiffTimeFromNow(date) {
            return moment(date).locale(this.$i18n.locale).fromNow();
        },

        /**
         * Get member joined info
         */
        getMemberJoinedInfo(member) {
            return moment(member.created_at).format("MMM Do, YYYY");
        },

        /**
         * Get country text
         */
        getCountryTxt(value) {
            let countryTxt = '-';
            let selectedCountries = this.countries.filter(el => el.value === value);
            if (selectedCountries.length === 1) {
                countryTxt = selectedCountries[0].text;
            }

            return countryTxt;
        },

        showChatModal(member) {
            this.emitter.emit("closeMentionTooltip", {});

            setTimeout(() => {
                this.$store.dispatch('GET_CHAT_DETAIL', {
                    communityId: this.community.id,
                    fromId: this.user.id,
                    toId: member.user_id,
                    showDetail: true
                });
            }, 100);
        },
    }
}
</script>

<style scoped>
    .mention-left-content,
    .mention-right-content {
        padding: 7px;
    }

    .mention-right-content {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .mention-leaderboard-avatar-container {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 3px;
    }

    .mention-leaderboard-avatar {
        border: 5px solid #eee;
        border-radius: 50%;
        width: 120px;
        height: 120px;
        padding: 3px;
        position: relative;
    }

    .mention-leaderboard-avatar-img {
        border-radius: 50%;
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .mention-leaderboard-level {
        color: rgb(67, 87, 173);
        white-space: nowrap;
        font-weight: bold;
        font-style: normal;
        font-size: 13px;
        text-align: center;
    }

    .next-level-up-desc {
        color: rgb(144, 144, 144);
        padding-left: 5px;
        padding-right: 5px;
    }

    .next-level-up-icon {
        cursor: pointer;
        font-size: 15px;
    }

    .next-level-up-points {
        color: rgb(67, 87, 173);
        font-weight: bold;
    }

    .mention-member-name {
        font-size: 18px;
        font-weight: bold;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: left;
    }

    .mention-member-tag {
        font-size: 14px;
        font-weight: 500;
        color: rgb(144, 144, 144);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 5px;
        text-align: left;
    }

    .mention-member-desc {
        font-size: 13px;
        margin-top: 5px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: pre-wrap;
        text-align: left;
    }

    .mention-member-online,
    .mention-member-joined,
    .mention-member-location {
        padding-top: 3px;
        font-size: 14px;
        text-align: left;
    }

    .mention-tooltip-box-action {
        margin-top: 10px;
        display: flex;
        align-content: center;
        justify-content: flex-end;
    }

    @media only screen and (max-width: 600px)
    {
        .mention-left-content,
        .mention-right-content {
            padding: 5px;
        }

        .mention-leaderboard-avatar {
            border: 3px solid #eee;
            border-radius: 50%;
            width: 90px;
            height: 90px;
            padding: 3px;
            position: relative;
        }

        .mention-member-desc {
            display: none;
        }

        .mention-member-name {
            font-size: 15px;
            text-align: center;
        }

        .mention-member-tag {
            font-size: 13px;
            text-align: center;
        }

        .mention-member-online,
        .mention-member-joined,
        .mention-member-location {
            padding: 1px 3px;
        }

        .mention-tooltip-box-action {
            margin-top: 7px;
        }
    }
</style>
