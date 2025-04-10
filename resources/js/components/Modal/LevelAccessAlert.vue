<template>
	<div class="inner-modal-container classroom-alert-modal">
        <div class="classroom-alert-modal-header">
            <img v-if="item.photo" :src="item.photo" class="classroom-alert-modal-img"/>
            <div v-if="accessLevel(item) > 0" class="cc-lock-section">
                <font-awesome-icon icon="fa fa-lock" class="access-locked-level-icon" />
                <div class="cc-item-card-title mt1">
                    {{ item.title }}
                </div>
                <div class="access-locked-level-desc">
                    {{ $t('community.classroom.unlock-at-level').replace("#level#", accessLevel(item)) }}
                </div>
            </div>
        </div>

        <div class="classroom-alert-modal-content">
            <div class="cc-item-card-desc">
                {{ item.content }}
            </div>

            <button class="button is-medium mt1 mb1 w100 community-blue-btn" @click="goToLeaderBoard">
                {{ $t('community.classroom.check-level-on-leaderboards') }}
            </button>
        </div>
    </div>
</template>

<script>

import { MemberAccess } from '../../data/enums';
export default {
	name: 'LevelAccessAlert',
    data () {
        return {
            MemberAccess,
            btnClass: ''
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
         * Returns community
         */
        community ()
        {
            return this.$store.state.community.data;
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
         * extra data of child modal
         */
        item ()
        {
            return this.$store.state.modal.extraData;
        },
	},
	methods: {
        /**
         * Return access level
         */
        accessLevel (item)
        {
            let accessLevel = 0;
            if (item.access_type === 'only_level') {
                if (parseInt(this.level) < parseInt(item.access_value)) {
                    accessLevel = parseInt(item.access_value);
                }
            }

            return accessLevel;
        },

        /**
         * Show login modal
         */
        showLogin ()
        {
            this.$store.commit('resetUser');
            this.$store.commit('showModal', {
                type: 'Login',
                transparent: true
            });
        },

        /**
         * Go to leader board page
         */
        goToLeaderBoard ()
        {
            if (this.auth) {
              if (this.access === MemberAccess.ALLOWED) {
                this.$store.commit('hideModal');

                let tab = 'rankings';
                let path = '/' + this.community.url + '/' + tab;

                this.$router.push(path).catch(()=>{});
                this.$store.commit('setCommunityTab', tab);
              } else if (this.access === MemberAccess.PENDING) {
                this.$store.commit('showModal', {
                  type: 'Pending',
                  transparent: true
                });
              } else {
                this.$store.commit('showModal', {
                  type: 'Join',
                  transparent: true
                });
              }
            } else {
                this.showLogin();
            }
        },
	}
}
</script>

<style scoped>
    .classroom-alert-modal {
        padding: 0px !important;
    }

    .classroom-alert-modal-header {
        border-radius: 10px 10px 0px 0px;
        background: #6b51cd;
        overflow: initial;
        position: relative;
        height: 250px;
    }

    .classroom-alert-modal-img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
        border-radius: 10px 10px 0px 0px;
    }

    .classroom-alert-modal-content {
        background: white;
        border-radius: 0px 0px 10px 10px;
        padding: 15px;
    }

    .cc-item-card-title {
        font-size: 24px;
    }

    .access-locked-level-desc {
        font-size: 17px;
    }

    @media only screen and (max-width: 600px)
    {
        .cc-item-card-title {
            font-size: 20px;
        }
        .access-locked-level-desc {
            font-size: 16px;
        }

        .classroom-alert-modal-content {
            padding: 10px;
        }
    }
</style>
