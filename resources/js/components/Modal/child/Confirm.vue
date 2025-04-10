<template>
    <div>
        <p class="title is-4 font-weight-600" v-html="title"></p>

        <p class="mt1" v-html="desc"></p>

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
export default {
    name: 'Confirm',
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
         * Return modal description
         */
        desc ()
        {
            return this.extraData.desc;
        },

        /**
         * Return modal action
         */
        action ()
        {
            return this.extraData.action;
        },

        /**
         * Return modal param
         */
        param ()
        {
            return this.extraData.param;
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

            if (this.hideModal) {
                this.$store.commit('hideModal');
            }
            
            this.cancel();
            this.processing = false;
        },
    }
}
</script>

<style computed>

</style>