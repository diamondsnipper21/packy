<template>
    <div class="container pl-0 pr-0">
        <!-- No community Exist -->
        <div v-if="typeof community.name === 'undefined' || ![CommunityStatus.PUBLISHED, CommunityStatus.PENDING].includes(community.status)" class="empty-section">
        {{ $t('community.community.empty-community-placeholder') }}
    </div>

    <div v-else>
        <div class="box" style="margin-top: 130px!important; margin-left: 10px; margin-right: 10px;">
            <div class="columns">
                <div class="position-absolute w100">
                    <div class="progressbar-container">
                        <div class="progressbar-content" :style="'width: '+ progressBarWidth + '%'"></div>
                    </div>
                    <div class="leaderboard-neededpoints-info">
                        <div class="leaderboard-neededpoints-info-sec">
                            <div class="leaderboard-neededpoints-info-text">
                                {{ nextNeededPoints }} {{ $t('community.leaderboard.level-up') }}
                            </div>
                            <img src="/assets/icons/treasure-chest.png" class="fa-beat">
                        </div>
                    </div>
                    <img
                        v-if="member"
                        class="leaderboard-avatar-img" 
                        :src="getMemberGravatar(member)"
                    />

                    <div class="leaderboard-level-mark">{{ level }}</div>
                    <div class="leaderboard-member-name">{{ getMemberName(member) }}</div>
                </div>

                <div class="column is-one-third"></div>
                <div class="column is-one-third">
                    <LeaderboardLevels :user-level="level" :levels="leftPoints"/>
                </div>
                <div class="column is-one-third">
                    <LeaderboardLevels :user-level="level" :levels="rightPoints"/>
                </div>
            </div>
        </div>

        <div class="columns">
            <div class="column">
                <div class="last-updated-info">
                    {{ $t('community.leaderboard.last-updated') }}: {{ lastUpdated }}
                </div>
            </div>
        </div>

        <div class="columns" v-if="allowedMembers.length > 0">
            <div class="column is-one-third">
                <div class="box">
                    <CompactLeaderboard
                        :title="$t('community.leaderboard.all-time')"
                        :ranking="rankedMembers"
                        :index="'points'"
                    />
                </div>
            </div>

            <div class="column is-one-third">
                <div class="box">
                    <CompactLeaderboard
                        :title="$t('community.leaderboard.month')"
                        :ranking="monthlyRankedMembers"
                        :index="'monthlyPoints'"
                    />
                </div>
            </div>

            <div class="column is-one-third">
                <div class="box">
                    <CompactLeaderboard
                        :title="$t('community.leaderboard.week')"
                        :ranking="weeklyRankedMembers"
                        :index="'weeklyPoints'"
                    />
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<script>
import md5 from 'md5'
import moment from 'moment'

import getMemberName from "../mixins/util";
import getMemberGravatar from "../mixins/util";
import { CommunityStatus } from '../data/enums';
import CompactLeaderboard from "./General/CompactLeaderboard.vue";
import LeaderboardLevels from "./General/LeaderboardLevels.vue";

