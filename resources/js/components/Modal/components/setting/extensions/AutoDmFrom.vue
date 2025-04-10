<template>
    <div>
        <div class="tab-content-title">
            {{ $t('community.members.setting-modal.admin-settings.advanced.message-will-send-from') }}
        </div>

        <div class="mt1">
            <div
                v-for="(item, index) in fromMembers"
                :key="item.id"
                class="radio-select-item"
                :class="fromSectionClass(item)"
                @click="selectAdminMemberOption(item.id)"
            >
                <div class="flex align-items-center pointer">
                    <input
                        type="radio"
                        :id="item.id"
                        v-model="item.selected"
                        :value="selected"
                        class="mr-05"
                    />

                    <img v-if="item" class="customer-avatar-photo size-34px" :src="getMemberGravatar(item)" alt="gravatar-profile-image">

                    <label class="text-left input-label w100 pointer">
                        {{ getMemberName(item) }}
                    </label>
                </div>
            </div>
        </div>

        <div class="flex mt2">
            <button class="button is-medium community-blue-btn mr-05" @click="goToAutoDm">
                {{ $t('common.change') }}
            </button>
            <button class="button is-medium community-btn" @click="goToAutoDm">
                {{ $t('common.cancel') }}
            </button>
        </div>
    </div>
</template>

<script>

import getMemberName from "../../../../../mixins/util";
import getMemberGravatar from "../../../../../mixins/util";

import { ExtensionViewType } from "../../../../../data/enums";

export default {
    name: 'AutoDmFrom',
    mixins: [
        getMemberName,
        getMemberGravatar
    ],
    data() {
        return {
            selected: 1,
            ExtensionViewType
        };
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
         * Returns requested admin members
         */
        adminMembers() {
            return this.$store.state.communitycenter.adminMembers;
        },


        /**
         * Returns extension
         */
        extension ()
        {
            return this.$store.state.community.extension;
        },

        /**
         * Returns extension
         */
        template ()
        {
            return this.extension?.template;
        },

        /**
         * Returns from members
         */
        fromMembers ()
        {
            let fromMembers = JSON.parse(JSON.stringify(this.adminMembers));
            fromMembers = fromMembers.map(member => {
                member.selected = 0;
                if (this.template && member.id === this.template.member_id) {
                    member.selected = 1;
                }

                return { ...member };
            });

            return fromMembers;
        },
    },
    methods: {

        /**
         * Returns selected from member class
         */
        fromSectionClass (item)
        {
            let fromSectionClass = '';
            if (this.template && item.id === this.template.member_id) {
                fromSectionClass += ' selected-item';
            }

            return fromSectionClass;
        },

        /**
         * Select media option
         */
        selectAdminMemberOption (id)
        {
            this.$store.commit('setAudoDmTemplateProps', {
                key: 'member_id',
                v: id
            });
        },

        /**
         * Go to auto dm page
         */
        goToAutoDm ()
        {
            this.$store.commit('setCommunitySettingsProperty', {
                key: 'extensionShow',
                v: ExtensionViewType.EDIT
            });
        },

        /**
         * Close auto dm from page
         */
        async close ()
        {
            await this.$store.dispatch('GET_EXTENSION', {
                id: item.id
            });

            this.goToAutoDm();
        },

    }
}
</script>

<style scoped>

</style>
