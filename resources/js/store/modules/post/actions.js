import router from '../../../router';
import { notify } from "@kyvg/vue3-notification";
import { MemberRole, PostVisibility, PostType } from '../../../data/enums';

export const actions = {
    async GET_SCHEDULED_POSTS(context) {

        console.log('get_scheduled_posts');

        context.commit('setScheduledPostLoading', true);
        try {
            const { data: result } = await axios.get(`/c/communities/${context.rootState.community.data.id}/schedule-posts`,
                {
                    params: {
                        selectedCategoryId: context.state.selectedCategoryId,
                        selectedFilter: context.state.selectedFilter,
                        selectedSort: context.state.selectedSort
                    }
                });
            context.commit("setScheduledPosts", result.data || []);
        } catch (e) {
            context.commit("setScheduledPosts", []);
        }
        context.commit('setScheduledPostLoading', false);
    },

    async GET_SCHEDULED_POST(context, payload) {
        try {
            const { data: result } = await axios.get(`/c/communities/${payload.communityId}/schedule-posts/${payload.id}`);
            context.commit('setAddPostShow', false);

            const postData = result.data;
            postData.scheduled = true;

            context.commit('setCommunityPost', postData);
            context.commit('showModal', {
                type: 'ViewCommunityPost',
                extraData: PostType.SCHEDULE,
                transparent: true
            });
            context.commit('setViewPostMode', 'view');

            setTimeout(() => {
                if (typeof document.getElementById('post_content_container') !== 'undefined' && document.getElementById('post_content_container') !== null) {
                    var objDiv = document.getElementById('post_content_container');
                    objDiv.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }, 500);
        } catch (e) {
            context.commit("setCommunityPost", {});
        }
    },

    async DELETE_SCHEDULED_POST(context, payload) {
        try {
            await axios.delete(`/c/communities/${payload.communityId}/schedule-posts/${payload.id}`);
            context.commit('removeScheduledPost', payload.id);
        } catch (e) {
            notify({
                text: e.response?.data?.message || 'Unknown Error',
                type: 'error'
            });
        }
    },

    async UPDATE_SCHEDULED_POST(context, payload) {
        const hideModal = payload.hideModal === undefined ? true : payload.hideModal;
        const postData = context.state.data;

        if (payload.sendEmail !== undefined) {
            postData.send_notification = payload.sendEmail;
        }

        postData.polls = (postData.polls || []).map(item => {
            return { ...item, owner: "scheduled_post" }
        })

        postData.medias = (postData.medias || []).map(item => {
            return { ...item, owner: "scheduled_post" }
        })

        postData.mentionedMembers = context.rootState.community.mentionedMembers;

        try {
            const { data: result } = await axios.put(`/c/communities/${payload.communityId}/schedule-posts/${payload.id}`, postData);
            const resultData = result.data;
            context.commit('updateScheduledPosts', resultData);
            context.commit('setCommunityPost', { ...resultData, scheduled: true });

            context.commit('setMentionedMembers', []);

            if (hideModal) {
                context.commit('hideModal');
            }
        } catch (e) {
            notify({
                text: e.response?.data?.message || 'Unknown Error',
                type: 'error'
            });
        }
    },

    async CREATE_SCHEDULED_POST(context, payload) {
        const hideModal = payload.hideModal === undefined ? true : payload.hideModal;
        const postData = context.state.data;

        if (payload.sendEmail !== undefined) {
            postData.send_notification = payload.sendEmail;
        }
        postData.polls = (postData.polls || []).map(item => {
            return { ...item, owner: "scheduled_post" }
        })

        postData.medias = (postData.medias || []).map(item => {
            return { ...item, owner: "scheduled_post" }
        })

        try {
            const { data: result } = await axios.post(`/c/communities/${payload.communityId}/schedule-posts`, postData);
            const resultData = result.data;
            context.commit('updateScheduledPosts', resultData);
            context.commit('setCommunityPost', { ...resultData, scheduled: true });
            if (hideModal) context.commit('hideModal');
            notify({
                text: 'Your scheduled post is created.',
                type: 'success'
            });
        } catch (e) {
            notify({
                text: e.response?.data?.message || 'Unknown Error',
                type: 'error'
            });
        }

    },

    ///// Regular posts /////
    async GET_COMMUNITY_POSTS(context, page) {
        context.commit('setPostLoading', true);
        try {
            const { data: result } = await axios.get(`/c/communities/${context.rootState.community.data.id}/posts`, {
                params: {
                    communityId: context.rootState.community.data.id,
                    role: context.rootState.member.data?.role,
                    memberId: context.rootState.member.data?.id,
                    selectedCategoryId: context.state.selectedCategoryId,
                    selectedFilter: context.state.selectedFilter,
                    selectedSort: context.state.selectedSort,
                    page: page
                }
            });
            const { data: posts, total, current_page, per_page, to } = result.data || {}
            context.commit("setCommunityPosts", posts || []);
            context.commit("setPostPagination", { total, current_page, per_page, to });
        } catch (e) {
            context.commit("setScheduledPosts", []);
            context.commit("setPostPagination", {});
        }
        context.commit('setPostLoading', false);
    },

    async GET_COMMUNITY_POST(context, payload) {
        try {
            const { data: result } = await axios.get(`/c/communities/${payload.communityId}/posts/${payload.path}`, {
                params: {
                    postUrl: payload.path,
                    memberId: context.rootState.member.data?.id
                }
            });
            context.commit('setAddPostShow', false);

            const postData = result.data
            postData.scheduled = false;

            context.commit('setCommunityPost', postData);
            context.commit('resetCommunityPostComment');
            context.commit('resetEditedComment');
            context.commit('resetReplyComment');

            context.commit('showModal', {
                type: 'ViewCommunityPost',
                extraData: PostType.REGULAR,
                transparent: true
            });
            context.commit('setViewPostMode', 'view');

            let redirect = true;
            if (typeof payload.redirect !== 'undefined') {
                redirect = payload.redirect;
            }

            if (redirect) {
                let path = '/' + context.rootState.community.data?.url + '/' + postData.path;
                router.push(path).catch(err => {});
            }

            setTimeout(() => {
                if (typeof document.getElementById('post_content_container') !== 'undefined' && document.getElementById('post_content_container') !== null) {
                    var objDiv = document.getElementById('post_content_container');
                    objDiv.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }, 500);
        } catch (e) {
            context.commit("setCommunityPost", {});
            notify({
                text: e.response.data?.message || 'Unknown error',
                type: 'error'
            });

            let path = '/' + context.state.community.url;
            router.push(path).catch(err => { });
        }
    },

    async DELETE_COMMUNITY_POST(context, payload) {
        try {
            await axios.delete(`/c/communities/${context.rootState.community.data?.id}/posts/${payload.id}`);
            context.commit('removePost', payload.id);
        } catch (e) {
            notify({
                text: e.response?.data?.message || 'Unknown Error',
                type: 'error'
            });
        }
    },

    async UPDATE_COMMUNITY_POST(context, payload) {
        const hideModal = payload.hideModal === undefined ? true : payload.hideModal;
        const postData = context.state.data;
        postData.polls = (postData.polls || []).map(item => {
            return { ...item, owner: "post" }
        })

        postData.medias = (postData.medias || []).map(item => {
            return { ...item, owner: "post" }
        })

        postData.mentionedMembers = context.rootState.community.mentionedMembers;

        let notification = null;
        if (typeof payload.notification !== 'undefined') {
            notification = payload.notification;
        }

        try {
            const { data: result } = await axios.put(`/c/communities/${context.rootState.community.data?.id}/posts/${payload.id}`, postData);
            const resultData = result.data;
            context.commit('updateCommunityPosts', resultData);
            context.commit('setCommunityPost', { ...resultData, scheduled: false });

            context.commit('setMentionedMembers', []);

            if (hideModal) {
                context.commit('hideModal');
            }

            if (notification && typeof notification === 'string') {
                setTimeout(() => {
                    notify({
                        text: notification,
                        type: 'success'
                    });
                }, 100);
            }
        } catch (e) {
            notify({
                text: e.response?.data?.message || 'Unknown Error',
                type: 'error'
            });
        }
    },

    async CREATE_COMMUNITY_POST(context, payload) {
        const hideModal = payload.hideModal === undefined ? true : payload.hideModal;
        const postData = context.state.data;
        const sendEmail = payload.sendEmail || false;
        const role = context.rootState.member.data?.role;
        postData.polls = (postData.polls || []).map(item => {
            return { ...item, owner: "post" }
        })

        postData.medias = (postData.medias || []).map(item => {
            return { ...item, owner: "post" }
        })

        postData.mentionedMembers = context.rootState.community.mentionedMembers;

        if ([MemberRole.OWNER, MemberRole.ADMIN, MemberRole.MODERATOR].includes(role)) {
            postData.sendEmail = sendEmail;
            if (sendEmail) {
                postData.lastSentNotification = context.rootState.community.lastSentNotification;
            }
        }

        try {
            const { data: result } = await axios.post(`/c/communities/${context.rootState.community.data?.id}/posts`, postData);

            if (result.success) {
                const post = result.post;
                context.commit('updateCommunityPosts', post);
                context.commit('setCommunityPost', { ...post, scheduled: false });

                context.commit('setMentionedMembers', []);

                if (sendEmail) {
                    const community = result.community;
                    context.commit('updateCommunities', community);
                    context.commit('setCommunityLastSentNotification', context.rootState.community.lastSentNotification);
                }

                notify({
                    text: 'Your post is created.',
                    type: 'success'
                });

                if (hideModal) {
                    context.commit('hideModal');
                }
            } else {
                if (sendEmail) {
                    context.commit('setCommunityLastSentNotification', null);
                    context.commit('setCommunityBroadcast', 0);
                }

                notify({
                    text: result.message || 'Unknown Error',
                    type: 'error'
                });
            }
                
        } catch (e) {
            notify({
                text: e.response?.data?.message || 'Unknown Error',
                type: 'error'
            });
        }
    },

    async LIKE_COMMUNITY_POST(context, payload) {
        const postData = context.state.data;
        try {
            const { data: result } = await axios.post(`/c/communities/${context.rootState.community.data?.id}/posts/${payload.id}/like`, {});
            postData.number_of_likes = result.data.number_of_likes;
            postData.is_member_like = result.data.is_member_like;
            context.commit('updateCommunityPosts', postData);
        } catch (e) {
            notify({
                text: e.response?.data?.message || 'Unknown Error',
                type: 'error'
            });
        }
    },

    async VOTE_COMMUNITY_POST(context, payload) {
        const postData = context.state.data;
        try {
            await axios.post(`/c/communities/${context.rootState.community.data?.id}/posts/${payload.postId}/vote-poll`, {
                poll: payload.votedPoll
            });
            context.commit('updateCommunityPosts', postData);
        } catch (e) {
            notify({
                text: e.response?.data?.message || 'Unknown Error',
                type: 'error'
            });
        }
    },

    /**
     * Approve post request
     */
    async APPROVE_POST_REQUEST(context, payload) {
        try {
            await axios.post(`/c/communities/${context.rootState.community.data?.id}/posts/${payload}/approve`, {});
            const postData = context.state.items.find(item => item.id == payload)
            postData.visibility = PostVisibility.APPROVED
            context.commit('updateCommunityPosts', postData);
        } catch (e) {
            notify({
                text: e.response?.data?.message || 'Unknown Error',
                type: 'error'
            });
        }
    },

    /**
     * Decline post request
     */
    async DECLINE_POST_REQUEST(context, payload) {
        try {
            await axios.post(`/c/communities/${context.rootState.community.data?.id}/posts/${payload}/decline`, {});
            context.commit('removePost', payload);
        } catch (e) {
            notify({
                text: e.response?.data?.message || 'Unknown Error',
                type: 'error'
            });
        }
    },

    /**
     * Close post from parent page
     */
    async CLOSE_POST_FROM_PARENT(context, payload) {
        try {
            const { data: result } = await axios.post(`/c/communities/${context.rootState.community.data?.id}/posts/${payload.postId}/closeFromPage`, {
                pageId: payload.parentId
            });
            if (result.success) {
                let posts = JSON.parse(JSON.stringify(context.rootState.classroom.selectedLesson.posts));
                posts = posts.filter(elem => elem.id !== payload.postId);
                context.commit("setSelectedLessonProp", {
                    key: 'posts',
                    v: posts
                });

                context.commit('setCloneLessonPosts', posts);
            }
        } catch (e) {
            notify({
                text: e.response?.data?.message || 'Unknown Error',
                type: 'error'
            });
        }
    },

    /////////// Comment ////////////////
    async CREATE_POST_COMMENT(context, payload) {
        try {
            payload.mentionedMembers = context.rootState.community.mentionedMembers;

            const { data: result } = await axios.post(
                `/c/communities/${context.rootState.community.data?.id}/posts/${context.state.data.id}/comments`,
                payload
            );

            if (result.success) {
                context.commit('updateCommunityPostComments', result.data);
                context.commit('resetCommunityPostComment');
                context.commit('resetEditedComment');
                context.commit('resetReplyComment');
                context.commit('closeReplySection');

                const postData = context.state.items.find(item => item.id == context.state.data.id);

                if (postData) {
                    postData.commentsCount = postData.commentsCount + 1;
                    context.commit('updateCommunityPosts', postData);
                    context.commit('setMentionedMembers', []);
                }
            } else {
                notify({
                    text: result.message || 'Unknown Error',
                    type: 'error'
                });
            }
        } catch (e) {
            notify({
                text: e.response?.data?.message || 'Unknown Error',
                type: 'error'
            });
        }
    },

    async UPDATE_POST_COMMENT(context, payload) {
        try {
            payload.mentionedMembers = context.rootState.community.mentionedMembers;

            const { data: result } = await axios.put(
                `/c/communities/${context.rootState.community.data?.id}/posts/${context.state.data.id}/comments/${payload.id}`,
                payload
            );
            context.commit('updateCommunityPostComments', result.data);
            context.commit('resetCommunityPostComment');
            context.commit('resetEditedComment');
            context.commit('resetReplyComment');
            context.commit('closeReplySection');

            context.commit('setMentionedMembers', []);
        } catch (e) {
            notify({
                text: e.response?.data?.message || 'Unknown Error',
                type: 'error'
            });
        }
    },

    async LIKE_POST_COMMENT(context, payload) {
        try {
            const { data: result } = await axios.post(
                `/c/communities/${context.rootState.community.data?.id}/posts/${payload.post_id}/comments/${payload.id}/like`,
                payload
            );

            payload.number_of_likes = result.data.number_of_likes;
            payload.is_member_like = result.data.is_member_like;
            context.commit('updateCommunityPostCommentLike', payload);
        } catch (e) {
            notify({
                text: e.response?.data?.message || 'Unknown Error',
                type: 'error'
            });
        }
    },

    async DELETE_POST_COMMENT(context, payload) {
        try {
            await axios.delete(
                `/c/communities/${context.rootState.community.data?.id}/posts/${context.state.data.id}/comments/${payload}`,
                {}
            );
            context.commit('removeComment', payload);
            const postData = context.state.items.find(item => item.id == context.state.data.id);
            postData.commentsCount = postData.commentsCount + 1;
            context.commit('updateCommunityPosts', postData);
        } catch (e) {
            notify({
                text: e.response?.data?.message || 'Unknown Error',
                type: 'error'
            });
        }
    },

}