export default {
    name: 'CommunityLeaderboard',
    mixins: [
        getMemberName,
        getMemberGravatar
    ],
    components: {
        CompactLeaderboard,
        LeaderboardLevels
    },
    data () {
        return {
            CommunityStatus,
        };
    },
    mounted() {
        if (this.auth) {
            this.loadLevels()
        } else {
            window.location.href = '/' + this.community.url + '/about';
        }
    },
    computed: {
        /**
         * Return auth status
         */
        auth ()
        {
            return this.$store.state.auth.loggedIn;
        },

        /**
         * Returns community
         */
        community ()
        {
            return this.$store.state.community.data;
        },
        
        /**
         * Returns member
         */
        member ()
        {
            return this.$store.state.member.data;
        },

        levels ()
        {
            return this.$store.state.community.levels;
        },

        leftPoints() {
            return this.$store.state.community.levels.slice(0, 5);
        },
        
        rightPoints() {
            return this.$store.state.community.levels.slice(-5);
        },

        /**
         * Returns allowedMembers
         */
        allowedMembers ()
        {
            return this.$store.state.communitycenter.allowedMembers;
        },

        /**
         * Returns total members count
         */
        totalMembersCount ()
        {
            return this.allowedMembers.length;
        },

        /**
         * Returns top ranked members with points
         */
        rankedMembers ()
        {
            let rankedMembers = [];
            let members = JSON.parse(JSON.stringify(this.allowedMembers));
            if (members.length > 0) {
                members = members.sort((a, b) => {
                    if (a.points > b.points) {
                        return -1;
                    }
                });
                rankedMembers = members.slice(0, 10);
            }

            return rankedMembers;
        },

        /**
         * Returns top weekly ranked members with points
         */
        weeklyRankedMembers ()
        {
            let rankedMembers = [];
            let members = JSON.parse(JSON.stringify(this.allowedMembers));
            if (members.length > 0) {
                members = members.sort((a, b) => {
                    if (a.weeklyPoints > b.weeklyPoints) {
                        return -1;
                    }
                });
                rankedMembers = members.slice(0, 10);
            }

            return rankedMembers;
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
        },

        /**
         * Returns member's count according to level
         */
        memberCntForLevel ()
        {
            let memberCntForLevel = [];
            let neededPointsKeys = Object.keys(this.neededPoints);

            if (neededPointsKeys.length > 0) {
                neededPointsKeys.map((key, index) => {
                    memberCntForLevel[key] = 0;
                });
            }

            if (this.allowedMembers.length > 0) {
                this.allowedMembers.map((member, index) => {
                    if (typeof memberCntForLevel['level_' + member.level] === 'undefined') {
                        memberCntForLevel['level_' + member.level] = 0;
                    }

                    memberCntForLevel['level_' + member.level] ++;
                });
            }

            return memberCntForLevel;
        },

        /**
         * Returns leaderboard
         */
        leaderboard ()
        {
            return this.$store.state.communitycenter.leaderboard;
        },

        /**
         * Returns member existence
         */
        memberExist ()
        {
            return (typeof this.member.id !== 'undefined' && parseInt(this.member.id) > 0) ? true : false;
        },

        /**
         * Returns leaderboard level
         */
        level ()
        {
            let level = 0;
            if (this.memberExist) {
                level = this.member.level;
            }
            
            return level;
        },

        /**
         * Returns needed points for level up
         */
        neededPoints ()
        {
            return this.leaderboard.neededPoints;
        },

        /**
         * Returns needed points for level up
         */
        nextNeededPoints ()
        {
            let nextLevel = parseInt(this.level) + 1;
            return this.neededPoints['level_' + nextLevel];
        },

        /**
         * Returns left points
         */
        neededPointsCnt ()
        {
            var count = 0;
            for(var prop in this.neededPoints) {
                if(this.neededPoints.hasOwnProperty(prop))
                    ++ count;
            }

            return count;
        },

        /**
         * Returns last updated
         */
        lastUpdated ()
        {
            return moment().format("MMM Do YYYY, h:mm a");
        },

        progressBarWidth ()
        {
            let total = this.member.points + this.nextNeededPoints;

            return total ? (100 * this.member.points / total) : 0;
        }
    },
    methods: {
        loadLevels() {
            this.$store.dispatch('GET_REWARDS_LEVELS', {
                communityId: this.community.id,
            });
        },

        /**
         * Show points information
         */
        showPointsInfo ()
        {
            this.$store.commit('showModal', {
                type: 'PointsInfo',
                transparent: true
            });
        },

        /**
         * Return leaderboard name
         */
        getLeaderboardName (leaderboard)
        {
            let name = '';
            if (typeof leaderboard.firstname !== 'undefined') {
                name = leaderboard.firstname;
                if (typeof leaderboard.lastname !== 'undefined') {
                    name += ' ' + leaderboard.lastname;
                }
            }

            return name;
        },

        /**
         * Return leaderboard photo
         */
        getLeaderboardPhoto (leaderboard)
        {
            let photo = 'https://www.gravatar.com/avatar/?s=48&d=identicon';
            if (leaderboard.photo !== null) {
                photo = leaderboard.photo;
            } else if (leaderboard.customer && leaderboard.customer.email !== null) {
                photo = 'https://www.gravatar.com/avatar/' + md5(leaderboard.customer.email) + '?s=48&d=identicon';
            }

            return photo;
        }
    }
}
</script>

<style scoped>
    .leaderboard-avatar-img {
        border-radius: 50%;
        width: 100%;
        height: 100%;
        object-fit: contain;
        background-color: rgb(244, 245, 248);
        z-index: 1; 
        position: relative; 
        width: 200px; 
        height: 200px; 
        margin-top: -150px; 
        border: 4px solid white;
    }
    .leaderboard-level-mark {
        position: absolute;
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #7957d5;
        color: #fff;
        border-radius: 50%;
        z-index: 1;
        top: -37px;
        left: 175px;
        font-weight: 700;
        font-size: 20px;
    }
    .last-updated-info {
        font-size: 13px;
        color: rgb(144, 144, 144);
        font-style: italic;
    }

    .progressbar-container {
        position: absolute; 
        background: #ddd; 
        height: 5px; 
        top: -20px; 
        right: 40px; 
        width: calc(100% - 230px);
    }

    .progressbar-content {
        background: #7957d5; 
        height: 5px;
    }

    .leaderboard-member-name {
        position: absolute; 
        top: -55px; 
        left: 225px; 
        font-weight: 700; 
        font-size: 22px;
    }

    .leaderboard-neededpoints-info {
        position: absolute;
        right: 80px; 
        top: -40px;
    }

    .leaderboard-neededpoints-info-sec {
        position: absolute;
        width: 40px;
    }

    .leaderboard-neededpoints-info .fa-beat {
        position: absolute;
        top: -5px; 
        left: 20px; 
        width: 40px;
    }

    .leaderboard-neededpoints-info-text {
        position: absolute;
        top: 0px; 
        right: 30px; 
        font-size: 12px; 
        color: rgb(144, 144, 144);
        white-space: nowrap;
    }

    @media only screen and (max-width: 600px)
    {
        .leaderboard-avatar-img {
            width: 100px; 
            height: 100px; 
            margin-top: -150px; 
            border: 3px solid white;
        }
        .leaderboard-level-mark {
            width: 28px;
            height: 28px;
            top: -29px;
            left: 80px;
            font-weight: 700;
            font-size: 16px;
        }

        .progressbar-container {
            top: -15px; 
            right: 32px;
            width: calc(100% - 140px);
        }

        .leaderboard-member-name {
            top: -35px; 
            left: 115px; 
            font-weight: 700; 
            font-size: 16px;
        }

        .leaderboard-neededpoints-info {
            top: -34px; 
        }

        .leaderboard-neededpoints-info .fa-beat {
            top: 0px; 
            left: 20px;
        }

        .leaderboard-neededpoints-info-text {
            top: 25px;
        }
    }
</style>
