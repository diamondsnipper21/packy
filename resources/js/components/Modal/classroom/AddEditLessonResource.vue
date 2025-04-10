<template>
    <div class="inner-modal-container">
        <template v-if="type === 'link'">
            <div v-if="action === 'add'" class="tab-content-title">
                {{ $t('community.classroom.add-link') }}
            </div>
            <div v-else class="tab-content-title">
                {{ $t('community.classroom.edit-link') }}
            </div>

            <div class="flex mt2">
                <!-- Link label -->
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.classroom.label') }}
                    </p>
                    <input class="input" :placeholder="$t('community.classroom.label')" v-model="lessonLink.label" />
                </div>
            </div>

            <div class="flex mt2">
                <!-- Link url -->
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.classroom.url') }}
                    </p>
                    <input class="input" :placeholder="$t('community.classroom.url')" v-model="lessonLink.url"
                        @keypress="validateUrl($event)" />
                    <div v-if="lessonLink.url && !checkUrlValidation(lessonLink.url)" class="invalid-url-alert">
                        {{ $t('common.invalid-url-alert') }}
                    </div>
                </div>
            </div>

            <div class="flex mt2 mb2 align-items-center jc">
                <button v-if="action === 'add'" class="button is-medium community-blue-btn mr-05"
                    :disabled="linkConfirmDisabled" @click="saveLessonLink">
                    {{ $t('common.add') }}
                </button>
                <button v-else class="button is-medium community-blue-btn mr-05" :disabled="linkConfirmDisabled"
                    @click="saveLessonLink">
                    {{ $t('common.save') }}
                </button>

                <button class="button is-medium community-btn" @click="cancel">
                    {{ $t('common.cancel') }}
                </button>
            </div>
        </template>

        <template v-if="type === 'image'">
            <div v-if="action === 'add'" class="tab-content-title">
                {{ $t('community.classroom.add-file') }}
            </div>
            <div v-else class="tab-content-title">
                {{ $t('community.classroom.edit-file') }}
            </div>

            <div class="flex mt2">
                <!-- File label -->
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.classroom.label') }}
                    </p>
                    <input class="input" :placeholder="$t('community.classroom.label')" v-model="lessonFile.label" />
                </div>
                <div v-if="lessonFile.url && !checkUrlValidation(lessonFile.url)" class="invalid-url-alert">
                    {{ $t('common.invalid-url-alert') }}
                </div>
            </div>

            <div class="flex mt2">
                <!-- File url -->
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.classroom.url') }}
                    </p>

                    <div class="flex align-items-center">
                        <input class="input mr-05" :placeholder="$t('community.classroom.url')" v-model="lessonFile.url"
                            @keypress="validateUrl($event)" />
                        <UploadFile filetype="attachment" :owner="owner" />
                    </div>
                </div>
            </div>

            <div class="flex mt2 mb2 align-items-center jc">
                <button v-if="action === 'add'" class="button is-medium community-blue-btn mr-05"
                    :disabled="fileConfirmDisabled" @click="saveLessonFile">
                    {{ $t('common.add') }}
                </button>
                <button v-else class="button is-medium community-blue-btn mr-05" :disabled="fileConfirmDisabled"
                    @click="saveLessonFile">
                    {{ $t('common.save') }}
                </button>

                <button class="button is-medium community-btn" @click="cancel">
                    {{ $t('common.cancel') }}
                </button>
            </div>
        </template>
    </div>
</template>

<script>

import UploadFile from "../../General/UploadFile.vue";
import validateUrl from "../../../mixins/util";
import checkUrlValidation from "../../../mixins/util";

export default {
    name: "AddEditLessonResource",
    mixins: [
        validateUrl,
        checkUrlValidation
    ],
    components: {
        UploadFile
    },
    data() {
        return {
            uploadingText: this.$t('common.uploading'),
            owner: 'resource'
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

        editedLesson() {
            return this.$store.state.classroom.cloneLesson || {};
        },

        /**
         * extra data of modal
         */
        extraData() {
            return this.$store.state.modal.extraData;
        },

        /**
         * Return resource
         */
        resource() {
            return this.extraData.param;
        },

        /**
         * Return action
         */
        action() {
            return this.extraData.action;
        },

        /**
         * type of media
         */
        type() {
            return typeof this.resource.type !== 'undefined' ? this.resource.type : '';
        },

        /**
         * id of media
         */
        id() {
            return typeof this.resource.id !== 'undefined' ? this.resource.id : 0;
        },

        /**
         * Returns lesson link
         */
        lessonLink() {
            return this.resource.type == 'link' ? this.resource : {};
        },

        /**
         * Returns lesson file
         */
        lessonFile() {
            return this.resource.type == 'image' ? this.$store.state.classroom.lessonFile : {};
        },

        /**
         * Returns lesson link confirm status
         */
        linkConfirmDisabled() {
            let linkConfirmDisabled = true;
            if (this.lessonLink.label !== '' && this.lessonLink.url !== '' && this.checkUrlValidation(this.lessonLink.url)) {
                linkConfirmDisabled = false;
            }

            return linkConfirmDisabled;
        },

        /**
         * Returns lesson file confirm status
         */
        fileConfirmDisabled() {
            let fileConfirmDisabled = true;
            if (this.lessonFile.label !== '' && this.lessonFile.url !== '' && this.checkUrlValidation(this.lessonFile.url)) {
                fileConfirmDisabled = false;
            }

            return fileConfirmDisabled;
        },
    },
    methods: {

        /**
         * Save lesson link
         */
        saveLessonLink() {
            this.$store.commit("setResourceInCloneLesson", this.lessonLink);

            this.cancel();
        },

        /**
         * Save lesson file
         */
        saveLessonFile() {
            this.$store.commit("setResourceInCloneLesson", this.lessonFile);

            this.cancel();
        },

        cancel() {
            this.$store.commit('hideModal');
        },

    }
}
</script>

<style scoped></style>
