<template>
    <div class="has-text-centered mt1">
        <a v-show="this.current_page > 1"
            class="pagination-previous"
            @click="previousPage"
        >{{ $t('general.previous_page') }}</a>

        <a v-show="this.next_page_url"
            class="pagination-next"
            @click="nextPage"
        >{{ $t('general.next_page') }}</a>
    </div>
</template>

<script>
export default {
    name: 'PaginationButtons',
    props: [
        'current_page',
        'prev_page',
        'next_page',
        'next_page_url',
    ],
    methods: {
        /**
         * Load the previous page of data
         */
        previousPage ()
        {
            this.$store.dispatch(this.prev_page);
        },

        /**
         * Load the next page of data
         */
        nextPage ()
        {
            this.$store.dispatch(this.next_page);
        }
    }
}
</script>

<style scoped>

</style>
