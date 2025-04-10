<template>
    <div class="w100">
        <div class="dropzone-container">
            <div v-bind="getRootProps()" class="w100">
                <input v-if="imageType" v-bind="getInputProps()" accept="image/*" />
                <input v-else-if="imageVideoType" v-bind="getInputProps()" accept="image/*,video/*" />
                <input v-else v-bind="getInputProps()" />
                <div v-if="isDragActive" class="dropzone-msg">Drop the files here ...</div>
                <div v-else-if="uploading && filetype !== 'logo'" class="dropzone-msg" style='color: #4a4a4a; font-size: 14px;'>
                    {{ $t('common.uploading') }}
                </div>
                <div v-else class="dropzone-msg" :class="additionalDropzoneClass">
                    <div v-if="filetype === 'user_photo'" class="avatar-section">
                        <img src='/assets/img/default.png' class='media-icon user-avatar' />
                        <div class="avatar-icon-section">
                            <font-awesome-icon :icon="['fas', 'camera']" class="avatar-icon" />
                        </div>
                    </div>
                    <div v-else class="text-center">
                        <img src='/assets/icons/upload.png' class='media-icon' /><br>
                        <p v-if="filetype === 'summary_photo'" style='color: #4a4a4a; font-size: 13px;'>{{
                            $t('common.upload-cover-photo') }}</p>
                        <p v-else-if="filetype === 'community_media'" style='color: #4a4a4a; font-size: 14px;'>{{
                            $t('common.upload-image-video') }}</p>
                        <p v-else style='color: #4a4a4a; font-size: 13px;'>{{
                            $t('community.members.setting-modal.admin-settings.upload') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import { useDropzone } from "vue3-dropzone";
import { notify } from "@kyvg/vue3-notification";
import axios from "axios";

export default {
    name: 'Dropzone',
    props: ['filetype', 'selfobj', 'centericon'],
    setup(props) {
        const url = "/media/upload?from=settingModal&filetype=" + props.filetype;
        const saveFiles = (files) => {
            const formData = new FormData();
            for (var x = 0; x < files.length; x++) {
                formData.append('size', files[x].size);
                formData.append("file[]", files[x]);
            }

            if (props.filetype !== 'logo') {
                props.selfobj.$store.commit('setUploading', true);
            }

            axios
                .post(url, formData, {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                })
                .then((response) => {
                    if (typeof response.data[0].success !== 'undefined' && response.data[0].success) {
                        if (props.filetype === 'user_photo') {
                            props.selfobj.$store.commit('setUserProp', {
                                key: 'photo',
                                v: response.data[0].path
                            });
                        } else if (props.filetype === 'community_media') {
                            props.selfobj.$store.commit('setCommunityTempMedia', {
                                type: response.data[0].type,
                                path: response.data[0].path
                            });
                        } else {
                            props.selfobj.$store.commit('setCommunityProperty', {
                                key: props.filetype,
                                v: response.data[0].path
                            });
                        }
                    } else if (typeof response.data[0].message !== 'undefined') {
                        notify({
                            text: response.data[0].message,
                            type: 'error'
                        });
                    }

                    props.selfobj.$store.commit('setUploading', false);
                })
                .catch((err) => {
                    notify({
                        text: err?.response?.data?.message || 'Unknown error',
                        type: 'error'
                    });
                });
        };

        function onDrop(acceptFiles, rejectReasons) {
            saveFiles(acceptFiles);
            console.log(rejectReasons);
        }

        const { getRootProps, getInputProps, ...rest } = useDropzone({ onDrop });

        return {
            getRootProps,
            getInputProps,
            ...rest,
        };
    },
    computed: {
        /**
         * Returns uploading status
         */
        uploading() {
            return this.$store.state.communitycenter.uploading;
        },

        /**
         * Check file type is image or not
         */
        imageType() {
            return this.filetype === 'logo' || this.filetype === 'user_photo' || this.filetype === 'summary_photo' || this.filetype === 'user_photo';
        },

        /**
         * Check file type is image|video or not
         */
        imageVideoType() {
            return this.filetype === 'community_media';
        },

        /**
         * Returns additional dropzone class
         */
        additionalDropzoneClass() {
            let additionalDropzoneClass = '';
            if (this.filetype === 'community_media') {
                additionalDropzoneClass = 'min-height-200';
            }
            
            return additionalDropzoneClass;
        },
    },
}
</script>

<style scoped>
.dropzone-error {
    color: #e74c3c;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 10px;
}

.dropzone-container {
    display: flex;
    align-items: center;
    border: 4px solid #fff !important;
    border-radius: 8px;
    background-color: #eee;
    filter: grayscale(100%) !important;
    cursor: pointer;
}

.dropzone-container:hover {
    background-color: #fff;
    border: dashed 4px #ccc !important;
}

.dropzone-msg {
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.min-height-200 {
    min-height: 200px;
}

.media-icon {
    width: 35%;
    max-width: 90px;
}

.user-avatar {
    border-radius: 50%;
    object-fit: contain;
}

.avatar-section {
    width: 100%;
    position: relative;
    text-align: center;
}

.avatar-icon-section {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    padding: 3px 5px 1px 5px;
    border-radius: 30px;
    background-color: #ddd;
}

.dropzone-container:hover .avatar-icon-section {
    background-color: #ccc;
}

.avatar-icon {
    font-size: 16px;
}

@media only screen and (max-width: 600px) {
    .dropzone-msg {
        min-height: 100px;
    }
}
</style>
