<template>
    <div>
        <p class="title is-4 font-weight-600">
            {{ title }}
        </p>

        <pre class="mt1 preview-desc" v-html="desc"></pre>

        <div class="flex je mt2">
            <button class="button is-medium" @click="cancel">
                {{ $t('common.cancel') }}
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AutoDmPreview',
    computed: {
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
    },
    methods: {
        cancel() {
            this.$store.commit('hideChildModal');
        },
    }
}
</script>

<style computed>
    .preview-desc {
        text-align: left;
        background-color: #fff;
    }
</style>