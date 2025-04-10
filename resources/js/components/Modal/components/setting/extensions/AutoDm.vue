<template>
    <div class="mt2">
        <div class="flex align-items-center">
            <div v-if="fromAdminMember" class="flex align-items-center">
                <img class="customer-avatar-photo size-34px" :src="getMemberGravatar(fromAdminMember)" alt="gravatar-profile-image">
            </div>
            <div class="ml-05 font-weight-600">
                {{ $t('community.members.setting-modal.admin-settings.advanced.from') }}:
            </div>
            <div class="ml-05 font-weight-600">
                {{ getMemberName(fromAdminMember) }}
            </div>
            <div class="ml1 go-back-link" @click="changeFromForAutoDm">
                ({{ $t('common.change') }})
            </div>
        </div>

        <!-- Message -->
        <div class="flex mt1">
            <div class="flex-1">
                <p class="text-left input-label">{{ $t('community.members.setting-modal.message') }}</p>
                <textarea class="textarea" :placeholder="$t('community.community.description')" v-model="template.body"
                    rows="5" />
            </div>
        </div>

        <div class="flex mt2">
            <button class="button is-medium community-blue-btn mr-05" @click="saveAutoDmTemplate" :disabled="disabledConfirm">
                {{ $t('common.save') }}
            </button>
            <button class="button is-medium community-btn" @click="preview" :disabled="disabledConfirm">
                {{ $t('common.preview') }}
            </button>
        </div>

        <div class="mt2">

            <div class="mt1">
                {{ $t('community.members.setting-modal.admin-settings.advanced.auto-dm-help-text') }}
            </div>
            <div class="mt1">
                <b>
                    {{ $t('community.members.setting-modal.admin-settings.advanced.tips') }}:
                </b>
                {{ $t('community.members.setting-modal.admin-settings.advanced.auto-dm-help-tips') }}
            </div>
            
        </div>
    </div>
</template>

<script>

import getMemberName from "../../../../../mixins/util";
import getMemberGravatar from "../../../../../mixins/util";

import { ExtensionViewType } from "../../../../../data/enums";

export default {
    name: 'AutoDm',
    mixins: [
        getMemberName,
        getMemberGravatar
    ],
    data() {
        return {
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
         * Returns from admin member
         */
        fromAdminMember ()
        {
            let fromAdminMember = null;
            let adminMembers = JSON.parse(JSON.stringify(this.adminMembers));

            for (let i = 0; i < adminMembers.length; i++) {
                const adminMember = adminMembers[i];
                if (this.template && this.template.member_id === adminMember.id) {
                    fromAdminMember = adminMember;
                    break;
                }
            }

            return fromAdminMember;
        },

        /**
         * Returns from admin member's first name
         */
        firstname ()
        {
            let firstname = '';
            if (typeof this.fromAdminMember.customer !== 'undefined' && this.fromAdminMember.customer !== null) {
                if (this.fromAdminMember.customer.firstname !== null) {
                    firstname = this.fromAdminMember.customer.firstname;
                }
                if (firstname === '' && this.fromAdminMember.customer.lastname !== null) {
                    firstname = this.fromAdminMember.customer.lastname;
                }
            }

            return firstname;
        },

        /**
         * Returns disabled confirm status
         */
        disabledConfirm ()
        {
            return !this.fromAdminMember || this.template.body === '';
        },
    },
    methods: {
        /**
         * Go to auto dm from admin change page
         */
        changeFromForAutoDm ()
        {
            this.$store.commit('setCommunitySettingsProperty', {
                key: 'extensionShow',
                v: ExtensionViewType.AUTO_DM_FROM
            });
        },

        close ()
        {
            this.$store.commit('setCommunitySettingsProperty', {
                key: 'extensionShow',
                v: ExtensionViewType.LIST
            });
        },

        preview ()
        {
            this.$store.commit('showChildModal', {
                modalType: 'AutoDmPreview',
                extraData: {
                    title: this.$t('community.members.setting-modal.admin-settings.advanced.message-will-send-from') + ' ' + this.getMemberName(this.fromAdminMember),
                    desc: this.template.body.replace("[[FIRSTNAME]]", this.firstname).replace("[[COMMUNITYNAME]]", this.community.name)
                }
            });
        },

        /**
         * Save auto dm template
         */
        async saveAutoDmTemplate() {
            await this.$store.dispatch('SAVE_EXTENSION');
            this.close();
        },

    }
}
</script>

<style scoped>

</style>
