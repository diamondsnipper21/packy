<template>
    <div>
        <p class="tab-content-title">
            {{ $t('community.members.setting-modal.admin-settings.general') }}
        </p>

        <div class="columns">
            <div class="column is-one-third general-left-column-for-desktop">
                <div class="photo-upload-section">
                    <div v-if="logo" class="setting-photo-section">
                        <img :src="logo" class="setting-photo-img" />

                        <div class="remove-setting-img">
                            <font-awesome-icon icon="fa fa-trash" class="mtba mr-05 trash" @click.stop="removeCommunityLogo" />
                        </div>
                    </div>

                    <Dropzone v-else filetype="logo" :selfobj="self" />

                    <div class="recommend-info">
                        <div class="recommend-info-title">
                            {{ $t('community.members.setting-modal.admin-settings.icon') }}
                        </div>
                        <div class="recommend-info-desc">
                            {{ $t('community.members.setting-modal.admin-settings.recommended') }}
                        </div>
                        <div class="recommend-info-desc">
                            128 x 128
                        </div>
                    </div>
                </div>
            </div>
            <div class="column general-right-column-for-desktop">
                <div class="photo-upload-section">
                    <div v-if="summary_photo" class="setting-photo-section">
                        <img :src="summary_photo" class="setting-photo-img" />

                        <div class="remove-setting-img">
                            <font-awesome-icon icon="fa fa-trash" class="mtba mr-05 trash" @click.stop="removeCommunityPhoto" />
                        </div>
                    </div>

                    <Dropzone v-else filetype="summary_photo" :selfobj="self" />

                    <div class="recommend-info">
                        <div class="recommend-info-title">
                            {{ $t('community.members.setting-modal.admin-settings.cover') }}
                        </div>
                        <div class="recommend-info-desc">
                            {{ $t('community.members.setting-modal.admin-settings.recommended') }}
                        </div>
                        <div class="recommend-info-desc">
                            1084 x 576
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex mt2">
            <!-- Group name -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.community.create-modal.group-name') }}
                </p>
                <input
                    class="input"
                    :placeholder="$t('community.community.create-modal.group-name')"
                    v-model="name"
                />
            </div>
        </div>

        <div class="flex mt2">
            <!-- Group url -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.members.setting-modal.admin-settings.url') }}
                </p>
                <input
                    class="input"
                    :placeholder="$t('community.members.setting-modal.admin-settings.url')"
                    v-model="url"
                    @keypress="validateCommunityUrl($event)"
                    :disabled="disabledChangeCommunityUrl"
                />
                <p class="text-left input-desc">
                    {{ $t('community.members.setting-modal.admin-settings.url-desc') }}
                </p>
            </div>
        </div>

        <div class="flex mt2">
            <!-- Media option -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.community.create-modal.media') }}
                </p>
                <div class="radio-select-section mt-05">
                    <div
                        v-for="(item, index) in mediaOptions"
                        :key="item.key"
                        class="radio-select-item"
                        :class="privacySectionClass(item)"
                        @click="selectMediaOption(item.key)"
                    >
                        <div class="flex align-items-center pointer">
                            <input
                                type="radio"
                                :id="item.key"
                                v-model="item.selected"
                                :value="selected"
                                class="mr-05"
                            />

                            <font-awesome-icon v-if="item.key === 'image'" icon="fa fa-image" class="mr-05 font-14px" />
                            <font-awesome-icon v-else-if="item.key === 'video'" icon="fa fa-video" class="mr-05 font-14px" />

                            <label class="text-left input-label w100 pointer">
                                {{ item.label }}
                            </label>
                        </div>
                        <p class="text-left input-desc">
                            {{ item.desc }}
                        </p>
                    </div>
                </div>

                <div class="mt-05" v-if="mediaType === 'image'">
                    <p class="text-left input-label">
                        {{ $t('community.community.create-modal.photo') }}
                    </p>

                    <div class="flex align-items-center">
                        <input
                            class="input mr-05"
                            :placeholder="$t('community.community.create-modal.photo-desc')"
                            v-model="tempMediaPath"
                            @keypress="validateUrl($event)"
                        />
                        <UploadFile filetype="image" :owner="owner" />
                    </div>
                </div>

                <div class="mt-05" v-else-if="mediaType === 'video'">
                    <p class="text-left input-label">
                        {{ $t('community.community.create-modal.video') }}
                    </p>

                    <div class="flex align-items-center">
                        <input
                            class="input mr-05"
                            :placeholder="$t('community.community.create-modal.video-desc')"
                            v-model="tempMediaPath"
                            @keypress="validateUrl($event)"
                        />
                        <UploadFile filetype="video" :owner="owner" />
                    </div>
                </div>
            </div>
        </div>

        <div class="radio-select-section">
            <div class="radio-select-item" :class="parseInt(ownerShow) === 1 ? 'selected-item' : ''">
                <input
                    class="mr-05"
                    type="radio"
                    v-model="ownerShow"
                    value="1"
                    @click="checkOwnerShow('1')"
                />
                <label class="text-left input-label pointer" @click="checkOwnerShow('1')">
                    {{ $t('community.members.setting-modal.admin-settings.show-owner-title') }}
                </label>

                <p class="text-left input-desc">
                    {{ $t('community.members.setting-modal.admin-settings.show-owner-desc') }}
                </p>
            </div>
            <div class="radio-select-item" :class="parseInt(ownerShow) === 0 ? 'selected-item' : ''">
                <input
                    class="mr-05"
                    type="radio"
                    v-model="ownerShow"
                    value="0"
                    @click="checkOwnerShow('0')"
                />
                <label class="text-left input-label pointer" @click="checkOwnerShow('0')">
                    {{ $t('community.members.setting-modal.admin-settings.hide-owner-title') }}
                </label>

                <p class="text-left input-desc">
                    {{ $t('community.members.setting-modal.admin-settings.hide-owner-desc') }}
                </p>
            </div>
        </div>

        <div class="flex mt2">
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.members.setting-modal.admin-settings.group-summary') }}
                </p>
                <textarea
                    class="textarea"
                    :placeholder="$t('community.members.setting-modal.admin-settings.group-summary')"
                    v-model="summary_description"
                    rows="3"
                />
            </div>
        </div>

        <div class="flex mt2">
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.members.setting-modal.admin-settings.group-desc') }}
                </p>
                <textarea
                    class="textarea"
                    :placeholder="$t('community.members.setting-modal.admin-settings.group-desc')"
                    v-model="description"
                    rows="5"
                />
            </div>
        </div>

        <div class="radio-select-section">
            <div
                v-for="(privacy, index) in privacyOptions"
                :key="privacy.key"
                class="radio-select-item"
                :class="privacySectionClass(privacy)"
                @click="selectPrivacyOption(privacy.key)"
            >
                <div class="flex align-items-center pointer">
                    <input
                        type="radio"
                        :id="privacy.key"
                        v-model="privacy.selected"
                        :value="selected"
                        class="mr-05"
                    />

                    <font-awesome-icon v-if="privacy.key === 'private'" icon="fa fa-lock" class="mr-05 font-14px" />
                    <font-awesome-icon v-else-if="privacy.key === 'public'" icon="fa fa-lock-open" class="mr-05 font-14px" />

                    <label class="text-left input-label w100 pointer">
                        {{ privacy.label }}
                    </label>
                </div>
                <p class="text-left input-desc">
                    {{ privacy.desc }}
                </p>
            </div>
        </div>

        <div class="mt2 font-14px">
            {{ $t('community.members.setting-modal.admin-settings.agree-desc') }}
        </div>

        <div class="submit-button mt1">
            <button
                class="button is-medium community-blue-btn"
                :disabled="disabledConfirm"
                @click="updateSettings">
                {{ $t('common.save') }}
            </button>
        </div>
    </div>
