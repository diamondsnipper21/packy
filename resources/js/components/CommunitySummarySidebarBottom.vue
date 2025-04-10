<template>
    <div class="box">
        <div class="font-weight-500">
            {{ $t('community.community.summary.leaderboard') }} ({{ this.$t('community.leaderboard.days', { count: 30 }) }})
        </div>

        <div class="leaderboard-content border-grey-bottom">
            <div
                v-for="member, key in monthlyRankedMembers"
                :key="key"
                class="leaderboard-item"
            >
                <div class="leaderboard-item-info">
                    <div class="leaderboard-item-rank-mark">
                        <div
                            class="leaderboard-item-rank"
                            :class="getRankClass(key)"
                        >
                            {{ key + 1 }}
                        </div>
                        <div
                            v-if="parseInt(key) < 3"
                            class="top-ranked-tail"
                            :class="getRankClass(key)"
                        ></div>
                    </div>
                        
                    <img :src="getMemberGravatar(member)" class="customer-avatar-photo size-34px" />
                    <div class="leaderboard-item-name">
                        {{ getMemberName(member) }}
                    </div>
                </div>
                <div class="leaderboard-item-count">
                    {{ member.monthlyPoints > 0 ? '+' : '' }} {{ member.monthlyPoints }}
                </div>
            </div>
        </div>

        <div class="leaderboard-all-link" @click="goToAllLeaderboards">
            {{ $t('community.community.summary.see-all-leaderboards') }}
        </div>
    </div>
</template>

<script>

import md5 from 'md5'
import { MemberAccess } from '../data/enums';
import getMemberName from "../mixins/util";
import getMemberGravatar from "../mixins/util";

export default {
    name: 'CommunitySummarySidebarBottom',
    mixins: [
        getMemberName,
        getMemberGravatar
    ],
    data() {
        return {
            MemberAccess
        };
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
         * Returns community privacy
         */
        privacy ()
        {
            return this.community.privacy;
        },

        /**
         * Returns member
         */
        member ()
        {
            return this.$store.state.member.data;
        },

        /**
         * Returns member existence
         */
        memberExist ()
        {
            return (typeof this.member.id !== 'undefined' && parseInt(this.member.id) > 0) ? true : false;
        },

        /**
         * Returns access of member
         */
        access ()
        {
            return this.memberExist ? this.member.access : null;
        },

        /**
         * Returns allowedMembers
         */
        allowedMembers ()
        {
            return this.$store.state.communitycenter.allowedMembers;
        },

        /**
         * Returns top monthly ranked members with points
         */
        monthlyRankedMembers ()
        {
            let rankedMembers = [];
            let members = JSON.parse(JSON.stringify(this.allowedMembers));
            if (members.length > 0) {
                members = members.sort((a, b) => {
                    if (a.monthlyPoints > b.monthlyPoints) {
                        return -1;
                    }
                });
                rankedMembers = members.slice(0, 10);
            }

            return rankedMembers;
        }
    },
    methods: {
        /**
         * Return rank class
         */
        getRankClass (key)
        {
            let getRankClass = '';
            let rank = parseInt(key);

            if ([0, 1, 2].includes(rank)) {
                getRankClass += 'top-ranked';
                if (rank === 0) {
                    getRankClass += ' first-rank';
                } else if (rank === 1) {
                    getRankClass += ' second-rank';
                } else if (rank === 2) {
                    getRankClass += ' third-rank';
                }
            }

            return getRankClass;
        },

        goToAllLeaderboards ()
        {
            let tab = 'rankings';
            if (this.privacy === 'private' && this.access !== MemberAccess.ALLOWED) {
                tab = 'about';
            }

            let path = '/' + this.community.url + '/' + tab;

            this.$router.push(path).catch(()=>{});
            this.$store.commit('setCommunityTab', tab);
        }
    }
}
</script>

<style scoped>
    .leaderboard-all-link {
        color: rgb(46, 110, 245);
        font-size: 14px;
        padding-top: 15px;
        text-align: center;
        cursor: pointer;
    }
    .leaderboard-all-link:hover {
        text-decoration: underline;
    }
    .leaderboard-item-name {
        max-width: 220px;
        max-height: 25px;
        overflow: hidden;
    }
</style>
