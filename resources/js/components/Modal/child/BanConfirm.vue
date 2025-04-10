<template>
    <div class="inner-modal-container">
        <p class="title is-4 font-weight-600" v-html="title"></p>
        <p class="text-left input-label mt1" v-html="descriptionHtml"></p>
        <div class="flex align-items-start mt1">
            <input type="checkbox" v-model="deleteBanChecked" class="cc-checkbox mt-5px" id="delete-ban-checked" />
            <label for="delete-ban-checked" class="pointer text-left" v-html="checkboxHtml"></label>
        </div>

        <div class="flex jc mt2">
            <button class="button is-medium" @click="cancel">
                {{ $t('common.cancel') }}
            </button>
            <button
                class="community-blue-btn ml-05"
                :class="button"
                @click="confirm"
            >{{ buttonText }}</button>
        </div>
    </div>
</template>

<script>

import getMemberName from "../../../mixins/util";

export default {
	name: 'BanConfirm',
    mixins: [
        getMemberName
    ],
    data () {
        return {
            processing: false
        };
    },
    computed: {
        /**
         * Add ' is-loading' when processing
         */
        button ()
        {
            let button = 'button ';

            return (this.processing)
                ? button + ' is-loading'
                : button;
        },

        /**
         * Returns community
         */
        community() {
            return this.$store.state.community.data;
        },

        /**
         * extra data of modal
         */
        extraData ()
        {
            return this.$store.state.modal.childModalExtraData;
        },

        /**
         * Return modal title
         */
        title ()
        {
            return this.extraData.title;
        },

        /**
         * Return modal param
         */
        param ()
        {
            return this.extraData.param;
        },

        /**
         * Return modal action
         */
        action ()
        {
            return this.extraData.action;
        },

        /**
         * Return description html
         */
        descriptionHtml ()
        {
            return this.$t('community.members.ban-from-group-desc').replace('#memberName#', this.getMemberName(this.param));
        },

        /**
         * Return checkbox html
         */
        checkboxHtml ()
        {
            return this.$t('community.members.ban-from-group-checkbox-desc').replace('#memberName#', this.getMemberName(this.param)).replace('#communityName#', this.community.name);
        },

        /**
         * Select/de-select PayPal checkbox
         */
        deleteBanChecked: {
            get () {
                return typeof this.param.deleteBanChecked !== 'undefined' ? this.param.deleteBanChecked : false;
            },
            set (v) {
                this.$store.commit('setMembershipSettingsMemberProperty', {
                    key: 'deleteBanChecked',
                    v
                });
            }
        },

        /**
         * Return modal hideModal
         */
        hideModal ()
        {
            return this.extraData.hideModal;
        },

        /**
         * Text to display on the action button
         */
        buttonText ()
        {
            return typeof this.extraData.buttonText !== 'undefined' ? this.extraData.buttonText : this.$t('common.yes');
        },
    },
    methods: {
        cancel() {
            this.$store.commit('hideChildModal');
        },

        async confirm() {
            this.processing = true;
            await this.$store.dispatch(this.action, this.param);
            
            this.cancel();

            if (this.hideModal) {
                this.$store.commit('hideModal');
            }
            this.processing = false;
        },
    }
}
</script>

<style scoped>

</style>
