<template>
    <div>
        <div v-if="linkShow === 'list'">
            <div>
                <div class="tab-content-title">
                    {{ $t('community.members.setting-modal.admin-settings.links') }}
                </div>

                <div class="mt1">
                    <button class="button is-medium community-blue-btn" @click="addLink">
                        {{ $t('community.community.add-link.title') }}
                    </button>
                </div>
            </div>

            <div class="mt2 mb2">
                <div v-for="link, index in links" :key="link.id" class="tab-content-item">
                    <div class="tab-content-item-content">
                        <div class="tab-content-sub-title font-weight-600 max-full-width">
                            {{ index + 1 }}. {{ link.name }}
                        </div>
                        <div class="tab-content-desc">
                            {{ link.url }}
                        </div>
                    </div>
                    <div class="tab-content-item-action">
                        <button class="button is-medium community-btn mr-05" @click="editLink(link)">
                            {{ $t('common.edit') }}
                        </button>

                        <div :class="showDropdown(link)" class="tab-content-action-dropdown">
                            <div class="dropdown-trigger flex">
                                <button class="button is-medium community-btn tab-content-action-dropdown-link"
                                    @click.stop="toggleDropdown(link)">
                                    <font-awesome-icon icon="fa fa-bars"
                                        class="tab-content-action-dropdown-link-icon" />
                                </button>
                            </div>
                            <div class="dropdown-menu-items">
                                <div class="tab-content-item-action-item" @click.stop="deleteLink(link)">
                                    {{ $t('common.delete') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt2">
                <div class="tab-content-title">{{ $t('community.members.setting-modal.admin-settings.tabs-panel.title') }}</div>
                <div class="tab-content-desc">{{ $t('community.members.setting-modal.admin-settings.tabs-panel.subtitle') }}</div>

                <InputSwitch
                    v-model="tabClassroom"
                    :title="$t('community.members.setting-modal.admin-settings.tabs-panel.classroom.title')"
                    :subtitle="$t('community.members.setting-modal.admin-settings.tabs-panel.classroom.subtitle')"
                />

                <InputSwitch
                    v-model="tabCalendar"
                    :title="$t('community.members.setting-modal.admin-settings.tabs-panel.calendar.title')"
                    :subtitle="$t('community.members.setting-modal.admin-settings.tabs-panel.calendar.subtitle')"
                />

                <InputSwitch
                    v-model="tabLeaderboard"
                    :title="$t('community.members.setting-modal.admin-settings.tabs-panel.leaderboard.title')"
                    :subtitle="$t('community.members.setting-modal.admin-settings.tabs-panel.leaderboard.subtitle')"
                />

                <div class="submit-button mt1">
                    <button class="button is-medium community-blue-btn" @click="saveTabSettings">
                        {{ $t('common.save') }}
                    </button>
                </div>
            </div>
        </div>

        <div v-else-if="linkShow === 'add'">
            <div class="tab-content-title">
                {{ $t('community.community.add-link.title') }}
            </div>

            <div class="flex mt2">
                <!-- Link name -->
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.community.title') }}
                    </p>
                    <input class="input" :placeholder="$t('community.community.title')" v-model="newLink.name" />
                </div>
            </div>

            <div class="flex mt1">
                <!-- Link url -->
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.members.setting-modal.admin-settings.url') }}
                    </p>
                    <input class="input" :placeholder="$t('community.community.add-link.description')"
                        v-model="newLink.url" @keypress="validateUrl($event)" />
                    <div v-if="newLink.url && !checkUrlValidation(newLink.url)" class="invalid-url-alert">
                        {{ $t('common.invalid-url-alert') }}
                    </div>
                </div>
            </div>


            <div class="flex mt2">
                <button class="button is-medium community-blue-btn mr-05" :disabled="addConfirm" @click="saveAddLink">
                    {{ $t('common.add') }}
                </button>
                <button class="button is-medium community-btn" @click="close">
                    {{ $t('common.cancel') }}
                </button>
            </div>
        </div>

        <div v-else-if="linkShow === 'edit'">

            <div class="tab-content-title">
                {{ $t('community.members.setting-modal.admin-settings.edit-link') }}
            </div>

            <div class="flex mt2">
                <!-- Link Name -->
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.community.title') }}
                    </p>
                    <input class="input" :placeholder="$t('community.community.title')" v-model="editedLink.name" />
                </div>
            </div>
            <div class="flex mt1">
                <!-- Link url -->
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.members.setting-modal.admin-settings.url') }}
                    </p>
                    <input class="input" :placeholder="$t('community.community.add-link.description')"
                        v-model="editedLink.url" @keypress="validateUrl($event)" />
                    <div v-if="editedLink.url && !checkUrlValidation(editedLink.url)" class="invalid-url-alert">
                        {{ $t('common.invalid-url-alert') }}
                    </div>
                </div>
            </div>
            <div class="flex mt2">
                <button class="button is-medium community-blue-btn mr-05" :disabled="editConfirm" @click="saveEditLink">
                    {{ $t('common.save') }}
                </button>
                <button class="button is-medium community-btn" @click="close">
                    {{ $t('common.cancel') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>

import validateUrl from "../../../../mixins/util";
import checkUrlValidation from "../../../../mixins/util";
import InputSwitch from "../../../Forms/InputSwitch.vue";

export default {
    name: 'LinkTabSetting',
    mixins: [validateUrl, checkUrlValidation],
    components: {InputSwitch},
    data() {
        return {
            editedLink: null,
            newLink: null,
        };
    },
    computed: {
        /**
         * Returns community
         */
        community() {
            return this.$store.state.community.data;
        },

        /**
         * Returns community links
         */
        links() {
            return this.community.links;
        },

        /**
         * Returns community community settings
         */
        communitySettings() {
            return this.$store.state.communitycenter.communitySettings;
        },

        /**
         * Returns setting link show
         */
        linkShow() {
            return this.communitySettings.linkShow;
        },

        /**
         * Returns add confirm btn status
         */
        addConfirm() {
            let addConfirm = true;
            if (this.newLink.name !== '' && this.newLink.url !== '' && this.checkUrlValidation(this.newLink.url)) {
                addConfirm = false;
            }

            return addConfirm;
        },

        /**
         * Returns edit confirm btn status
         */
        editConfirm() {
            let editConfirm = true;
            if (this.editedLink.name !== '' && this.editedLink.url !== '' && this.checkUrlValidation(this.editedLink.url)) {
                editConfirm = false;
            }

            return editConfirm;
        },

        tabClassroom: {
            get() {
                return this.community.tab_classrooms ? true : false;
            },
            set(v) {
                this.setCommunityProperty('tab_classrooms', v);
            }
        },

        tabCalendar: {
            get() {
                return this.community.tab_calendar ? true : false;
            },
            set(v) {
                this.setCommunityProperty('tab_calendar', v);
            }
        },

        tabLeaderboard: {
            get() {
                return this.community.tab_leaderboard ? true : false;
            },
            set(v) {
                this.setCommunityProperty('tab_leaderboard', v);
            }
        },
    },
    methods: {
        addLink() {
            this.newLink = {
                id: 0,
                community_id: this.community.id,
                name: '',
                url: ''
            };

            this.$store.commit('setCommunitySettingsProperty', {
                key: 'linkShow',
                v: 'add'
            });
        },

        editLink(link) {
            this.editedLink = link;
            this.$store.commit('setCommunitySettingsProperty', {
                key: 'linkShow',
                v: 'edit'
            });
        },

        close() {
            this.$store.commit('setCommunitySettingsProperty', {
                key: 'linkShow',
                v: 'list'
            });
        },

        /**
         * Show or hide the dropdown
         */
        showDropdown(item) {
            return (typeof item.dropdown !== 'undefined' && item.dropdown) ? 'dropdown is-active' : 'dropdown';
        },

        /**
         * Show or hide the dropdown
         */
        toggleDropdown(link) {
            this.$store.commit('toggleLinkDropdownItem', link);
        },

        async deleteLink(link) {
            await this.$store.dispatch('DELETE_LINK', link);
        },

        async saveAddLink() {
            await this.$store.dispatch('SAVE_LINK', this.newLink);
            this.close();
        },

        async saveEditLink() {
            await this.$store.dispatch('SAVE_LINK', this.editedLink);
            this.close();
        },

        setCommunityProperty(key, v) {
            this.$store.commit('setCommunityDataProperty', {
                key: key,
                v
            });
        },

        async saveTabSettings() {
            await this.$store.dispatch('SAVE_COMMUNITY_TABS');
        }
    }
}
</script>

<style scoped>
</style>
