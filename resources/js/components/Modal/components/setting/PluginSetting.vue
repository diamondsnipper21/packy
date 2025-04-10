<template>
    <div>
        <div v-if="extensionShow === ExtensionViewType.LIST">
            <div class="flex align-items-center jcb">
                <div class="tab-content-title">
                    {{ $t('community.members.setting-modal.admin-settings.advanced.extensions') }}
                </div>
            </div>

            <div class="mt1">
                <div v-if="extensions.length === 0" class="empty-section">
                    {{ $t('community.members.setting-modal.admin-settings.advanced.empty-features') }}
                </div>
                <div
                    v-for="item, index in extensions"
                    :key="item.id"
                    class="tab-content-item align-items-center"
                >
                    <template v-if="item.extension">
                        <div class="tab-content-item-content">
                            <div class="tab-content-sub-title font-weight-600 max-full-width">
                                {{ item.extension.name }}
                                <span v-if="item.active" class="ml-05 font-weight-600">
                                    ({{ $t('community.members.setting-modal.admin-settings.advanced.on') }})
                                </span>
                                <span v-else class="grey ml-05 font-weight-600">
                                    ({{ $t('community.members.setting-modal.admin-settings.advanced.off') }})
                                </span>
                            </div>
                            <div class="tab-content-sub-desc font-14px pl-0">
                                {{ item.extension.description }}
                            </div>
                        </div>
                        <div class="tab-content-item-action">
                            <button class="button is-medium community-btn mr-05" @click="editExtension(item)">
                                {{ $t('common.edit') }}
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div v-else-if="extensionShow === ExtensionViewType.EDIT">
            <div class="flex align-items-center jcb">
                <div>
                    <div class="tab-content-title">
                        {{ extension.extension.name }}
                    </div>
                    <div class="go-back-link" @click="goToList">
                        <font-awesome-icon icon="fa fa-chevron-left" class="font-12px" /> {{ $t('community.members.setting-modal.admin-settings.advanced.back-to-plugins') }}
                    </div>
                </div>

                <div class="flex align-items-center">
                    <div v-if="extension.active" class="font-14px mr-05">
                        {{ $t('community.members.setting-modal.admin-settings.advanced.on') }}
                    </div>
                    <div v-else class="font-14px mr-05">
                        {{ $t('community.members.setting-modal.admin-settings.advanced.off') }}
                    </div>

                    <Toggle v-model="extension.active" class="toggle-blue" :true-value="1" :false-value="0" />
                </div>                
            </div>

            <AutoDm v-if="extension.extension.type === 'auto_dm'" />

        </div>

        <AutoDmFrom v-else-if="extensionShow === ExtensionViewType.AUTO_DM_FROM" />
    </div>
</template>

<script>

import Toggle from '@vueform/toggle'
import '@vueform/toggle/themes/default.css'

import { ExtensionViewType } from "../../../../data/enums";

import AutoDm from "./extensions/AutoDm";
import AutoDmFrom from "./extensions/AutoDmFrom";

export default {
	name: 'PluginSetting',
    components: {
        Toggle,
        AutoDm,
        AutoDmFrom
    },
    data () {
        return {
            ExtensionViewType,
            fromAdminMember: null,
            defaultTemplate: null,
        };
    },
    created() {
        let adminMembers = JSON.parse(JSON.stringify(this.adminMembers));
        for (let i = 0; i < adminMembers.length; i++) {
            const adminMember = adminMembers[i];
            if (this.community.user_id === adminMember.user_id) {
                this.fromAdminMember = adminMember;
                break;
            }
        }
    },
    computed: {
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
        member() {
            return this.$store.state.member.data;
        },

        /**
         * Returns community extensions
         */
        extensions ()
        {
            return this.community.extensions;
        },

        /**
         * Returns community community settings
         */
        communitySettings ()
        {
            return this.$store.state.communitycenter.communitySettings;
        },

        /**
         * Returns setting advanced feature show
         */
        extensionShow ()
        {
            return this.communitySettings.extensionShow;
        },

        /**
         * Returns requested admin members
         */
        adminMembers ()
        {
            return this.$store.state.communitycenter.adminMembers;
        },

        /**
         * Returns edited extension
         */
        extension ()
        {
            return this.$store.state.community.extension;
        },
    },
	methods: {
        /**
         * Get edit extension info
         */
        async editExtension (item)
        {
            await this.$store.dispatch('GET_EXTENSION', {
                id: item.id
            });
            this.defaultTemplate = {
                'community_id': this.community.id,
                'extension_id': this.extension?.id || 0,
                'member_id': this.fromAdminMember.id,
                'body': this.$t('community.members.setting-modal.admin-settings.advanced.default-template')
            };

            if (typeof this.extension.template === 'undefined' || !this.extension.template) {
                this.$store.commit('setAutoDmTemplate', this.defaultTemplate);
            }

            this.$store.commit('setCommunitySettingsProperty', {
                key: 'extensionShow',
                v: ExtensionViewType.EDIT
            });
        },

        close ()
        {
            this.$store.commit('setCommunitySettingsProperty', {
                key: 'extensionShow',
                v: ExtensionViewType.LIST
            });
        },

        async deleteExtension (item)
        {
            await this.$store.dispatch('DELETE_EXTENSION', item);
        },

        async goToList() {
            await this.$store.dispatch('GET_EXTENSIONS');
            this.close();
        },
	},
}
</script>

<style scoped>
    .show-auto-dm-detail {
        margin-top: 45px;
        font-size: 15px;
        color: rgb(46, 110, 245);
        display: flex;
        align-items: center;
        cursor: pointer;
    }

    .show-auto-dm-detail:hover {
        text-decoration: underline;
    }

    .show-auto-dm-detail .share-link-icon {
        transition-duration: 500ms;
        color: rgb(46, 110, 245);
    }
</style>