</template>

<script>

import Dropzone from "../../../../components/General/Dropzone";
import UploadFile from "../../../General/UploadFile";
import validateUrl from "../../../../mixins/util";
export default {
	name: 'GeneralSetting',
    mixins: [validateUrl],
    components: {
        Dropzone,
        UploadFile
    },
    data () {
        return {
            selected: 1,
            self: null,
            mediaType: 'image',
            uploadingText: this.$t('common.uploading'),
            owner: 'general-setting'
        };
    },
    created ()
    {
        this.self = this;
    },
    mounted() {
        this.$store.commit('setCommunityTempMedia', this.firstMedia);

        if (this.tempMediaType) {
            this.mediaType = this.tempMediaType;
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
         * Returns user
         */
        user ()
        {
            return this.$store.state.auth.user;
        },

        /**
         * Returns community
         */
        community ()
        {
            return this.$store.state.community.clone;
        },

        /**
         * Get | Set name
         */
        name: {
            get () {
                return this.community.name;
            },
            set (v) {
                this.$store.commit('setCommunityProperty', {
                    key: 'name',
                    v
                });
            }
        },

        /**
         * Get | Set url
         */
        url: {
            get () {
                return this.community.url;
            },
            set (v) {
                this.$store.commit('setCommunityProperty', {
                    key: 'url',
                    v
                });
            }
        },

        /**
         * Get | Set summary_description
         */
        summary_description: {
            get () {
                return this.community.summary_description;
            },
            set (v) {
                this.$store.commit('setCommunityProperty', {
                    key: 'summary_description',
                    v
                });
            }
        },

        /**
         * Get | Set description
         */
        description: {
            get () {
                return this.stripHtml(this.community.description);
            },
            set (v) {
                this.$store.commit('setCommunityProperty', {
                    key: 'description',
                    v
                });
            }
        },

        /**
         * Returns community logo
         */
        logo ()
        {
            return this.community.logo;
        },

        /**
         * Returns community summary_photo
         */
        summary_photo ()
        {
            return this.community.summary_photo;
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
         * Returns privacy options
         */
        privacyOptions ()
        {
            return [{
                key: "public",
                label: this.$t('community.community.create-modal.public'),
                desc: this.$t('community.community.create-modal.public-desc'),
                selected: this.privacy === 'public' ? 1 : 0
            }, {
                key: "private",
                label: this.$t('community.community.create-modal.private'),
                desc: this.$t('community.community.create-modal.private-desc'),
                selected: this.privacy === 'private' ? 1 : 0
            }];
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
        firstMedia () {
            let firstMedia = null;
            if (this.medias.length > 0) {
                firstMedia = this.medias[0];
            }

            return firstMedia;
        },

        /**
         * Returns community first image media
         */
        firstImageMedia () {
            let firstImageMedia = null;
            if (this.medias.length > 0) {
                for (let i = 0; i < this.medias.length; i++) {
                    if (this.medias[i].type === 'image') {
                        firstImageMedia = this.medias[i];
                        break;
                    }
                }
            }

            return firstImageMedia;
        },

        /**
         * Returns community first video media
         */
        firstVideoMedia () {
            let firstVideoMedia = null;
            if (this.medias.length > 0) {
                for (let i = 0; i < this.medias.length; i++) {
                    if (this.medias[i].type === 'video') {
                        firstVideoMedia = this.medias[i];
                        break;
                    }
                }
            }

            return firstVideoMedia;
        },

        /**
         * Returns temp media of community
         */
        tempMedia ()
        {
            return this.$store.state.community.tempMedia;
        },

        /**
         * Returns temp media type of community
         */
        tempMediaType ()
        {
            return this.tempMedia && typeof this.tempMedia.type !== 'undefined' ? this.tempMedia.type : null;
        },

        /**
         * Returns temp media path of community
         */
        tempMediaPath ()
        {
            return this.tempMedia && typeof this.tempMedia.path !== 'undefined' ? this.tempMedia.path : null;
        },

        /**
         * Returns mediaOptions
         */
        mediaOptions ()
        {
            return [{
                key: "image",
                label: this.$t('community.community.create-modal.photo'),
                desc: this.$t('community.community.create-modal.photo-desc'),
                selected: this.mediaType === 'image' ? 1 : 0
            }, {
                key: "video",
                label: this.$t('community.community.create-modal.video'),
                desc: this.$t('community.community.create-modal.video-desc'),
                selected: this.mediaType === 'video' ? 1 : 0
            }];
        },

        /**
         * Returns disabled confirm
         */
        disabledConfirm ()
        {
            let disabledConfirm = true;
            if (this.name !== null && this.name !== '' && this.url !== null && this.url !== '') {
                disabledConfirm = false;
            }

            return disabledConfirm;
        },

        /**
         * Returns allowed members
         */
        allowedMembers ()
        {
            return this.$store.state.communitycenter.allowedMembers;
        },

        /**
         * Returns member's count
         */
        memberCnt ()
        {
            return this.community.number_of_members;
        },

        /**
         * disable change of community url
         */
        disabledChangeCommunityUrl (event)
        {
            if (this.memberCnt >= 50) {
                return true;
            } else {
                return false;
            }
        },
    },
	methods: {

        removeCommunityLogo ()
        {
            this.$store.commit('setCommunityProperty', {
                key: 'logo',
                v: null
            });
        },

        removeCommunityPhoto ()
        {
            this.$store.commit('setCommunityProperty', {
                key: 'summary_photo',
                v: null
            });
        },

        checkOwnerShow (show)
        {
            this.$store.commit('setCommunityProperty', {
                key: 'owner_show',
                v: show
            });
        },

        /**
         * Returns privacy section class
         */
        privacySectionClass (privacy)
        {
            let privacySectionClass = privacy.key;
            if (privacy.selected === 1) {
                privacySectionClass += ' selected-item';
            }

            return privacySectionClass;
        },

        /**
         * Select privacy option
         */
        selectPrivacyOption (columnKey)
        {
            this.$store.commit('setCommunityProperty', {
                key: 'privacy',
                v: columnKey
            });
        },

        /**
         * Select media option
         */
        selectMediaOption (columnKey)
        {
            this.mediaType = columnKey;
            if (this.mediaType === 'image') {
                this.$store.commit('setCommunityTempMedia', this.firstImageMedia);
            } else if (this.mediaType === 'video') {
                this.$store.commit('setCommunityTempMedia', this.firstVideoMedia);
            }
        },

        /**
         * validate community url
         */
        validateCommunityUrl (event)
        {
            if (this.memberCnt >= 50) {
                event.preventDefault();
                return false;
            } else {
                this.validateUrl(event);
            }
        },

        async updateSettings ()
        {
            await this.$store.dispatch('UPDATE_COMMUNITY');
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
         * Strip html
         */
        stripHtml (html)
        {
            let tmp = document.createElement("DIV");
            tmp.innerHTML = html;
            return tmp.textContent || tmp.innerText || "";
        },
	}
}
</script>

<style scoped>
    .photo-upload-section {
        display: flex;
    }

    .recommend-info {
        width: 120px;
        padding: 0px 10px;
    }

    .recommend-info-title {
        font-size: 14px;
        font-weight: bold;
    }

    .recommend-info-desc {
        margin-top: 5px;
        font-size: 13px;
        color: rgb(144, 144, 144);
    }

    .setting-photo-img {
        display: block;
        border-radius: 5px;
    }

    .setting-photo-section {
        position: relative;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .remove-setting-img {
        position: absolute;
        right: 5px;
        top: 5px;
        font-size: 13px;
        padding: 0px 5px;
        z-index: 1;
        cursor: pointer;
    }

    .remove-setting-img .trash {
        padding: 5px;
        cursor: pointer;
    }

    .setting-photo-section .remove-setting-img {
        visibility: hidden;
    }

    .setting-photo-section:hover .remove-setting-img {
        visibility: visible;
    }

    .setting-photo-section:hover .setting-photo-img {
        opacity: 0.4;
    }

    .input-desc {
        font-size: 13px;
        color: rgb(144, 144, 144);
    }

    .general-left-column-for-desktop {
        padding-left: 0px;
    }

    .general-right-column-for-desktop {
        padding-right: 0px;
    }

    @media only screen and (max-width: 600px)
    {
        .general-left-column-for-desktop {
            padding-left: 0.75rem;
        }

        .general-right-column-for-desktop {
            padding-right: 0.75rem;
        }
    }
</style>
