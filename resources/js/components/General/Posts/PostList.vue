<template>
    <div ref="postRef">
        <Skeleton height="4em" :count="2" v-if="loading" />
        <div v-else>
            <PostElement v-for="post in posts" :key="post.id" :post="post" :postType="postType" />
            <Pagination v-if="postType === PostType.REGULAR && total" :total="total" :perPage="perPage"
                :current="currentPage" pageAction="GET_COMMUNITY_POSTS" />
        </div>
    </div>
</template>

<script>
import { PostType } from '../../../data/enums';
import PostElement from './PostElement';
import Pagination from '../Elements/Pagination';
import Skeleton from '../Elements/Skeleton';
export default {
    name: 'PostList',
    components: {
        PostElement,
        Pagination,
        Skeleton,
    },
    props: {
        postType: {
            type: Number,
        },
        communityId: {
            type: Number,
        },
    },
    watch: {
        'lastPostNumber': function (v) {
            if (v > 10 && this.$refs.postRef && this.postType == PostType.REGULAR) {
                setTimeout(() => {
                    window.scroll({
                        top: this.$refs.postRef.offsetTop,
                        left: 0,
                        behavior: 'smooth'
                    })
                }, 100)
            }
        },
        'selectedCategoryId': function (n, v) {
            this.loadPosts()
        },
        'selectedFilter': function (n, v) {
            this.loadPosts()
        },
        'selectedSort': function (n, v) {
            this.loadPosts()
        }
    },
    data() {
        return {
            PostType,
        };
    },
    created() {
        this.loadPosts()
    },
    computed: {
        community() {
            return this.$store.state.community.data;
        },

        total() {
            return this.$store.state.post.pagination?.total || 0;
        },

        currentPage() {
            return this.$store.state.post.pagination?.current_page || 0;
        },

        perPage() {
            return this.$store.state.post.pagination?.per_page || 1;
        },

        lastPostNumber() {
            return this.$store.state.post.pagination?.to || 0;
        },

        posts() {
            return this.postType === PostType.SCHEDULE ? this.$store.state.post.scheduledItems : this.$store.state.post.items;
        },

        loading() {
            return this.postType === PostType.SCHEDULE ? this.$store.state.post.scheduledLoading : this.$store.state.post.loading;
        },

        selectedCategoryId() {
            return this.$store.state.post.selectedCategoryId;
        },

        selectedFilter() {
            return this.$store.state.post.selectedFilter;
        },

        selectedSort() {
            return this.$store.state.post.selectedSort;
        },
    },
    methods: {
        async loadPosts() {
            if (this.postType === PostType.SCHEDULE) {
                await this.$store.dispatch('GET_SCHEDULED_POSTS');
            } else {
                await this.$store.dispatch('GET_COMMUNITY_POSTS');
            }
        }
    }
}
</script>
<style lang="scss" scoped></style>