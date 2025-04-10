
/**
 * Generate hierarchical comments
 */
function generateHierarchicalComments(comments, parentComments) {
    if (typeof parentComments === 'undefined') {
        parentComments = [];
        for (let i = 0; i < comments.length; i++) {
            if (comments[i].parent_id === null) {
                parentComments.push(comments[i]);
            }
        }

        generateHierarchicalComments(comments, parentComments);
    } else {
        parentComments.map(parentComment => {
            parentComment.children = [];
            for (let n = 0; n < comments.length; n++) {
                if (parentComment.id === comments[n].parent_id) {
                    parentComment.children.push(comments[n]);
                }
            }

            generateHierarchicalComments(comments, parentComment.children);
        });
    }

    return parentComments;
};

export const mutations = {
    setScheduledPostLoading(state, payload) {
        state.scheduledLoading = payload
    },

    setScheduledPosts(state, payload) {
        state.scheduledItems = payload;
    },

    setPostLoading(state, payload) {
        state.loading = payload
    },

    resetCommunityPost(state, payload) {
        state.data = {
            id: 0,
            member: null,
            title: '',
            content: null,
            path: '',
            medias: [],
            pinned: 0,
            broadcast: 0,
            likes: null,
            category_id: 0,
            commentsCount: 0,
            comments: [],
            hierarchicalComments: [],
            polls: [],
            allowMultipleAnswersChecked: false,
            scheduled: false,
            publish_at: new Date(),
            publish_timezone: '',
            repeat: false,
            repeat_end_at: '',
            repeat_every: '',
            repeat_on: '',
            origin_id: 0,
            send_notification: false
        };
    },

    setPostPagination(state, payload) {
        state.pagination = payload
    },

    /**
     * Set category filter
     */
    setCategoryFilter(state, payload) {
        state.selectedCategoryId = payload;
    },

    /**
     * Set filter for post
     */
    setPostFilter(state, payload) {
        state.selectedFilter = payload;
    },

    /**
     * Set sort for post
     */
    setPostSort(state, payload) {
        state.selectedSort = payload;
    },


    /**
     * Set add post show
     */
    setAddPostShow(state, payload) {
        state.addPostShow = payload;
    },


    /**
     * Set view post mode
     */
    setViewPostMode(state, payload)
    {
        state.viewPostMode = payload;
    },

    /**
     * Set community posts
     *
     * @param state
     * @param payload
     */
    setCommunityPosts(state, payload) {
        state.items = payload;
    },

    /**
     * Set community post
     */
    setCommunityPost(state, payload) {
        const comments = JSON.parse(JSON.stringify(payload.comments || []));
        payload.hierarchicalComments = generateHierarchicalComments(comments);
        state.data = payload;
    },


    /**
     * Set community post property
     */
    setCommunityPostProperty(state, payload) {
        state.data[payload.key] = payload.v;
    },

    /**
     * Reset community post schedule property
     */
    resetCommunityPostScheduleProps(state) {
        state.data.scheduled = false;
        state.data.publish_at = new Date();
        state.data.publish_timezone = state.member?.timezone || '';
        state.data.repeat = false;
        state.data.repeat_end_at = '';
        state.data.repeat_every = '';
        state.data.repeat_on = '';
        state.data.origin_id = 0;
        state.data.send_notification = false;
    },

    /**
     * Set community post media
     */
    setCommunityPostMedia(state, payload) {
        state.data.medias.push(payload);
    },

    /**
    * Removes a post media
    */
    removeCommunityPostMedia(state, payload) {
        state.data.medias = state.data.medias.filter(item => item.path !== payload.path);
    },

    /**
     * Update scheduled posts
     */
    updateScheduledPosts(state, payload) {
        let scheduledItems = JSON.parse(JSON.stringify(state.scheduledItems));
        let existedPost = false;
        scheduledItems = scheduledItems.map((post) => {
            if (post.id === payload.id) {
                post = payload;
                existedPost = true;
            }

            return { ...post };
        });

        if (!existedPost) {
            scheduledItems.unshift(payload);
        }
        scheduledItems.sort((a, b) => b.pinned - a.pinned);
        state.scheduledItems = scheduledItems;
    },

    /**
     * Update community posts
     */
    updateCommunityPosts(state, payload) {
        let communityPosts = JSON.parse(JSON.stringify(state.items));
        let existedPost = false;
        communityPosts = communityPosts.map((post) => {
            if (post.id === payload.id) {
                post = payload;
                existedPost = true;
            }
            return { ...post };
        });

        if (!existedPost) {
            communityPosts.unshift(payload);
        }

        communityPosts.sort((a, b) => b.pinned - a.pinned);

        state.items = communityPosts;
    },

    /**
     * Remove scheduled post
     */
    removeScheduledPost(state, payload) {
        if (state.scheduledItems) {
            let scheduledItems = JSON.parse(JSON.stringify(state.scheduledItems));
            state.scheduledItems = scheduledItems.filter(post => parseInt(post.id) !== parseInt(payload));
        }
    },

    /**
     * Remove post
     */
    removePost(state, payload) {
        let communityPosts = JSON.parse(JSON.stringify(state.items));
        state.items = communityPosts.filter(post => parseInt(post.id) !== parseInt(payload));
    },

    /**
     * Remove comment
     */
    removeComment(state, payload) {
        let comments = JSON.parse(JSON.stringify(state.data.comments));
        comments = comments.filter(comment => parseInt(comment.id) !== parseInt(payload) && parseInt(comment.parent_id) !== parseInt(payload));

        state.data.comments = comments;
        state.data.hierarchicalComments = generateHierarchicalComments(comments);

        let communityPosts = JSON.parse(JSON.stringify(state.items));
        communityPosts = communityPosts.map((post) => {
            if (post.id === state.data.id) {
                post.comments = comments;
            }

            return { ...post };
        });

        state.items = communityPosts;
    },

    /**
     * Update community post comments
     */
    updateCommunityPostComments(state, payload) {
        let comments = JSON.parse(JSON.stringify(state.data.comments));

        let existedComment = false;
        comments = comments.map((comment) => {
            if (comment.id === payload.id) {
                comment = payload;
                existedComment = true;
            } else {
                if (payload.showMode === 'edit') {
                    comment.showMode = 'view';
                }
            }

            return { ...comment };
        });

        if (!existedComment) {
            comments.push(payload);
        }

        state.data.comments = comments;
        state.data.hierarchicalComments = generateHierarchicalComments(comments);

        let communityPosts = JSON.parse(JSON.stringify(state.items));
        communityPosts = communityPosts.map((post) => {
            if (post.id === state.data.id) {
                post.comments = comments;
            }

            return { ...post };
        });

        state.items = communityPosts;
    },


    /**
     * Close reply section
     */
    closeReplySection(state)
    {
        let comments = JSON.parse(JSON.stringify(state.data.comments));
        comments = comments.map((comment) => {
            comment.replyShow = false;

            return { ...comment };
        });

        state.data.comments = comments;
        state.data.hierarchicalComments = generateHierarchicalComments(comments);
    },


    /**
     * Set community post comment
     */
    setCommunityPostComment(state, payload) {
        state.comment = payload;
    },

    /**
     * Set edited comment
     */
    setEditedComment(state, payload) {
        state.editedComment = payload;
    },

    /**
     * Reset community post comment
     */
    resetCommunityPostComment(state, payload) {
        let content = null;
        let parentId = null;
        if (typeof payload !== 'undefined') {
            content = payload.content;
            parentId = payload.parentId;
        }
        state.comment = {
            id: 0,
            member_id: 0, //state.member !== null ? state.member.id : 0,
            post_id: 0, //state.data.id,
            content: content,
            path: '',
            medias: [],
            likes: null,
            parent_id: parentId
        };
    },

    /**
     * Reset edited comment
     */
    resetEditedComment(state) {
        state.editedComment = {
            id: 0,
            member_id: 0, //state.member !== null ? state.member.id : 0,
            post_id: 0, //state.data.id,
            content: null,
            path: '',
            medias: [],
            likes: null,
            parent_id: null
        };
    },

    /**
     * Reset reply comment
     */
    resetReplyComment(state, payload) {
        let content = null;
        let parentId = null;
        if (typeof payload !== 'undefined') {
            content = payload.content;
            parentId = payload.parentId;
        }
        state.replyComment = {
            id: 0,
            member_id: 0, //state.member !== null ? state.member.id : 0,
            post_id: 0, //state.data.id,
            content: content,
            path: '',
            medias: [],
            likes: null,
            parent_id: parentId
        };
    },

    /**
     * set comment's showMode to view
     */
    setCommentShowModeToView(state, payload) {
        let comments = JSON.parse(JSON.stringify(state.data.comments));
        comments = comments.map((comment) => {
            comment.showMode = 'view';

            return { ...comment };
        });

        state.data.comments = comments;
        state.data.hierarchicalComments = generateHierarchicalComments(comments);
    },

    /**
     * Revert edited comment show mode
     */
    revertEditedCommentShow(state, payload) {
        let comments = JSON.parse(JSON.stringify(state.data.comments));
        comments = comments.map((comment) => {
            if (comment.id === payload.id) {
                comment.showMode = 'view';
            }

            return { ...comment };
        });

        state.data.comments = comments;
        state.data.hierarchicalComments = generateHierarchicalComments(comments);
    },

    /**
     * Set community post comment property
     */
    setCommunityPostCommentProperty(state, payload)
    {
        state.comment[payload.key] = payload.v;
    },

    /**
     * Set edited comment property
     */
    setEditedCommentProperty(state, payload)
    {
        state.editedComment[payload.key] = payload.v;
    },

    /**
     * Set community post comment media
     */
    setCommunityPostCommentMedia(state, payload)
    {
        state.comment.medias.push(payload);
    },

    /**
     * Set edited comment media
     */
    setEditedCommentMedia(state, payload)
    {
        state.editedComment.medias.push(payload);
    },

    /**
     * Set reply comment property
     */
    setReplyCommentProperty(state, payload)
    {
        state.replyComment[payload.key] = payload.v;
    },

    /**
     * Set reply comment media
     */
    setReplyCommentMedia(state, payload)
    {
        state.replyComment.medias.push(payload);
    },

    /**
    * Removes a post comment media
    */
    removeCommunityPostCommentMedia(state, payload)
    {
        state.comment.medias = state.comment.medias.filter(item => item.path !== payload.path);
    },

    /**
    * Removes a edited comment media
    */
    removeEditedCommentMedia(state, payload)
    {
        state.editedComment.medias = state.editedComment.medias.filter(item => item.path !== payload.path);
    },

    /**
    * Removes a post reply media
    */
    removeReplyCommentMedia(state, payload)
    {
        state.replyComment.medias = state.replyComment.medias.filter(item => item.path !== payload.path);
    },

    /**
     * Update community post comment like
     */
    updateCommunityPostCommentLike(state, payload)
    {
        let comments = JSON.parse(JSON.stringify(state.data.comments));
        comments = comments.map((comment) => {
            if (comment.id === payload.id) {
                comment.likes = payload.likes;
                comment.number_of_likes = payload.number_of_likes;
                comment.is_member_like = payload.is_member_like;
            }

            return { ...comment };
        });

        state.data.comments = comments;
        state.data.hierarchicalComments = generateHierarchicalComments(comments);
    },

    /**
     * Show reply section
     */
    showReplySection(state, payload)
    {
        let comments = JSON.parse(JSON.stringify(state.data.comments));
        comments = comments.map((comment) => {
            if (comment.id === payload.id) {
                comment.replyShow = true;
            } else {
                comment.replyShow = false;
            }

            return { ...comment };
        });

        state.data.comments = comments;
        state.data.hierarchicalComments = generateHierarchicalComments(comments);
    },

    /**
     * Set parent page of post
     */
    setParentPage(state, payload) {
        state.parentPage = payload;
    },
}