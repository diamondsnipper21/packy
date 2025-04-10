<template>
	<div>
		<h1 class="title is-4">{{ title }}</h1>

		<div class="flex align-items-center mt1">
            <multiselect v-model="selectedPage"
                :options="pageOptions"
                :multiple="false"
                :close-on-select="true"
                :clear-on-select="true"
                :preserve-search="true"
                :hide-selected="true"
                label="name"
                track-by="name"
                :placeholder="$t('community.community.post.search-for-page')"
                :select-label="$t('common.press-enter-to-select')"
                :selected-label="$t('common.selected')"
                :deselect-label="$t('common.press-enter-to-deselect')"
            >
                <template #option="props">
                    <div class="flex-1">
                        <div class="lesson-label">
                            {{ props.option.name }}
                        </div>
                        <div class="classroom-label">
                            {{ props.option.classroomTitle }}
                        </div>
                    </div>
                </template>
            </multiselect>
        </div>

		<div class="flex jc mt2">
			<button class="button is-medium" @click="cancel">
                {{ $t('common.cancel') }}
            </button>
            <button
                :class="button"
                @click="togglePinPost"
                :disabled="!selectedPage"
                class="ml-05"
            >{{ btnText }}</button>
		</div>
	</div>
</template>

<script>

import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'
import { PostAction } from '../../../data/enums';

export default {
	name: "TogglePinPostPage",
	components: {
        Multiselect
    },
	data () {
		return {
            processing: false,
            selectedPageIds: [],
            PostAction,
		};
	},
	computed: {
        /**
         * Return community data
         */
        community() {
            return this.$store.state.community.data;
        },

        /**
         * Return community post
         */
        communityPost() {
            return this.$store.state.post.data;
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
         * extra data of modal
         */
        extraData ()
        {
            return this.$store.state.modal.childModalExtraData;
        },

        /**
         * Return pages
         */
        pages ()
        {
            return this.extraData.pages;
        },

        /**
         * Return action
         */
        action ()
        {
            return this.extraData.action;
        },

        /**
         * Return title
         */
        title ()
        {
            let title = this.$t('community.community.post.pin-to-page');
            if (this.action === PostAction.UNPIN_FROM_PAGE) {
                title = this.$t('community.community.post.unpin-from-course-page');
            }

            return title;
        },

        /**
         * Return button text
         */
        btnText ()
        {
            let btnText = this.$t('community.community.post.pin-btn');
            if (this.action === PostAction.UNPIN_FROM_PAGE) {
                btnText = this.$t('community.community.post.unpin');
            }

            return btnText;
        },

        /**
         * Returns all lesson pages
         */
        pageOptions ()
        {
            let pageOptions = [];
            if (this.pages.length > 0) {
                this.pages.map((page, index) => {
                    pageOptions.push({
                        id: page.id,
                        name: page.title,
                        classroomTitle: page.classroomTitle,
                        classroomId: page.classroom_id,
                        setId: page.set_id,
                    });
                });
            }

            return pageOptions;
        },

        /**
         * Get | Set new group members
         */
        selectedPage: {
            get () {
                let selectedPage = null;
                if (this.pageOptions.length > 0) {
                    this.pageOptions.map((page, index) => {
                        if (this.selectedPageIds.includes(page.id)) {
                            selectedPage = page;
                        }
                    });
                }

                return selectedPage;
            },
            set (v) {
                this.selectedPageIds = [];
                if (Array.isArray(v)) {
                    v.map(page => {
                        this.selectedPageIds.push(page.id);
                    });
                } else if (typeof v === 'object') {
                    this.selectedPageIds.push(v.id);
                }
            }
        },
	},
    methods: {
        /**
         * hide child modal
         */
    	cancel() {
			this.$store.commit('hideChildModal');
		},

        /**
         * pin | unpin post to selected page
         */
        async togglePinPost() {
            let selectedPageId = this.selectedPage?.id || null;
            if (selectedPageId) {
                this.processing = true;
                this.$store.commit('setCommunityPostProperty', {
                    key: 'action',
                    v: this.action
                });
                this.$store.commit('setCommunityPostProperty', {
                    key: 'classroom_lesson_id',
                    v: selectedPageId
                });

                let notification = this.$t('community.community.post.post-pinned-to-page');
                if (this.action === PostAction.UNPIN_FROM_PAGE) {
                    notification = this.$t('community.community.post.post-unpinned-from-page');
                }

                await this.$store.dispatch('UPDATE_COMMUNITY_POST', {
                    hideModal: false,
                    id: this.communityPost.id,
                    notification
                });
                this.processing = false;

                this.cancel();

                this.goToLessonPage();

                this.$store.commit('hideModal');
            }
        },

        async goToLessonPage() {
            await this.$store.dispatch('GET_CLASSROOM_LESSON', {
                classroom_id: this.selectedPage.classroomId || 0,
                set_id: this.selectedPage.setId || 0,
                id: this.selectedPage.id
            });

            await this.$router.push('/' + this.community.url + '/ressources/' + this.selectedPage.classroomId + '/lesson/' + this.selectedPage.id);
            this.$store.commit('setCommunityTab', 'ressources');
        },
    }
}
</script>

<style scoped>
    .lesson-label {
        font-size: 14px;
        font-weight: 700;
    }

    .classroom-label {
        font-size: 13px;
    }


    @media only screen and (max-width: 600px)
    {
        .lesson-label {
            font-size: 13px;
        }

        .classroom-label {
            font-size: 12px;
        }
    }
</style>
