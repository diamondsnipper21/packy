<template>
    <div style="position: relative; min-height: 100vh;">
        <div class="cc-outer-container">

            <notifications position="bottom left" :close-on-click="true"/>

            <!-- modal component -->
            <modal v-if="showModal" />

            <!-- child modal component -->
            <child-modal v-if="showChildModal" />


            <loading v-if="loading" :active.sync="loading" :is-full-page="true" />
            <template v-else>
                <div v-if="validUrl">
                    <CommunityTopNav />

                    <div class="inner-main-container">
                        <router-view />
                    </div>
                </div>

                <div v-else-if="showProfile" class="container">
                    <div class="home-top-container">
                        <img src="/assets/logo/packie-logo.png" class="home-logo"/>

                        <CommunityTopRightNav />
                    </div>
                    <UserProfile />
                </div>

                <div v-else-if="showLegal" class="container">
                    <div class="home-top-container">
                        <img src="/assets/logo/packie-logo.png" class="home-logo"/>

                        <CommunityTopRightNav />
                    </div>
                    <LegalIndex />
                </div>

                <div v-else class="container">
                    <div class="home-top-container">
                        <img src="/assets/logo/packie-logo.png" class="home-logo"/>

                        <div v-if="!authStatus" class="flex align-items-center">
                            <div class="home-action-link" @click="showLogin">
                                {{ $t('home.login') }}
                            </div>
                            <div class="home-action-link" @click="showSignup">
                                {{ $t('home.signup') }}
                            </div>
                        </div>
                    </div>
                    <CommunityHome />
                </div>
            </template>
        </div>
        <!-- <CommunityFooter /> -->

        <div id="mention_menu_container">
            <div class="mention-menu" role="listbox"></div>
        </div>

        <MentionTooltip id="mention_tooltip_container" />
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/css/index.css'
import {notify} from "@kyvg/vue3-notification";

import CommunityTopNav from './CommunityTopNav'
import CommunityTopRightNav from './CommunityTopRightNav'
import CommunityHome from './CommunityHome'
import LegalIndex from './LegalIndex'
import UserProfile from './UserProfile'
import Modal from '../components/Modal/Modal'
import ChildModal from '../components/Modal/ChildModal'
import {MemberAccess, MemberRole} from '../data/enums';
import MentionTooltip from "./General/Elements/MentionTooltip.vue";

