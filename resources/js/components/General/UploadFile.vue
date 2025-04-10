<template>
    <div class="flex align-items-center">
        <input v-if="filetype === 'image'" type="file" @change="onFileChanged($event)" class="file-upload-input"
            accept="image/*" />
        <input v-else-if="filetype === 'video'" type="file" @change="onFileChanged($event)" class="file-upload-input"
            accept="video/*,audio/*" />
        <input v-else-if="filetype === 'attachment'" type="file" @change="onFileChanged($event)"
            class="file-upload-input"
            accept="image/*,video/*,audio/*,.pdf,.csv,application/msword,application/vnd.ms-excel,application/vnd.ms-powerpoint" />

        <a href="#" :title="tooltipTitle" class="control ml-05 tooltip last-child" :class="additionalClass" @click="clickLink">
            <font-awesome-icon v-if="filetype === 'image'" icon="fa-image" class="link-icon" />
            <font-awesome-icon v-else-if="filetype === 'video'" icon="fa-video" class="link-icon" />
            <font-awesome-icon v-else-if="filetype === 'attachment'" icon="fa-paperclip" class="link-icon" />
        </a>
    </div>
</template>

<script>

import axios from "axios";
import { MemberAccess } from '../../data/enums';
import { notify } from "@kyvg/vue3-notification";

