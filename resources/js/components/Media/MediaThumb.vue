<template>
	<div
        class="community-media-container"
        :class="mediaThumbClass"
        @click="mediaThumbClick"
    >
        <template v-if="type === 'image'">
            <img :src="path" />
        </template>

        <template v-else-if="type === 'audio'">
            <audio controls class="w100" :key="path">
                <source v-if="ext === 'ogg'" :src="path" type="audio/ogg" />
                <source v-else :src="path" type="audio/mpeg" />
            </audio>
        </template>

        <template v-else-if="type === 'video'">
            <iframe
                v-if="path.includes('youtube')"
                :src="'https://www.youtube.com/embed/' + path.replace('youtube-', '') + '?autoplay=0' + addtionalProp"
                width="100%"
                height="100%"
                frameborder="0"
            ></iframe>

            <iframe
                v-else-if="path.includes('vimeo')"
                :src="'https://player.vimeo.com/video/' + path.replace('vimeo-', '') + '?autoplay=0&loop=false' + addtionalProp"
                width="100%"
                height="100%"
                frameborder="0"
                allow="autoplay; fullscreen; picture-in-picture"
                allowFullScreen
            ></iframe>

            <video v-else :key="path" controls>
                <source :src="path" type="video/mp4" />
            </video>
        </template>

        <template v-else-if="filename !== ''">
            <a
                :href="path"
                class="community-external-link tab-content-sub-title font-weight-500 resource-label"
                target="_blank"
            >
                <font-awesome-icon icon="fa fa-file" class="resource-icon" />&nbsp;
                {{ filename }}
            </a>
        </template>

        <!-- show file info when hover -->
        <div
            v-if="hoverMedia"
            class="community-media-hover-show"
        >
            <div class="file-action-sec">
                <font-awesome-icon icon="fa fa-trash" @click.stop="removeMedia" class="mtba mr-05" />
                <font-awesome-icon icon="fa fa-bars" class="mtba" />
            </div>
        </div>
    </div>
</template>

<script>
import { MemberAccess } from '../../data/enums';

export default {
	name: 'MediaThumb',
	props: [
        'media',
        'additionalClass',
        'viewMedia',
        'hoverMedia',
        'owner'
    ],
    data ()
    {
        return {
            MemberAccess,
            viewableTypes: ['image', 'audio', 'video']
        };
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
         * Return additional container class
         */
        addtionalProp ()
        {
            let addtionalProp = '';

            if (this.additionalClass === 'community-main-media-container') {
                addtionalProp = '&controls=false';
            }

            if (window.innerWidth <= 600) {
                addtionalProp = '&controls=false';
            }

            return addtionalProp;
        },

        /**
         * Returns type of media
         */
        type ()
        {
            return this.media?.media?.type || this.media?.type || '';
        },

        /**
         * Returns path of media
         */
        path ()
        {
            return this.media?.media?.path || this.media?.path || '';
        },

        /**
         * Returns extension of media
         */
        ext ()
        {
            return this.media?.media?.ext || this.media?.ext || '';
        },

        /**
         * Returns filename of media
         */
        filename ()
        {
            return this.media?.media?.filename || this.media?.filename || '';
        },

        /**
         * Returns id of media
         */
        mediaId ()
        {
            return this.media?.media?.id || this.media?.id || 0;
        },

        /**
         * Returns additional classes of media thumb
         */
        mediaThumbClass ()
        {
            let mediaThumbClass = this.additionalClass;
            if (this.media.selected) {
                mediaThumbClass += ' selected';
            }
            
            return mediaThumbClass;
        },
    },
	methods: {
        /**
         * Media thumb click handler
         */
        mediaThumbClick()
        {
            if (this.viewMedia) {
                if (this.viewableTypes.includes(this.type)) {
                    this.$store.commit('showChildModal', {
                        modalType: 'ViewMedia',
                        extraData: this.media
                    });
                } else if (this.path) {
                    window.open(this.path, '_blank');
                }
            }
        },

        /**
         * Removes media
         */
        removeMedia ()
        {
            if (this.auth) {
              if (this.access === MemberAccess.ALLOWED) {
                if (this.owner === 'comment') {
                  this.$store.commit('removeCommunityPostCommentMedia', this.media);
                } else if (this.owner === 'edit-comment') {
                  this.$store.commit('removeEditedCommentMedia', this.media);
                } else if (this.owner === 'reply') {
                  this.$store.commit('removeReplyCommentMedia', this.media);
                } else if (this.owner === 'post') {
                  this.$store.commit('removeCommunityPostMedia', this.media);
                } else if (this.owner === 'chat') {
                  this.$store.commit('removeNewChatMedia', this.media);
                } else if (this.owner === 'community') {
                  this.$store.commit('setModalSize', 'small');
                  this.$store.commit('showModal', {
                    type: 'DeleteConfirm',
                    extraData: this.media,
                    title: this.$t('community.community.delete-media-modal.title'),
                    description: this.$t('community.community.delete-media-modal.description'),
                    button: this.$t('common.delete'),
                    disabledConfirm: false,
                    action: 'REMOVE_COMMUNITY_MEDIA',
                    actionParam: {
                      communityId: this.community.id,
                      mediaId: this.mediaId
                    }
                  });
                }
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
	}
}
</script>

<style scoped>
    .community-media-container {
        height: 0px;
        position: relative;
        border: 1px solid rgb(228, 228, 228);
        display: block;
        padding-top: 56.25%;
    }

    .community-main-media-container {
        position: relative;
        width: 150px;
        padding-top: 150px;
        border-radius: 10px;
    }

    .community-media-container.selected {
        border: 1px solid rgb(128, 128, 128);
    }

    .community-media-container img,
    .community-media-container iframe,
    .community-media-container video,
    .community-media-container audio,
    .community-media-container a {
        position: absolute;
        pointer-events: none;
    }

    .community-media-container img,
    .community-media-container iframe,
    .community-media-container video {
        border-radius: 10px;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
    }

    .community-media-container audio,
    .community-media-container a {
        transform: translate(-50%, -50%);
        left: 50%;
        top: 50%;
        padding: 0px 5px;
    }

    .community-media-grid-item img,
    .community-media-grid-item iframe,
    .community-media-grid-item video {
        border-radius: 3px;
    }

    .community-media-container img {
        object-fit: cover;
    }

    .tab-content-sub-title {
        max-width: 100%;
    }

    @media only screen and (max-width: 600px)
    {
        .community-main-media-container {
            width: 90px;
            padding-top: 90px;
            border-radius: 5px;
        }

        .community-main-media-container img,
        .community-main-media-container iframe,
        .community-main-media-container video {
            border-radius: 5px;
        }
    }
</style>
