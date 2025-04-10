<template>
	<div>
		<h1 class="title is-4">{{ title }}</h1>
		<p class="label mt1">{{ description }}</p>

		<div class="flex align-items-center mt1">
			<input
                class="input"
                :class="type === 'video' ? 'mr-05' : ''"
                :placeholder="$t('common.link')"
                v-model="link"
                @keypress="validateUrl"
                autofocus
            />
            <UploadFile v-if="childVideoUploadShow" filetype="video" :owner="owner" />
        </div>

		<div class="flex jc mt2">
			<button class="button is-medium" @click="cancel">
                {{ $t('common.cancel') }}
            </button>
            <button
                :class="button"
                @click="addLink"
                :disabled="disabledLink"
                class="ml-05"
            >{{ $t('common.save') }}</button>
		</div>
	</div>
</template>

<script>

import UploadFile from "../../General/UploadFile.vue";
import validateUrl from "../../../mixins/util";
import getVideoIdFromUrl from "../../../mixins/util";

export default {
	name: "AddLink",
    mixins: [validateUrl, getVideoIdFromUrl],
	components: {
        UploadFile
    },
	data () {
		return {
            processing: false
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
         * Returns member
         */
        member ()
        {
            return this.$store.state.member.data;
        },

        /**
         * Returns access of member
         */
        access ()
        {
            return this.member ? this.member.access : null;
        },

        /**
         * Returns user
         */
        user ()
        {
            return this.$store.state.auth.user;
        },

        /**
         * Get | Set link
         */
        link: {
            get () {
                return this.$store.state.modal.mediaModalLink.path;
            },
            set (v) {
                this.$store.commit('setMediaModalLinkProperty', {
                    key: 'path',
                    v
                });
            }
        },

        /**
         * Returns media type
         */
        mediaType ()
        {
            return typeof this.$store.state.modal.mediaModalLink.type !== 'undefined' ? this.$store.state.modal.mediaModalLink.type : '';
        },

        /**
         * Returns media filename
         */
        mediaFilename ()
        {
            return typeof this.$store.state.modal.mediaModalLink.filename !== 'undefined' ? this.$store.state.modal.mediaModalLink.filename : '';
        },

        /**
         * Returns media ext
         */
        mediaExt ()
        {
            return typeof this.$store.state.modal.mediaModalLink.ext !== 'undefined' ? this.$store.state.modal.mediaModalLink.ext : '';
        },

		/**
         * Add ' is-loading' when processing
         */
        button ()
        {
            let button = 'button ';

            return (this.processing)
                ? button + this.$store.state.modal.buttonStyle + ' is-loading'
                : button + this.$store.state.modal.buttonStyle;
        },

        /**
         * Return true to disable the Submit File button
         */
		disabledLink ()
        {
			return this.link === "" || this.link === "uploading ...";
		},

        /**
         * extra data of modal
         */
        extraData ()
        {
            return this.$store.state.modal.childModalExtraData;
        },

        /**
         * video upload link show status
         */
        childVideoUploadShow ()
        {
            return this.$store.state.modal.childVideoUploadShow;
        },
        
        /**
         * Child type of modal
         */
        type ()
        {
        	return typeof this.extraData.type !== 'undefined' ? this.extraData.type : '';
        },

        /**
         * Child owner of modal
         */
        owner ()
        {
        	return typeof this.extraData.owner !== 'undefined' ? this.extraData.owner : '';
        },

        /**
         * Return modal title
         */
		title ()
        {
        	let title = '';
        	if (this.type === 'link') {
				title = this.$t('community.community.add-link.title');
			} else if (this.type === 'image') {
				title = this.$t('community.community.add-image.title');
			} else if (this.type === 'video') {
				title = this.$t('community.community.add-video.title');
			}

			return title;
		},

		/**
         * Return modal description
         */
		description ()
        {
			let description = '';
        	if (this.type === 'link') {
				description = this.$t('community.community.add-link.description');
			} else if (this.type === 'image') {
				description = this.$t('community.community.add-image.description');
			} else if (this.type === 'video') {
				description = this.$t('community.community.add-video.description');
			}

			return description;
		}
	},
    methods: {

    	cancel() {
			this.$store.commit('hideChildModal');
		},

		/**
		* Add link
		*/
		addLink ()
		{
			if (this.type === 'link') {
				if (this.owner === 'post') {
					this.$store.commit('setCommunityPostProperty', {
		                key: 'path',
		                v: this.link
		            });
				} else if (this.owner === 'comment') {
					this.$store.commit('setCommunityPostCommentProperty', {
		                key: 'path',
		                v: this.link
		            });
				} else if (this.owner === 'edit-comment') {
                    this.$store.commit('setEditedCommentProperty', {
                        key: 'path',
                        v: this.link
                    });
                } else if (this.owner === 'reply') {
					this.$store.commit('setReplyCommentProperty', {
		                key: 'path',
		                v: this.link
		            });
				}

				this.cancel();
			} else if (this.type === 'image') {
                let media = {
                    type: this.mediaType,
                    ext: this.mediaExt,
                    filename: this.mediaFilename,
                    path: this.link
                };

				if (this.owner === 'post') {
					this.$store.commit('setCommunityPostMedia', media);
				} else if (this.owner === 'comment') {
					this.$store.commit('setCommunityPostCommentMedia', media);
				} else if (this.owner === 'edit-comment') {
                    this.$store.commit('setEditedCommentMedia', media);
                } else if (this.owner === 'reply') {
					this.$store.commit('setReplyCommentMedia', media);
				}

				this.cancel();
			} else if (this.type === 'video') {
				let videoId = this.getVideoIdFromUrl(this.link);
                if (videoId === null)
                {
                	this.$notify({
                		text: 'This video url is not valid.',
                		type: 'error'
                	});
                }
                else
                {
                    let media = {
                        type: this.mediaType,
                        ext: this.mediaExt,
                        filename: this.mediaFilename,
                        path: videoId
                    };

                    if (this.owner === 'post') {
						this.$store.commit('setCommunityPostMedia', media);
					} else if (this.owner === 'comment') {
						this.$store.commit('setCommunityPostCommentMedia', media);
					} else if (this.owner === 'edit-comment') {
                        this.$store.commit('setEditedCommentMedia', media);
                    } else if (this.owner === 'reply') {
                        this.$store.commit('setReplyCommentMedia', media);
					} else if (this.owner === 'chat') {
                        this.$store.commit('addNewChatMedia', media);
                    }

					this.cancel();
                }
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
