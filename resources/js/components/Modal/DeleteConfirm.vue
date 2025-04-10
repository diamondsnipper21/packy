<template>
    <div class="inner-modal-container flex align-items-center jc">
        <p v-html="description"></p>
    </div>
</template>

<script>

export default {
	name: 'DeleteConfirm',
    data () {
        return {
            processing: false
        };
    },
    computed: {
        /**
         * extra data of child modal
         */
        extraData ()
        {
            return this.$store.state.modal.extraData;
        },

        /**
         * Text to display on the action button
         */
        description ()
        {
            return this.$store.state.modal.description;
        },
    },
}
</script>

<style scoped>

</style>