export default {
    name: 'CommunityDashboard',
    components: {
        CommunityTopNav,
        CommunityTopRightNav,
        CommunityHome,
        LegalIndex,
        UserProfile,
        Loading,
        Modal,
        ChildModal,
        MentionTooltip
    },
    data () {
        return {
            MemberAccess,
            auth: false,
            action: '',
            communityUrl: false,
            userId: false,
            showProfile: false,
            inviteToken: '',
            currentTarget: null,
            mentionTooltipRef: null,
            inModal: false,
            tooltipXAlign: 'left',
            tooltipYAlign: 'top',
            tooltipContainerId: '',
            showLegal: false,
        };
    },
    async created ()
    {
        this.$store.commit('communityLoading', true);

        const app = document.getElementById("app");
        this.auth = app.getAttribute("auth");
        this.action = app.getAttribute("action");
        this.communityUrl = app.getAttribute("communityUrl");
        this.userId = app.getAttribute("userId");
        this.userTag = app.getAttribute("userTag");
        this.inviteToken = app.getAttribute("inviteToken");

        this.$store.commit('setLoggedIn', this.auth);

        if (!this.auth && localStorage.getItem('communityUrl') !== null) {
            localStorage.removeItem('communityUrl');
            this.$store.commit('resetUser');
        }

        if (this.auth && this.userId) {
            await this.$store.dispatch('GET_USER', parseInt(this.userId));
        }

        const params = this.$route.path.split("/");

        if (this.action === 'view-profile' && this.userTag) {
            this.showProfile = true;
            await this.$store.dispatch('VIEW_PROFILE', this.userTag);
        } else if (this.action === 'invite') {
            localStorage.setItem('inviteToken', this.inviteToken);
            await this.$store.dispatch('GET_INVITE_COMMUNITY', {
                communityUrl: this.communityUrl,
                userId: parseInt(this.userId)
            });
        } else if (this.action === 'legal' && typeof params[1] !== 'undefined' && params[1] === this.action) {
            this.showLegal = true;
        } else {
            let validatedUrl = null;
            if (this.action === 'reset-password' || this.action === 'unsubscribe-digest' || this.action === 'chat') {
                if (this.communityUrl) {
                    validatedUrl = this.communityUrl;
                }
            } else if (this.url) {
                validatedUrl = this.url;
            }

            if (validatedUrl) {
                await this.$store.dispatch('VALIDATE_URL', validatedUrl);
            }

            if (this.validUrl) {
                await this.$store.dispatch('GET_COMMUNITY', this.validUrl);

                localStorage.setItem('communityUrl', this.validUrl);

                let tab = 'community';
                let postUrl = '';

                if (typeof params[1] !== 'undefined' && params[1] === this.validUrl && typeof params[2] !== 'undefined') {
                    if (this.tabs.includes(params[2])) {
                        tab = params[2];
                    } else {
                        postUrl = params[2];
                    }
                }

                if (this.privacy === 'private' && this.access !== MemberAccess.ALLOWED) {
                    tab = 'about';
                }

                let incubateurMemberExist = this.member?.incubateurMemberExist || false;
                // For only 'INCUBATEUR' community, if member has active subscription, then will be redirect to incubateur/start page
                if (this.validUrl.toUpperCase() === 'INCUBATEUR' && params[2] === 'start') {
                    if (incubateurMemberExist) {
                        tab = 'community';
                    } else {
                        tab = 'start';
                    }
                }

                let path = '/' + this.validUrl;
                if (tab !== 'community') {
                    path += '/' + tab;

                    if (tab === 'ressources') {
                        if (typeof params[3] !== 'undefined') {
                            path += '/' + params[3];
                            if (typeof params[4] !== 'undefined' && params[4] === 'lesson') {
                                path += '/' + params[4];
                                if (typeof params[5] !== 'undefined') {
                                    path += '/' + params[5];
                                }
                            }
                        }
                    }
                }

                if (postUrl !== '' && tab !== 'about' && tab !== 'start') {
                    await this.$store.dispatch('GET_COMMUNITY_POST', {
                        path: postUrl,
                        communityId: this.community.id
                    });
                } else {
                    this.$router.push(path).catch(()=>{});
                }

                if (this.authStatus) {
                    let pusher = new Pusher("1ca6ac28164f32071ec6", { cluster: "eu" });
                    let audio = new Audio('/assets/audio/cashier.mp3');

                    if (this.member.role === MemberRole.ADMIN || this.member.role === MemberRole.OWNER) {
                        let channel = pusher.subscribe(this.appName + "-community-" + this.community.id);
                        channel.bind("new-payment", (data) => {
                            audio.play();
                            notify({
                                title: this.$t('community.notifications.message.new-payment'),
                                text: data.message,
                                type: 'success'
                            });
                        });
                    }

                    if (this.user.id === 1) {
                        let channel = pusher.subscribe("user-" + this.user.id);
                        channel.bind("new-subscription", (data) => {
                            audio.play();
                            notify({
                                title: this.$t('community.notifications.message.new-subscription'),
                                text: data.message,
                                type: 'success'
                            });
                        });
                    }
                }

                this.$store.commit('setCommunityTab', tab);
            }

            if (this.action === 'reset-password') {
                this.$store.commit('showModal', {
                    type: 'ResetPassword',
                    extraData: this.userId,
                    transparent: true
                });
            }
        }

        this.$i18n.locale = 'fr';
        if (this.user && this.user.language) {
            this.$i18n.locale = this.user.language;
        } else if (localStorage.getItem('lang') !== null) {
            this.$i18n.locale = localStorage.getItem('lang');
        }

        if (localStorage.getItem('lang') === null) {
            localStorage.setItem('lang', this.$i18n.locale);
        }

        if (this.authStatus && this.access === MemberAccess.ALLOWED) {
            if (this.action === 'unsubscribe-digest') {
                this.$store.commit('showModal', {
                    type: 'CommunitySetting',
                    extraData: 'notifications',
                    transparent: true
                });
            } else if (this.action === 'chat') {
                await this.$store.dispatch('GET_CHAT_DETAIL', {
                    communityId: this.community.id,
                    fromId: this.user.id,
                    toId: this.userId,
                    showDetail: true
                });
            }
        }

        this.$store.commit('communityLoading', false);
    },
    mounted ()
    {
        window.addEventListener("mouseover", async event => {
            this.currentTarget = event.target;

            this.tooltipContainerId = 'mention_tooltip_container';

            const modalContainer = this.currentTarget.closest("#community_modal_container");

            this.inModal = false;
            if (modalContainer !== null) {
                this.tooltipContainerId = 'mention_tooltip_modal_container';
                this.inModal = true;
            }

            this.mentionTooltipRef = document.getElementById(this.tooltipContainerId);
            
            // mentioned member hover handler
            if (this.currentTarget.matches('.mentioned-name')) {
                await this.$store.dispatch('GET_MEMBER_SUMMARY', {
                    memberId: this.currentTarget.dataset.id,
                    communityId: this.community.id
                });

                const targetStyle = this.currentTarget.getBoundingClientRect();
                const targetTop = targetStyle.top;
                const targetBottom = targetStyle.bottom;
                const targetLeft = targetStyle.left;
                const targetRight = targetStyle.right;
                const targetHeight = targetStyle.height;
                const targetX = targetStyle.x;
                const targetY = targetStyle.y;

                if (this.mentionTooltipRef) {
                    setTimeout(() => {
                        if (targetY < window.innerHeight / 2) {
                            this.tooltipYAlign = 'top';
                        } else {
                            this.tooltipYAlign = 'bottom';
                        }

                        if (targetX < window.innerWidth / 2) {
                            this.tooltipXAlign = 'left';
                        } else {
                            this.tooltipXAlign = 'right';
                        }

                        if (this.inModal && modalContainer) {
                            const modalContainerStyle = modalContainer.getBoundingClientRect();
                            const modalTop = modalContainerStyle.top;
                            const modalBottom = modalContainerStyle.bottom;
                            const modalLeft = modalContainerStyle.left;
                            const modalRight = modalContainerStyle.right;

                            if (this.tooltipXAlign === 'left') {
                                this.left = targetLeft - modalLeft;
                                this.right = undefined;
                            } else {
                                this.left = undefined;
                                this.right = modalRight - targetRight;
                            }

                            if (this.tooltipYAlign === 'top') {
                                this.top = targetTop - modalTop + targetHeight;
                                this.bottom = undefined;
                            } else {
                                this.top = undefined;
                                this.bottom = modalBottom - targetBottom + targetHeight;
                            }
                        } else {
                            if (this.tooltipXAlign === 'left') {
                                this.left = window.scrollX + targetLeft;
                                this.right = undefined;
                            } else {
                                this.left = undefined;
                                this.right = window.innerWidth - (window.scrollX + targetRight + 20);
                            }

                            if (this.tooltipYAlign === 'top') {
                                this.top = window.scrollY + targetTop + targetHeight;
                                this.bottom = undefined;
                            } else {
                                this.top = undefined;
                                this.bottom = document.body.offsetHeight - (window.scrollY + targetBottom - targetHeight);
                            }
                        }

                        this.renderMentionTooltip();
                    }, 900);
                }
            } else {
                let closeTooltip = true;
                if (this.currentTarget.matches('#' + this.tooltipContainerId) || this.currentTarget.closest('#' + this.tooltipContainerId) !== null) {
                    closeTooltip = false;
                }

                if (closeTooltip) {
                    this.closeMentionTooltip();
                }
            }
        });

        window.addEventListener("mousedown", (event) => {
            if (!event.target.matches('#mention_menu_container') && event.target.closest("#mention_menu_container") === null) {
                setTimeout(() => {
                    this.emitter.emit("closeMentionMenu", {});
                }, 500)
            }
        });

        this.emitter.on("closeMentionTooltip", ev => {
            this.closeMentionTooltip();
        });

        const mentionTooltipRef = document.getElementById('mention_tooltip_container');
        if (mentionTooltipRef) {
            mentionTooltipRef.hidden = true;
        }
    },
    computed: {
        appName ()
        {
          return this.$store.state.community.app_name;
        },

        /**
         * Returns user
         */
        user ()
        {
            return this.$store.state.auth.user;
        },

        /**
         * Return auth status
         */
        authStatus ()
        {
            return this.$store.state.auth.loggedIn;
        },

        /**
         * Return community url
         */
        url ()
        {
            let url = this.$route.params.url;
            if ((typeof url === 'undefined' || !url) && localStorage.getItem('communityUrl') !== null) {
                url = localStorage.getItem('communityUrl');
            }

            return url;
        },

        /**
         * Return url valid status
         */
        validUrl ()
        {
            return this.$store.state.auth.validUrl;
        },

        /**
         * Loading spinner
         * @bool
         */
        loading ()
        {
            return this.$store.state.communitycenter.loading;
        },

        /**
         * Show modal
         * @bool
         */
        showModal ()
        {
            return this.$store.state.modal.show;
        },

        /**
         * Show child modal
         */
        showChildModal ()
        {
            return this.$store.state.modal.childShow;
        },

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
         * Returns tabs
         */
        tabs ()
        {
            let tabs = [];
            if (this.memberExist) {
                tabs = ['community', 'ressources', 'calendar', 'member', 'rankings', 'about', 'users', 'start'];
            } else {
                tabs = ['community', 'ressources', 'calendar', 'member', 'about', 'start'];
            }

            return tabs;
        },

        /**
         * Returns access of member
         */
        access ()
        {
            return this.memberExist ? this.member.access : null;
        },

        /**
         * type for modal
         */
        modalType ()
        {
            return this.$store.state.modal.type;
        },
    },
    methods: {
        /**
         * Close mention tooltip
         */
        closeMentionTooltip() {
            setTimeout(() => {

                if (!this.currentTarget.matches('.mentioned-name') && !this.currentTarget.matches('#' + this.tooltipContainerId) && this.currentTarget.closest('#' + this.tooltipContainerId) === null) {
                    this.left = undefined;
                    this.right = undefined;
                    this.top = undefined;
                    this.bottom = undefined;
                }
                    

                this.renderMentionTooltip();
            }, 0)
        },

        /**
         * Render mention tooltip
         */
        renderMentionTooltip() {
            if (this.mentionTooltipRef) {
                if (!this.currentTarget.matches('.mentioned-name') && !this.currentTarget.matches('#' + this.tooltipContainerId) && this.currentTarget.closest('#' + this.tooltipContainerId) === null) {
                    this.left = undefined;
                    this.right = undefined;
                    this.top = undefined;
                    this.bottom = undefined;
                }

                if (this.top === undefined && this.bottom === undefined) {
                    this.mentionTooltipRef.hidden = true;
                    return
                }

                if (this.left !== undefined) {
                    this.mentionTooltipRef.style.left = this.left + 'px';
                    this.mentionTooltipRef.style.right = 'auto';
                } else if (this.right !== undefined) {
                    this.mentionTooltipRef.style.left = 'auto';
                    this.mentionTooltipRef.style.right = this.right + 'px';
                }


                if (this.top !== undefined) {
                    this.mentionTooltipRef.style.top = this.top + 'px';
                    this.mentionTooltipRef.style.bottom = 'auto';
                } else if (this.bottom !== undefined) {
                    this.mentionTooltipRef.style.top = 'auto';
                    this.mentionTooltipRef.style.bottom = this.bottom + 'px';
                }

                this.mentionTooltipRef.hidden = false;
            }
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
         * Show signup modal
         */
        showSignup ()
        {
            this.$store.commit('resetUser');
            this.$store.commit('showModal', {
                type: 'Signup',
                transparent: true
            });
        },
    }
}
</script>

<style scoped>
    .cc-outer-container {
        min-height: 100vh;
    }
    .inner-main-container {
        padding: 2em 0 5em 0;
    }
    .home-top-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 2em;
    }

    @media only screen and (max-width: 600px)
    {
        .home-top-container {
            padding: 2em 1em;
        }
    }
</style>