export default {
    name: 'UploadFile',
    props: [
        'filetype',
        'owner'
    ],
    data() {
        return {
            MemberAccess,
            uploadingText: this.$t('common.uploading'),
        };
    },
    computed: {
        /**
         * Return auth status
         */
        auth() {
            return this.$store.state.auth.loggedIn;
        },

        /**
         * Returns member
         */
        member() {
            return this.$store.state.member.data;
        },

        /**
         * Returns member existence
         */
        memberExist() {
            return (typeof this.member.id !== 'undefined' && parseInt(this.member.id) > 0) ? true : false;
        },

        /**
         * Returns access of member
         */
        access() {
            return this.memberExist ? this.member.access : null;
        },

        /**
         * Returns tooltip title
         */
        tooltipTitle() {
            let tooltipTitle = this.$t('general.add-attachment');
            if (this.filetype === 'image') {
                tooltipTitle = this.$t('general.add-image');
            } else if (this.filetype === 'video') {
                tooltipTitle = this.$t('general.add-video');
            }

            return tooltipTitle;
        },

        /**
         * Returns additional class
         */
        additionalClass() {
            let additionalClass = '';
            if (this.owner === 'chat') {
                additionalClass = 'right-align';
            }

            return additionalClass;
        },
    },
    methods: {
        /**
         * click link handler
         */
        clickLink() {
            if (this.auth) {
              if (this.access === MemberAccess.ALLOWED) {
                const activeElement = document.activeElement;
                const parent = activeElement.parentNode;
                const input = parent.querySelector('.file-upload-input');
                if (input) {
                  input.click();
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
         * file change handler
         */
        async onFileChanged($event) {
            const url = "/media/upload?filetype=" + this.filetype;
            let formData = new FormData();

            const target = $event.target;
            if (target && target.files) {
                const files = target.files;
                for (var x = 0; x < files.length; x++) {
                    formData.append('size', files[x].size);
                    formData.append("file[]", files[x]);
                }

                if (this.owner === 'general-setting') {
                    this.$store.commit('setCommunityTempMedia', {
                        type: this.filetype,
                        path: this.uploadingText
                    });
                } else if (this.owner === 'classroom') {
                    if (this.filetype === 'image') {
                        this.$store.commit('updateClassroomCloneData', { photo: this.uploadingText })
                    } else if (this.filetype === 'video') {
                        this.$store.commit('updateClassroomCloneData', { media: this.uploadingText })
                    }
                } else if (this.owner === 'lesson') {
                    if (this.filetype === 'video') {
                        this.$store.commit('updateCloneLessonMedia', this.uploadingText)
                    }
                } else if (this.owner === 'resource') {
                    if (this.filetype === 'attachment') {
                        this.$store.commit('updateLessonFile', { url: this.uploadingText })                        
                    }
                } else if (this.owner === 'calendar') {
                    if (this.filetype === 'image') {
                        this.$store.commit('setCommunitySettingsCalendarEventProperty', {
                            key: 'media',
                            v: this.uploadingText
                        });
                    }
                } else if (this.$store.state.modal.childVideoUploadShow) {
                    this.$store.commit('setMediaModalLinkProperty', {
                        key: 'path',
                        v: this.uploadingText
                    });
                } else if (this.owner === 'modal') {
                    if (this.filetype === 'image') {
                        this.$store.commit('setModalExtraDataProperty', {
                            key: 'photo',
                            v: this.uploadingText
                        });
                    } else if (this.filetype === 'video') {
                        this.$store.commit('setModalExtraDataProperty', {
                            key: 'media',
                            v: this.uploadingText
                        });
                    }
                }

                let response = await axios
                    .post(url, formData, {
                        headers: {
                            "Content-Type": "multipart/form-data",
                        }
                    })
                    .then(response => {
                        return response;
                    })
                    .catch((err) => {
                        notify({
                            text: err?.response?.data?.message || 'Unknown error',
                            type: 'error'
                        });
                    });

                if (typeof response !== 'undefined' && typeof response.data !== 'undefined' && typeof response.data[0].success !== 'undefined' && response.data[0].success) {
                    if (this.owner === 'post') {
                        if (this.$store.state.modal.childVideoUploadShow) {
                            this.$store.commit('setMediaModalLink', response.data[0]);
                        } else {
                            this.$store.commit('setCommunityPostMedia', response.data[0]);
                        }
                    } else if (this.owner === 'comment') {
                        if (this.$store.state.modal.childVideoUploadShow) {
                            this.$store.commit('setMediaModalLink', response.data[0]);
                        } else {
                            this.$store.commit('setCommunityPostCommentMedia', response.data[0]);
                        }
                    } else if (this.owner === 'edit-comment') {
                        if (this.$store.state.modal.childVideoUploadShow) {
                            this.$store.commit('setMediaModalLink', response.data[0]);
                        } else {
                            this.$store.commit('setEditedCommentMedia', response.data[0]);
                        }
                    } else if (this.owner === 'reply') {
                        if (this.$store.state.modal.childVideoUploadShow) {
                            this.$store.commit('setMediaModalLink', response.data[0]);
                        } else {
                            this.$store.commit('setReplyCommentMedia', response.data[0]);
                        }
                    } else if (this.owner === 'general-setting') {
                        this.$store.commit('setCommunityTempMedia', {
                            type: this.filetype,
                            path: response.data[0].path
                        });
                    } else if (this.owner === 'classroom') {
                        if (this.filetype === 'image') {
                            this.$store.commit('updateClassroomCloneData', { photo: response.data[0].path })
                        } else if (this.filetype === 'video') {
                            this.$store.commit('updateClassroomCloneData', { media: response.data[0].path })
                        }
                    } else if (this.owner === 'calendar') {
                        if (this.filetype === 'image') {
                            this.$store.commit('setCommunitySettingsCalendarEventProperty', {
                                key: 'media',
                                v: response.data[0].path
                            });
                        }
                    } else if (this.owner === 'lesson') {
                        if (this.filetype === 'video') {
                            this.$store.commit('updateCloneLessonMedia', response.data[0].path)
                        }
                    } else if (this.owner === 'resource') {
                        if (this.filetype === 'attachment') {
                            this.$store.commit('updateLessonFile', { url: response.data[0].path })
                        }
                    } else if (this.owner === 'chat') {
                        if (this.$store.state.modal.childVideoUploadShow) {
                            this.$store.commit('setMediaModalLink', response.data[0]);
                        } else {
                            this.$store.commit('addNewChatMedia', response.data[0]);
                        }
                    } else if (this.owner === 'modal') {
                        if (this.filetype === 'image') {
                            this.$store.commit('setModalExtraDataProperty', {
                                key: 'photo',
                                v: response.data[0].path
                            });
                        } else if (this.filetype === 'video') {
                            this.$store.commit('setModalExtraDataProperty', {
                                key: 'media',
                                v: response.data[0].path
                            });
                        }
                    }
                }
            }
        },


        /**
         * Show login modal
         */
        showLogin() {
            this.$store.commit('resetUser');
            this.$store.commit('showModal', {
                type: 'Login',
                transparent: true
            });
        },
    }
}
</script>

<style scoped></style>
