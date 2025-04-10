<template>
    <div class="container pl-0 pr-0">
        <!-- No community Exist -->
        <div v-if="typeof community.name === 'undefined' || ![CommunityStatus.PUBLISHED, CommunityStatus.PENDING].includes(community.status)"
            class="empty-section">
            {{ $t('community.community.empty-community-placeholder') }}
        </div>
        <div v-else class="columns">
            <div class="column is-two-thirds left-column-for-desktop">
                <div class="box">
                    <div class="about-name">
                        {{ community.name }}
                    </div>

                    <template v-if="mediaLoading">
                        <div class="media-loading-section">
                            <div class="media-loading"></div>
                        </div>
                    </template>
                    <template v-else>
                        <div 
                            v-if="selectedMedia && selectedMediaType === 'video'"
                            class="community-video-container"
                            :class="selectedMediaPath.includes('youtube') || selectedMediaPath.includes('vimeo') ? 'iframe-container' : ''"
                        >
                            <iframe
                                v-if="selectedMediaPath.includes('youtube')"
                                :src="'https://www.youtube.com/embed/' + selectedMediaPath.replace('youtube-', '')"
                                width="100%"
                                height="100%"
                                frameborder="0"
                            ></iframe>

                            <iframe
                                v-else-if="selectedMediaPath.includes('vimeo')"
                                :src="'https://player.vimeo.com/video/' + selectedMediaPath.replace('vimeo-', '') + '?autoplay=0&loop=false&controls=true'"
                                width="100%"
                                height="100%"
                                frameborder="0"
                                allow="autoplay; fullscreen; picture-in-picture"
                                allowFullScreen
                            ></iframe>

                            <video v-else :key="selectedMediaPath" controls>
                                <source :src="selectedMediaPath" type="video/mp4" />
                            </video>
                        </div>
                        <img v-else-if="selectedMedia && selectedMediaType === 'image'" :src="selectedMediaPath" class="about-photo-img" @click="selectImage" />
                        <div v-else-if="isManager(role)" @click="showAddMediaConfirm" class="add-media-section">
                            {{ $t('common.click-to-add-image-video') }}
                        </div>
                    </template>

                    <template v-if="medias.length > 0">
                        <div class="about-medias-container" id="about_medias_container">
                            <div v-if="changeOrderProcessing" class="change-loading"></div>
                            <template v-else>
                                <media-thumb 
                                    v-for="media, i in medias" 
                                    :key="media.id" 
                                    :media="media"
                                    additionalClass="community-media about-media-item" 
                                    :viewMedia="false" 
                                    :hoverMedia="isManager(role)"
                                    owner="community" 
                                    @dragenter="setTarget($event, media)"
                                    @dragstart="startDrag($event, media)"
                                    @dragend="endDrag($event, media)"
                                    @dragenter.prevent
                                    @dragleave.prevent
                                    @click="selectMedia(media)"
                                    :draggable="isManager(role)"
                                />

                                <div 
                                    v-if="isManager(role)" 
                                    class="community-media-container community-media about-media-item"
                                    @click="showAddMediaConfirm"
                                >
                                    <div class="about-new-media-item">
                                        <font-awesome-icon icon="fa fa-plus" class="font-16px" />
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <div class="about-summary-info">
                        <div class="about-summary-info-item w100">
                            <template v-if="privacy === 'public'">
                                <font-awesome-icon icon="fa fa-lock-open"/>
                                <span class="ml-05">
                                    {{ $t('community.community.public-group') }}
                                </span>
                            </template>
                            <template v-else>
                                <font-awesome-icon icon="fa fa-lock"/>
                                <span class="ml-05">
                                    {{ $t('community.community.private-group') }}
                                </span>
                            </template>
                        </div>

                        <div class="about-summary-info-item w100">
                            <font-awesome-icon icon="fa fa-users"/>
                            <span class="ml-05">
                                {{ $t('common.members', { count: totalMembersCount }) }}
                            </span>
                        </div>
                        <div class="about-summary-info-item w60">
                            <font-awesome-icon icon="fa fa-tag" />
                            <span class="ml-05" v-if="community.price">
                                {{ community.price.priceName }}
                            </span>
                            <span class="ml-05" v-else>
                                {{ $t('community.community.free') }}
                            </span>
                        </div>
                        <div v-if="parseInt(ownerShow) === 1 && creator" class="about-summary-info-item w100">
                            <img :src="getUserGravatar(creator)" class="about-summary-img" />
                            <span class="ml-05">
                                {{ $t('community.community.by') }} {{ creatorName }}
                            </span>
                        </div>
                        <div v-else class="about-summary-info-item w100">&nbsp;</div>
                    </div>

                    <template v-if="descriptionView === 'edit'">
                        <textarea
                            class="textarea mt1"
                            :placeholder="$t('community.community.add-description')"
                            v-model="editDescription"
                            rows="3"/>
                        <div class="mt1 text-right">
                            <button class="button is-medium" @click="closeDescriptionEdit">
                                {{ $t('common.cancel') }}
                            </button>
                            <button class="button is-medium community-blue-btn ml-05" :class="saveDescriptionButton" @click="saveDescription">{{ $t('common.save') }}</button>
                        </div>
                    </template>
                    <template v-else-if="descriptionView === 'view'">
                        <div 
                            class="mt-05 white-space-pre-wrap" 
                            :class="isManager(role) ? 'about-description' : ''"
                            @click="editDescriptionView"
                        >
                            <template v-if="description" >
                                {{ description }}
                            </template>
                            <template v-else>
                                {{ $t('community.community.add-description') }}
                            </template>
                        </div>
                    </template>

                    <div class="w100 mt1 mobile-only-show">
                        <div v-if="access === MemberAccess.PENDING" class="w100">
                            <button class="button is-medium w100 community-btn text-uppercase">
                                {{ $t('community.community.summary.membership-pending') }}
                            </button>
                            <div class="cancel-membership text-center" @click="cancelMembershipRequest">
                                {{ $t('community.community.summary.cancel-membership') }}
                            </div>
                        </div>
                        <button
                            v-else-if="access !== MemberAccess.ALLOWED && access !== MemberAccess.BANNED"
                            class="button is-medium w100 community-blue-btn text-uppercase"
                            :class="button"
                            @click="joinMembershipRequest"
                        >
                            {{ $t('community.community.summary.join-group') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="column is-one-third right-column-for-desktop">
                <CommunitySummarySidebarTop />
                <CommunitySummarySidebarBottom v-if="access === MemberAccess.ALLOWED || privacy === 'public'" />
            </div>
        </div>
    </div>
</template>

<script>
import CommunitySummarySidebarTop from "./CommunitySummarySidebarTop.vue";
import CommunitySummarySidebarBottom from "./CommunitySummarySidebarBottom.vue";
import MediaThumb from "./Media/MediaThumb.vue";

import getUserGravatar from "../mixins/util";
import isManager from "../mixins/util";
import { MemberAccess, CommunityStatus } from '../data/enums';
import SubscriptionOverdueBar from "./General/SubscriptionOverdueBar.vue";
import InactiveBar from "./General/InactiveBar.vue";

export default {
    name: 'CommunityAbout',
    mixins: [
        getUserGravatar,
        isManager
    ],
    components: {
        InactiveBar,
        SubscriptionOverdueBar,
        CommunitySummarySidebarTop,
        CommunitySummarySidebarBottom,
        MediaThumb
    },
    data () {
        return {
            MemberAccess,
            CommunityStatus,
            processing: false,
            target: null,
            changeOrderProcessing: false,
            editDescription: '',
            saveDescriptionProcessing: false,
            mediaLoading: true
        };
    },
    async created ()
    {
        this.mediaLoading = true;
        this.self = this;

        if (this.medias.length > 0) {
            this.selectMedia(this.medias[0]);
        }

        await new Promise((resolve) => setTimeout(() => { resolve(); }, 200));
        await this.$store.dispatch('REGISTER_VISITOR');

        this.mediaLoading = false;
    },
    computed: {
        /**
         * Returns member
         */
        member() {
            return this.$store.state.member.data;
        },

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
         * Returns community url
         */
        communityUrl () {
            return this.community?.url || '';
        },

        /**
         * Returns community privacy
         */
        privacy ()
        {
            return this.community.privacy;
        },

        /**
         * Returns community owner_show
         */
        ownerShow ()
        {
            return this.community.owner_show;
        },

        /**
         * Returns community medias
         */
        medias () {
            return this.community?.medias || [];
        },

        /**
         * Returns community first media
         */
        selectedMedia () {
            let selectedMedia = null;
            if (this.medias.length > 0) {
                for (let i = 0; i < this.medias.length; i++) {
                    if (typeof this.medias[i].selected !== 'undefined' && this.medias[i].selected) {
                        selectedMedia = this.medias[i];
                        break;
                    }
                }
            }

            return selectedMedia;
        },

        /**
         * Returns type of media
         */
        selectedMediaType ()
        {
            return this.selectedMedia?.type || this.selectedMedia?.media?.type || '';
        },

        /**
         * Returns path of media
         */
        selectedMediaPath ()
        {
            return this.selectedMedia?.path || this.selectedMedia?.media?.path || '';
        },

        /**
         * Returns extension of media
         */
        selectedMediaExt ()
        {
            return this.selectedMedia?.ext || this.selectedMedia?.media?.ext || '';
        },

        /**
         * Returns filename of media
         */
        selectedMediaFilename ()
        {
            return this.selectedMedia?.filename || this.selectedMedia?.media?.filename || '';
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
         * Returns community owner
         */
        creator ()
        {
            return this.community.user;
        },

        /**
         * Returns creator name
         */
        creatorName ()
        {
            let creatorName = '';
            if (this.creator) {
                creatorName = this.creator.firstname + ' ' + this.creator.lastname;
            }
            
            return creatorName.trim();
        },

        /**
         * Returns current member
         */
        currentMember ()
        {
            return this.$store.state.member.data;
        },

        /**
         * Returns member existence
         */
        memberExist ()
        {
            return (typeof this.currentMember.id !== 'undefined' && parseInt(this.currentMember.id) > 0) ? true : false;
        },

        /**
         * Returns access of member
         */
        access ()
        {
            return this.memberExist ? this.currentMember.access : null;
        },

        /**
         * Returns role of member
         */
        role() {
            return this.memberExist ? this.currentMember.role : null;
        },

        /**
         * Returns button status class
         */
        button ()
        {
            return this.processing ? ' is-loading' : '';
        },

        /**
         * Returns description of community
         */
        description ()
        {
            return this.stripHtml(this.community.description);
        },

        /**
         * Returns save button status class
         */
        saveDescriptionButton ()
        {
            return this.saveDescriptionProcessing ? ' is-loading' : '';
        },

        /**
         * Returns description view
         */
        descriptionView ()
        {
            return this.$store.state.community.aboutDescriptionView;
        },
    },
    methods: {
        async joinMembershipRequest ()
        {
            if (this.communityUrl.toUpperCase() === 'INCUBATEUR') {
                let tab = 'start';
                let path = '/' + this.communityUrl + '/' + tab;
                this.$router.push(path).catch(() => { });
                this.$store.commit('setCommunityTab', tab);
            } else {
                if (this.auth) {
                    this.processing = true;
                    await new Promise((resolve)=>setTimeout(() => { resolve(); }, 2000));
                    await this.$store.dispatch('JOIN_TO_COMMUNITY');
                    this.processing = false;
                } else {
                    this.showSignup();
                }
            }
        },

        async cancelMembershipRequest ()
        {
            if (this.auth) {
                await this.$store.dispatch('LEAVE_FROM_COMMUNITY');
                this.$store.commit('hideModal');
            } else {
                this.showLogin();
            }
        },

        /**
         * Show add media confirm
         */
        showAddMediaConfirm ()
        {
            this.$store.commit('setCommunityTempMedia', {});
            this.$store.commit('setModalSize', 'small');
            this.$store.commit('showModal', {
                type: 'AddMediaConfirm',
                transparent: true
            });
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
                transparent: true,
                extraAction: 'JOIN_TO_COMMUNITY'
            });
        },

        setTarget(evt, item)
        {
            this.target = item;
        },

        /**
         * Community media drag start
         */
        startDrag (e)
        {
            console.log(e)
        },

        /**
         * Community media drag end
         */
        async endDrag(evt, item)
        {
            console.log('endDrag evt', evt);
            console.log('endDrag item', item);

            let containerHeight = 0;
            if (document.getElementById("about_medias_container")) {
                containerHeight = document.getElementById("about_medias_container").offsetHeight;
            }

            // this.changeOrderProcessing = true;

            // Keep original height of container section
            document.getElementById('about_medias_container').setAttribute("style","height:" + containerHeight + "px");

            await this.$store.dispatch('CHANGE_COMMUNITY_MEDIA_ORDER', {
                communityId: this.community.id,
                body: {
                    from: item,
                    to: this.target
                }
            });

            if (this.medias.length > 0) {
                this.selectMedia(this.medias[0]);
            }

            this.changeOrderProcessing = false;
        },

        editDescriptionView()
        {
            if (this.isManager(this.role)) {
                this.editDescription = this.stripHtml(this.community.description);
                this.$store.commit('setAboutDescriptionView', 'edit');
            }
        },

        closeDescriptionEdit()
        {
            this.$store.commit('setAboutDescriptionView', 'view');
        },

        selectMedia(media)
        {
            this.$store.commit('selectCommunityMedia', media);
        },

        selectImage(media)
        {
            this.$store.commit('showChildModal', {
                modalType: 'ViewMedia',
                extraData: this.selectedMedia
            });
        },

        /**
         * Strip html
         */
        stripHtml (html)
        {
            let tmp = document.createElement("DIV");
            tmp.innerHTML = html;
            return tmp.textContent || tmp.innerText || "";
        },

        async saveDescription()
        {
            this.saveDescriptionProcessing = true;

            await this.$store.dispatch('SAVE_ABOUT_DESCRIPTION', {
                communityId: this.community.id,
                body: {
                    description: this.editDescription
                }
            });

            this.saveDescriptionProcessing = false;

            this.$store.commit('setAboutDescriptionView', 'view');
        }
    }
}
</script>

<style scoped>
    .about-name {
        margin-bottom: 15px;
        font-size: 24px;
        font-weight: bold;
    }
    .media-loading-section {
        position: relative;
        min-height: 170px;
    }
    .about-photo-img {
        border-radius: 10px;
        width: 100%;
        height: auto;
        cursor: pointer;
    }
    .about-summary-info {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 15px 3px;
    }
    .about-summary-info-item {
        font-size: 16px;
        font-weight: bold;
        display: flex;
        align-items: center;
        padding: 3px;
    }
    .about-summary-img {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover; 
    }
    .about-description {
        text-align: left;
        font-size: 16px !important;
        font-weight: normal !important;
        white-space: pre-wrap;
        padding: 7px;
        min-height: 38px;
        border-radius: 3px;
    }

    .about-description:hover {
        cursor: pointer;
        background-color: #eee;
    }

    .community-video-container {
        margin-top: 10px;
        border: 1px solid rgb(228, 228, 228);
        display: block;
        cursor: pointer;
        border-radius: 15px;
    }

    .community-video-container iframe,
    .community-video-container video {
        width: 100%;
        height: 100%;
        border-radius: 15px;
        display: block;
    }

    .add-media-section,
    .about-new-media-item {
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #eee;
        filter: grayscale(100%) !important;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }

    .add-media-section {
        border: 4px solid #fff !important;
        border-radius: 8px;
        min-height: 200px;
    }

    .add-media-section:hover {
        background-color: #fff;
        border: dashed 4px #ccc !important;
    }

    .about-new-media-item:hover {
        background-color: #fff;
    }

    .about-medias-container {
        display: flex;
        flex-wrap: wrap;
        margin-left: -5px;
        margin-right: -5px;
        position: relative;
        min-height: 100px;
    }

    .change-loading {
        animation: spinAround 500ms infinite linear;
        border: 2px solid #a39c9c;
        border-radius: 290486px;
        border-right-color: transparent;
        border-top-color: transparent;
        content: "";
        display: block;
        position: absolute;
        transform: translate(-50%, -50%);
        left: 47%;
        top: 41%;
        height: 2em;
        width: 2em;
    }

    .about-media-item {
        display: block;
        width: calc(20% - 10px);
        height: 100px;
        cursor: pointer;
        margin: 5px;
        padding: 0px;
    }

    @media only screen and (min-width:599px) {
        .about-media-item {
            flex: none;
        }
    }

    @media only screen and (max-width: 600px)
    {
        .about-name {
            margin-bottom: 10px;
            font-size: 18px;
        }
        .about-summary-info {
            display: block;
            padding: 10px 5px;
        }
        .about-summary-info-item {
            font-size: 14px;
        }
        .about-description {
            text-align: left;
            font-size: 14px !important;
            padding: 5px;
        }
    }
</style>
