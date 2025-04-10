import router from '../../../router';
import moment from 'moment';
import { notify } from "@kyvg/vue3-notification";
import { MemberAccess, TrackedPages } from '../../../data/enums';
import {loadStripe} from "@stripe/stripe-js";

export const actions = {

    /**
     * Block chat user
     */
    async BLOCK_CHAT_USER(context, payload) {
        console.log('block_chat_user', payload);

        await axios.post("/block-user", payload).then(async response => {
            console.log('block_chat_user response', response);

            if (response.data.success) {
                await context.dispatch('GET_CHAT_USERS');
                notify({
                    text: response.data.message,
                    type: 'success'
                });
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.log('block_chat_user error', error);
        });
    },

    /**
     * Unblock chat user
     */
    async UNBLOCK_CHAT_USER(context, payload) {
        console.log('unblock_chat_user', payload);

        await axios.post("/unblock-user", payload).then(async response => {
            console.log('unblock_chat_user response', response);

            if (response.data.success) {
                await context.dispatch('GET_CHAT_USERS');
                notify({
                    text: response.data.message,
                    type: 'success'
                });
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.log('unblock_chat_user error', error);
        });
    },

    /**
     * Update user profile
     */
    async UPDATE_PROFILE(context) {
        let user = context.rootState.auth.user;
        if (typeof context.rootState.community.data.id !== 'undefined') {
            user.communityId = context.rootState.community.data.id;
        }

        await axios.post('/profile/update', user).then(async response => {
            console.log('update_profile response', response.data);

            if (response.data.success) {
                if (response.data.member) {
                    context.commit("setComMember", response.data.member);

                    let members = JSON.parse(JSON.stringify(context.rootState.communitycenter.allowedMembers));
                    members = members.map((item) => {
                        if (item.id === response.data.member.id) {
                            item = response.data.member;
                        }
                        return { ...item };
                    });

                    context.commit('setAllowedMembers', members);
                }
                context.commit('hideModal');

                notify({
                    text: response.data.message,
                    type: 'success'
                });
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.log('update_profile error', error);
        });
    },

    /**
     * Get data for community
     */
    async GET_COMMUNITY(context, payload) {
        console.log('get_community', payload);

        context.commit('communityLoading', true);

        try {
            const communityData = await context.dispatch('GET_COMMUNITY_DATA', { url: payload });

            let memberData = null;
            if (context.rootState.auth.loggedIn) {
                memberData = await context.dispatch('GET_MEMBER', { url: payload });
                await context.dispatch('GET_MEMBER_COMMUNITIES');
                
                if (memberData && communityData && communityData?.user_id === memberData?.user_id) {
                    await context.dispatch('GET_COMMUNITY_PLAN', { id: communityData.id });
                }
            }

            await context.dispatch('GET_COMMUNITY_EVENTS');

            const response = await axios.post("/get", { url: payload })
            console.log('get_community response', response);

            if (response.data.success) {
                context.commit('setUser', response.data.user);
                context.commit('setStripeConnected', response.data.stripeConnected);
                context.commit('setStripeLoginLink', response.data.stripeLoginLink);

                context.commit('setUnreadNotificationsCnt', response.data.unreadNotificationsCnt);
                context.commit('setUnreadChatsCnt', response.data.unreadChatsCnt);
                context.commit('setMembersCount', response.data.membersCount);

                let affSettings = {
                    paypal: '',
                    iban: '',
                    lang: 'fr',
                    paypalSelected: false,
                    ibanSelected: false
                };

                context.commit('initCommunityData', {
                    wolfeoLoginUrl: response.data.wolfeoLoginUrl,
                    neededPoints: response.data.neededPoints,
                    affSettings
                });

                if (response.data.members) {
                    context.commit('setBannedMembers', response.data.members.banned);
                    context.commit('setPendingMembers', response.data.members.pending);
                    context.commit('setAllowedMembers', response.data.members.allowed);
                    context.commit('setAdminMembers', response.data.members.admin);
                    context.commit('setModeratorMembers', response.data.members.moderator);
                }

                localStorage.setItem('communityUrl', payload);

                let memberAccess = memberData?.access || null;
                let tab = 'community';
                if (communityData.privacy === 'private' && memberAccess !== MemberAccess.ALLOWED) {
                    tab = 'about';
                }

                let incubateurMemberExist = memberData?.incubateurMemberExist || false;
                if (!incubateurMemberExist) {
                    let params = router.currentRoute._value.fullPath.split("/");
                    if (typeof params[1] !== 'undefined' && params[1] === payload && typeof params[2] !== 'undefined') {
                        if (payload.toUpperCase() === 'INCUBATEUR' && params[2] === 'start') {
                            tab = 'start';
                        }
                    }
                }

                let path = '/' + payload + '/' + tab;
                router.push(path).catch(err => {});

                context.commit('setCommunityTab', tab);
                
                /*
                if (context.rootState.auth.user?.id) {
                    context.commit('showModal', {
                        type: 'CompleteProfile',
                        transparent: true
                    });
                }
                */

                context.commit('communityLoading', false);

                if (response.data.notify) {
                    notify({
                        title: response.data.notify.title,
                        text: response.data.notify.text,
                        type: response.data.notify.type
                    });
                }
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        } catch (e) {
            console.log('get_community error', e);
        }
        context.commit('communityLoading', false);
    },

    /**
     * Save temp community
     */
    async SAVE_TEMP_COMMUNITY(context) {
        await axios.post('/temp/save', context.state.newCommunity).then(response => {
            console.log('save_temp_community response', response);

            if (response.data.success) {
                context.commit('hideModal');
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('save_temp_community error', errors.response.data);
        });
    },

    /**
     * Get community events
     */
    async GET_COMMUNITY_EVENTS(context) {
        await axios.post("/events/get", {
            communityId: context.rootState.community.data.id
        }).then(response => {
            console.log('get_community_events', response);

            if (response.data.success) {
                context.commit('setCommunityEvents', response.data.communityEvents);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.log('error.get_community_events', error);
        });
    },

    /**
     * Get monthly events
     */
    async GET_MONTHLY_EVENTS(context, page) {
        await axios.post("/monthly-events/get", {
            communityId: context.rootState.community.data.id,
            eventMonth: context.state.eventMonth,
            page
        }).then(response => {
            console.log("get_monthly_events", response.data);

            if (response.data.success) {
                context.commit('setMonthlyEvents', response.data.monthlyEvents);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.log('get_monthly_events', error);
        });
    },

    /**
     * Get event information
     */
    async GET_COMMUNITY_EVENT(context, payload) {
        await axios.post('/event/get', {
            communityId: context.rootState.community.data.id,
            id: payload
        }).then(response => {
            console.log('get_event_info response', response);

            if (response.data.success) {
                context.commit('setCommunityEvent', response.data.communityEvent);

                context.commit('showModal', {
                    type: 'ViewCommunityEvent',
                    transparent: true
                });
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('get_event_info error', errors.response.data);
        });
    },

    /**
     * Get community members
     */
    async GET_PAGINATED_COMMUNITY_MEMBERS(context, page) {
        await axios.post("/members/get", {
            communityId: context.rootState.community.data.id,
            memberFilter: context.state.memberFilter,
            searchFilter: context.state.searchMemberFilter,
            page
        }).then(response => {
            console.log("get_community_members", response.data);

            if (response.data.success) {
                context.commit('setPaginatedMembers', response.data.paginatedMembers);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.log('get_community_members', error);
        });
    },

    /**
     * Join to community
     */
    async JOIN_TO_COMMUNITY(context, payload) {
        console.log('community', context.rootState.community.data.price_id)

        let communityUrl = context.rootState.community.data?.url || '';
        communityUrl = communityUrl.toUpperCase();

        let access = context.rootState.member.data?.access || 0;
        let incubateurStart = false;
        if (typeof payload !== 'undefined' && typeof payload.incubateurStart !== 'undefined') {
            incubateurStart = payload.incubateurStart;
        }

        if (access === MemberAccess.BANNED) {
            notify({
                text: 'You cannot join this community because you have been banned from it.',
                type: 'error'
            });
            return;
        }

        if (incubateurStart === false && context.rootState.community.data.price_id !== null) {
            await context.dispatch('GET_SUBSCRIPTION_VAT_RATE')
            context.commit('showModal', {
                type: 'SubscriptionPurchase',
                transparent: true
            });
            return;
        }
        
        if (!incubateurStart && communityUrl === 'INCUBATEUR' && access === MemberAccess.DECLINE) {
            // For only 'INCUBATEUR' community, if member has active subscription, then will be redirect to incubateur/start page
            let path = '/' + context.rootState.community.data?.url + '/start';
            router.push(path).catch(err => { });
        } else {
            let userId = context.rootState.auth.user.id;

            await axios.post('/member/join', {
                communityId: context.rootState.community.data.id,
                userId,
                incubateurStart
            }).then(response => {
                console.log('join_to_community response', response);

                if (response.data.success) {
                    context.commit("setComMember", response.data.member);

                    context.commit('setBannedMembers', response.data.members.banned);
                    context.commit('setPendingMembers', response.data.members.pending);
                    context.commit('setAllowedMembers', response.data.members.allowed);
                    context.commit('setAdminMembers', response.data.members.admin);
                    context.commit('setModeratorMembers', response.data.members.moderator);

                    if (incubateurStart) {
                        let tab = 'community';
                        let path = '/' + context.rootState.community.data?.url + '/' + tab;
                        router.push(path).catch(err => { });
                        context.commit('setCommunityTab', tab);
                    } else {
                        context.commit('showModal', {
                            type: 'Pending',
                            transparent: true
                        });
                    }
                } else {
                    notify({
                        text: response.data.message,
                        type: 'error'
                    });
                }
            }).catch(errors => {
                console.error('join_to_community error', errors.response.data);
            });
        }
    },

    /**
     * Leave from community
     */
    async LEAVE_FROM_COMMUNITY(context)
    {
        let data = {
            communityId: context.rootState.community.data.id,
            memberId: context.rootState.member.data.id
        }

        console.log('leave_from_community', data)

        await axios.post('/member/leave', data).then(response => {
            console.log('leave_from_community response', response);

            if (response.data.success) {
                context.commit('setMemberAccess', null);
                if (response.data.member) {
                    context.commit('setMember', response.data.member);
                }
                
                context.commit('setBannedMembers', response.data.members.banned);
                context.commit('setPendingMembers', response.data.members.pending);
                context.commit('setAllowedMembers', response.data.members.allowed);
                context.commit('setAdminMembers', response.data.members.admin);
                context.commit('setModeratorMembers', response.data.members.moderator);
                context.commit('hideModal');

                let params = router.currentRoute._value.fullPath.split("/");
                let tab = 'community';
                if (context.rootState.community.data.privacy === 'private') {
                    tab = 'about';
                }

                if (typeof params[1] !== 'undefined') {
                    if (params[1].toUpperCase() === 'INCUBATEUR' && typeof params[2] !== 'undefined' && params[2] === 'start') {
                        tab = 'start';
                    }

                    let path = '/' + params[1] + '/' + tab;
                    router.push(path).catch(err => {});
                    context.commit('setCommunityTab', tab);
                }

                notify({
                    text: response.data.message,
                    type: 'error'
                });
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('leave_from_community error', errors.response.data);
        });
    },

    /**
     * Move up rule
     */
    async MOVE_UP_RULE(context, payload) {
        await axios.post('/rule/move-up', payload).then(response => {
            console.log('move_up_rule response', response);

            if (response.data.success) {
                context.commit('updateCommunityRules', response.data.rules);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('move_up_rule error', errors.response.data);
        });
    },

    /**
     * Move down rule
     */
    async MOVE_DOWN_RULE(context, payload) {
        await axios.post('/rule/move-down', payload).then(response => {
            console.log('move_down_rule response', response);

            if (response.data.success) {
                context.commit('updateCommunityRules', response.data.rules);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('move_down_rule error', errors.response.data);
        });
    },

    /**
     * Save rule
     */
    async SAVE_RULE(context, payload) {
        await axios.post('/rule/save', payload).then(response => {
            console.log('save_rule response', response);

            if (response.data.success) {
                context.commit('updateCommunityRules', response.data.rules);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('save_rule error', errors.response.data);
        });
    },

    /**
     * Delete rule
     */
    async DELETE_RULE(context, payload) {
        await axios.post('/rule/delete', payload).then(response => {
            console.log('delete_rule response', response);

            if (response.data.success) {
                context.commit('updateCommunityRules', response.data.rules);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('delete_rule error', errors.response.data);
        });
    },

    /**
     * Save category
     */
    async SAVE_CATEGORY(context, payload) {
        await axios.post('/category/save', payload).then(response => {
            console.log('save_category response', response);

            if (response.data.success) {
                context.commit('updateCommunityCategories', response.data.categories);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('save_category error', errors.response.data);
        });
    },

    /**
     * Delete category
     */
    async DELETE_CATEGORY(context, payload) {
        await axios.post('/category/delete', payload).then(response => {
            console.log('delete_category response', response);

            if (response.data.success) {
                context.commit('updateCommunityCategories', response.data.categories);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('delete_category error', errors.response.data);
        });
    },

    /**
     * Get community events
     */
    async GET_EXTENSIONS(context) {
        await axios.post("/extensions/get", {
            communityId: context.rootState.community.data.id
        }).then(response => {
            console.log('get_extensions', response);

            if (response.data.success) {
                context.commit('updateExtensions', response.data.extensions);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.log('error.get_extensions', error);
        });
    },

    async GET_EXTENSION(context, payload) {
        await axios.post("/extension/get", {
            communityId: context.rootState.community.data.id,
            id: payload.id
        }).then(response => {
            console.log("get_one_extension", response.data);

            if (response.data.success) {
                context.commit('setExtension', response.data.extension);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.log('get_one_extension', error);
        });
    },

    /**
     * Save extension
     */
    async SAVE_EXTENSION(context) {
        await axios.post('/extensions/save', context.rootState.community.extension).then(response => {
            console.log('save_extension response', response);

            if (response.data.success) {
                context.commit('updateExtensions', response.data.extensions);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('save_extension error', errors.response.data);
        });
    },

    /**
     * Delete extension
     */
    async DELETE_EXTENSION(context, payload) {
        await axios.post('/extensions/delete', payload).then(response => {
            console.log('delete_extension response', response);

            if (response.data.success) {
                context.commit('updateExtensions', response.data.extensions);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('delete_extension error', errors.response.data);
        });
    },
    
    /**
     * Save link
     */
    async SAVE_LINK(context, payload) {
        await axios.post('/link/save', payload).then(response => {
            console.log('save_link response', response);

            if (response.data.success) {
                context.commit('updateCommunityLinks', response.data.links);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('save_link error', errors.response.data);
        });
    },

    /**
     * Delete link
     */
    async DELETE_LINK(context, payload) {
        await axios.post('/link/delete', payload).then(response => {
            console.log('delete_link response', response);

            if (response.data.success) {
                context.commit('updateCommunityLinks', response.data.links);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('delete_link error', errors.response.data);
        });
    },

    /**
     * Approve member request
     */
    async APPROVE_MEMBER_REQUEST(context, payload) {
        await axios.post(`/c/communities/${context.rootState.community.data.id}/member/request/approve`, {
            communityId: context.rootState.community.data.id,
            adminId: context.rootState.member.data !== null ? context.rootState.member.data.id : 0,
            memberId: payload
        }).then(response => {
            console.log("approve_member_request", response.data);

            if (response.data.success) {
                context.commit('setPaginatedMembers', response.data.paginatedMembers);

                context.commit('setBannedMembers', response.data.members.banned);
                context.commit('setPendingMembers', response.data.members.pending);
                context.commit('setAllowedMembers', response.data.members.allowed);
                context.commit('setAdminMembers', response.data.members.admin);
                context.commit('setModeratorMembers', response.data.members.moderator);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.log('approve_member_request', error);
        });
    },

    /**
     * Decline member request
     */
    async DECLINE_MEMBER_REQUEST(context, payload) {
        await axios.post(`/c/communities/${context.rootState.community.data.id}/member/request/decline`, {
            communityId: context.rootState.community.data.id,
            adminId: context.rootState.member.data !== null ? context.rootState.member.data.id : 0,
            memberId: payload.memberId,
            feedback: payload.feedback,
            shareNotify: payload.shareNotify
        }).then(response => {
            console.log("decline_member_request", response.data);

            if (response.data.success) {
                context.commit('setPaginatedMembers', response.data.paginatedMembers);

                context.commit('setBannedMembers', response.data.members.banned);
                context.commit('setPendingMembers', response.data.members.pending);
                context.commit('setAllowedMembers', response.data.members.allowed);
                context.commit('setAdminMembers', response.data.members.admin);
                context.commit('setModeratorMembers', response.data.members.moderator);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.log('decline_member_request', error);
        });
    },

    /**
     * Save calendar event
     */
    async SAVE_EVENT(context) {
        context.state.communitySettings.calendarEvent.eventMonth = context.state.eventMonth;
        await axios.post('/event/save', context.state.communitySettings.calendarEvent).then(async response => {
            console.log('save_event response', response);

            if (response.data.success) {
                context.commit('setCommunityEvents', response.data.events);

                await context.dispatch('GET_MONTHLY_EVENTS');
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('save_event error', errors.response.data);
        });
    },

    /**
     * Delete calendar event
     */
    async DELETE_EVENT(context, payload) {
        await axios.post('/event/delete', payload).then(async response => {
            console.log('delete_event response', response);

            if (response.data.success) {
                context.commit('setCommunityEvents', response.data.events);

                await context.dispatch('GET_MONTHLY_EVENTS');
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('delete_event error', errors.response.data);
        });
    },

    /**
     * Send invite
     */
    async SEND_INVITE(context, payload) {
        await axios.post('/invite/send', {
            communityId: context.rootState.community.data.id,
            memberId: context.rootState.member.data.id,
            email: payload
        }).then(response => {
            console.log('send_invite response', response);

            if (response.data.success) {
                context.commit('hideModal');

                notify({
                    text: response.data.message,
                    type: 'success'
                });
            }
        }).catch(errors => {
            console.error('send_invite error', errors.response.data);
        });
    },

    /**
     * Get data for invite community
     */
    async GET_INVITE_COMMUNITY(context, payload) {
        await axios.post("/invite/get", payload).then(async response => {
            console.log('get_invite_community', response);

            if (response.data.success) {
                let validUrl = payload.communityUrl;
                context.commit('setValidUrl', validUrl);
                localStorage.setItem('communityUrl', validUrl);

                await context.dispatch('GET_COMMUNITY', validUrl);

                context.commit('setInviteReferMember', response.data.member);

                context.commit('showModal', {
                    type: 'InviteUser',
                    transparent: true
                });
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.log('error.get_invite_community', error);
        });
    },

    /**
     * Get data for invite share link
     */
    async GET_INVITE_SHARE_LINK(context) {
        await axios.post("/invite-share-link/get", {
            communityId: context.rootState.community.data.id,
            memberId: context.rootState.member.data.id
        }).then(async response => {
            console.log('get_invite_share_link', response);

            if (response.data.success) {
                context.commit('setInviteShareLink', response.data.link);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.log('error.get_invite_share_link', error);
        });
    },

    /**
     * Get notifications
     */
    async GET_NOTIFICATIONS(context, payload) {
        await axios.post('/notifications/get', {
            communityId: context.rootState.community.data.id,
            memberId: context.rootState.member.data.id,
            filter: context.state.notificationFilter
        }).then(response => {
            console.log('get_notifications response', response);

            if (response.data.success) {
                context.commit('setNotifications', response.data.notifications);

                const notifications = response.data.notifications
                let unreadNotificationsCnt = 0;
                if (notifications.length > 0) {
                    let unreadNotifications = notifications.filter(el => el.read_at === null);
                    unreadNotificationsCnt = unreadNotifications.length;
                }
                context.commit('setUnreadNotificationsCnt', unreadNotificationsCnt);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('get_notifications error', errors.response.data);
        });
    },

    /**
     * Mark notification as read
     */
    async MARK_NOTIFICATION_AS_READ(context, payload) {
        let id = 0;
        let objectType = '';
        let objectId = 0;
        let showDetail = false;
        if (typeof payload !== 'undefined') {
            id = payload.id;
            objectType = payload.objectType;
            objectId = payload.objectId;
            showDetail = payload.showDetail;
        }

        await axios.post('/notification/mark-as-read', {
            communityId: context.rootState.community.data.id,
            memberId: context.rootState.member.data.id,
            id
        }).then(async response => {
            console.log('mark_notification_as_read response', response);

            if (response.data.success) {
                if (showDetail) {
                    if (objectType === 'mention_in_chat') {
                        await context.dispatch('GET_CHAT_FROM_ID', {
                            communityId: context.rootState.community.data.id,
                            chatId: objectId,
                            showDetail: true
                        });
                    } else if (response.data.redirectUrl) {
                        await context.dispatch('GET_COMMUNITY_POST', {
                            path: response.data.redirectUrl,
                            communityId: context.rootState.community.data.id
                        });
                    }

                    // if (response.data.type === 'like_to_comment' || response.data.type === 'dislike_to_comment' || response.data.type === 'reply_to_comment') {
                    //     setTimeout(() => {
                    //         var objDiv = document.getElementById("community_modal_container");
                    //         objDiv.scrollTop = objDiv.scrollHeight / 2;
                    //     }, 1000);
                    // }
                }

                await context.dispatch('GET_NOTIFICATIONS');
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('mark_notification_as_read error', errors.response.data);
        });
    },

    /**
     * Get chat users
     */
    async GET_CHAT_USERS(context) {
        await axios.post('/chat-users/get', {
            communityId: context.rootState.community.data.id,
            chatUserFilter: context.state.chatUserFilter,
            searchFilter: context.state.searchUserFilter
        }).then(response => {
            console.log('get_chat_users response', response);

            if (response.data.success) {
                context.commit('setChatUsers', { users: response.data.users, userId: context.rootState.auth.user.id });
                context.commit('setBlockedUserIds', response.data.blockedUserIds);
                context.commit('setUnreadChatsCnt', response.data.unreadChatsCnt);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('get_chat_users error', errors);
        });
    },

    /**
     * Get one chat
     */
    async GET_CHAT_FROM_ID(context, payload) {
        let showDetail = false;
        if (typeof payload !== 'undefined') {
            if (typeof payload.showDetail !== 'undefined') {
                showDetail = payload.showDetail;
            }
        }

        await axios.post('/chat/get', payload).then(async response => {
            console.log('get_chat_from_id response', response);

            if (response.data.success) {
                await context.dispatch('GET_CHAT_DETAIL', {
                    communityId: context.rootState.community.data.id,
                    fromId: response.data.chat.from_id,
                    toId: response.data.chat.to_id,
                    showDetail
                });
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('get_chat_from_id error', errors.response.data);
        });
    },

    /**
     * Get chat users results according to search filter
     */
    async GET_CHAT_DETAIL(context, payload) {
        let toId = 0;
        let showDetail = false;
        if (typeof payload !== 'undefined') {
            if (typeof payload.toId !== 'undefined') {
                toId = payload.toId;
            }
            if (typeof payload.showDetail !== 'undefined') {
                showDetail = payload.showDetail;
            }
        }

        await axios.post('/chat-detail/get', payload).then(async response => {
            console.log('get_chat_detail response', response);

            if (response.data.success) {
                context.commit('setChatMessages', response.data.messages);
                context.commit('setChatOpponentUser', response.data.user);

                if (showDetail) {
                    context.commit('resetNewChat');
                    context.commit('setChatToId', toId);
                    context.commit('setModalSize', 'large');
                    context.commit('showModal', {
                        type: 'ChatDetail',
                        transparent: true
                    });

                    setTimeout(() => {
                        var objDiv = document.getElementById("chat_detail_content");
                        objDiv.scrollTo({ top: objDiv.scrollHeight, behavior: 'smooth' });
                    }, 500);
                }
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('get_chat_detail error', errors.response.data);
        });
    },

    /**
     * Save new chat message
     */
    async SAVE_NEW_CHAT_MESSAGE(context) {
        await axios.post('/chat-message/save', {
            communityId: context.rootState.community.data.id,
            memberId: context.rootState.member.data?.id,
            fromId: context.rootState.auth.user.id,
            toId: context.state.chatToId,
            content: context.state.newChat.content,
            mentionedMembers: context.rootState.community.mentionedMembers,
            medias: context.state.newChat.medias
        }).then(response => {
            console.log('save_new_chat_message response', response);

            if (response.data.success) {
                context.commit('addNewChatMessage', response.data.message);
                context.commit('setMentionedMembers', []);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('save_new_chat_message error', errors.response.data);
        });
    },

    /**
     * Add new chat message
     */
    async ADD_NEW_CHAT_MESSAGE(context, payload) {
        await axios.post('/chat-message/get', payload).then(response => {
            console.log('get_chat_message response', response);

            if (response.data.success) {
                context.commit('addNewChatMessage', response.data.message);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('get_chat_message error', errors.response.data);
        });
    },

    /**
     * Mark chat all as read
     */
    async MARK_CHAT_ALL_AS_READ(context, payload) {
        await axios.post('/chat/mark-all-as-read', {
            communityId: context.rootState.community.data.id
        }).then(async response => {
            console.log('mark_chat_all_as_read response', response);

            if (response.data.success) {
                await context.dispatch('GET_CHAT_USERS');
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('mark_chat_all_as_read error', errors.response.data);
        });
    },

    /**
     * Get paginated users
     */
    async GET_PAGINATED_USERS(context, page) {
        await axios.post("/users/get", {
            search: context.state.paginatedUserSearch,
            page
        }).then(response => {
            console.log("get_paginated_users", response.data);

            if (response.data.success) {
                context.commit('setPaginatedUsers', response.data.paginatedUsers);
                context.commit('setCommunities', response.data.communities);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.log('get_paginated_users', error);
        });
    },

    /**
     * Gets the previous page of paginated users
     *
     * @param context
     */
    async PREVIOUS_PAGINATED_USERS(context) {
        await axios.post(context.state.paginatedUsers.prev_page_url, {
            search: context.state.paginatedUserSearch
        }).then(response => {
            console.log("previous_paginated_users", response.data);

            // Set users
            if (response.data.success) {
                context.commit("setPaginatedUsers", response.data.paginatedUsers);
            }
        }).catch(error => {
            console.log('previous_paginated_users', error);
        });
    },

    /**
     * Gets the next page of paginated users
     *
     * @param context
     */
    async NEXT_PAGINATED_USERS(context) {
        await axios.post(context.state.paginatedUsers.next_page_url, {
            search: context.state.paginatedUserSearch
        }).then(response => {
            console.log("next_paginated_users", response.data);

            // Set users
            if (response.data.success) {
                context.commit("setPaginatedUsers", response.data.paginatedUsers);
            }
        }).catch(error => {
            console.log('next_paginated_users', error);
        });
    },

    /**
     * Remove member
     */
    async REMOVE_MEMBER(context, payload) {
        await axios.post(`/c/communities/${context.rootState.community.data.id}/member/remove`, {
            communityId: context.rootState.community.data.id,
            memberId: payload
        }).then(async response => {
            console.log("remove_member", response.data);

            if (response.data.success) {
                context.commit('hideModal');
                context.commit('setModalExtraData', '');
                
                context.commit('setBannedMembers', response.data.members.banned);
                context.commit('setPendingMembers', response.data.members.pending);
                context.commit('setAllowedMembers', response.data.members.allowed);
                context.commit('setAdminMembers', response.data.members.admin);
                context.commit('setModeratorMembers', response.data.members.moderator);

                await context.dispatch('GET_PAGINATED_COMMUNITY_MEMBERS');
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.log('remove_member', error);
        });
    },

    /**
     * Save group
     */
    async SAVE_GROUP(context, payload) {
        await axios.post('/group/save', payload).then(response => {
            console.log('save_group response', response);

            if (response.data.success) {
                context.commit('updateCommunityGroups', response.data.groups);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('save_group error', errors.response.data);
        });
    },

    /**
     * Delete group
     */
    async DELETE_GROUP(context, payload) {
        await axios.post('/group/delete', payload).then(response => {
            console.log('delete_group response', response);

            if (response.data.success) {
                context.commit('updateCommunityGroups', response.data.groups);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('delete_group error', errors.response.data);
        });
    },

    async SAVE_COMMUNITY_TABS(context) {
        let postData = {
            communityId: context.rootState.community.data.id,
            tabClassrooms: context.rootState.community.data.tab_classrooms,
            tabCalendar: context.rootState.community.data.tab_calendar,
            tabLeaderboard: context.rootState.community.data.tab_leaderboard
        }

        console.log('save_community_tabs postData', postData)

        await axios.post('/tabs/save', postData).then(response => {
            console.log('save_community_tabs response', response);

            if (response.data.success) {
                context.commit('hideModal');
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('save_community_tabs error', errors.response.data);
        });
    },

    async SAVE_COMMUNITY_PRICE(context, payload)
    {
        console.log('save_community_price', payload);

        await axios.post('/community/price/save', {
            communityId: context.rootState.community.data.id,
            priceId: payload
        }).then(response => {
            console.log('save_community_price response', response);
            if (response.data.success) {
                context.commit('saveCommunityPrice', response.data.price);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('save_community_price error', errors.response.data);
        });
    },

    async SAVE_STRIPE_PRICE(context)
    {
        let data = {
            communityId: context.rootState.community.data.id,
            priceId: context.rootState.community.price.id,
            amountMonthly: context.rootState.community.price.amount_monthly,
            amountYearly: context.rootState.community.price.amount_yearly
        }

        console.log('save_stripe_price', data);

        await axios.post('/community/price/create', data).then(response => {
            console.log('save_stripe_price response', response);
            if (response.status === 200) {
                context.commit('setDisplayPriceForm', false);
                context.commit('resetCommunityPrice');
                context.commit('setCommunityProducts', response.data.products);
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            let error = Object.values(errors.response.data.errors);
            error = error.flat();
            notify({
                text: error[0],
                type: 'error'
            });
        });
    },

    async SAVE_COMMUNITY_FREE_TRIAL_DAYS(context, payload)
    {
        console.log('save_community_free_trial_days');

        await axios.post('/community/free-trial-days/update', {
            communityId: context.rootState.community.data.id,
            trialDays: context.rootState.community.data.trial_days
        }).then(response => {
            console.log('save_community_free_trial_days response', response);
            notify({
                text: response.data.message,
                type: 'success'
            });
        }).catch(errors => {
            console.error('save_community_free_trial_days error', errors.response.data);
        });
    },

    // @todo - test
    async SUBSCRIPTION_CHECKOUT(context, payload)
    {
        console.log('subscription_checkout', payload);

        await axios.post('/community/subscription/checkout', payload).then(async response => {
            console.log('subscription_checkout response', response);

            let pending_setup_intent = response.pending_setup_intent;
            if (pending_setup_intent && payload.stripeInstance) {
                const { result2, error } = await payload.stripeInstance.confirmCardSetup(pending_setup_intent);
                if (error) {
                    notify({
                        text: error.message,
                        type: 'error'
                    });
                    return;
                }
            }

            /* @todo
            let payment_intent_client_secret = result.payment_intent_client_secret;
            if (payment_intent_client_secret) {
                let stripeInstance = await loadStripe(stripeKey, {
                    stripeAccount: undefined,
                    apiVersion: undefined,
                    locale: 'auto',
                });

                const { result2, error } = await stripeInstance.confirmPayment({
                    clientSecret: payment_intent_client_secret,
                    redirect: 'if_required'
                });

                if (error) {
                    notify({
                        text: error.message,
                        type: 'error'
                    });
                    context.commit('setCommunityLoading', false);
                    return;
                }

                data.status = 'active'
            }
            */

            if (response.data.success) {
                context.commit('hideModal');

                if (response.data.overdue === true) {
                    context.commit('showModal', {
                        type: 'SubscriptionOverdueSucceeded',
                        transparent: true
                    });
                } else {
                    await router.push('/' + context.rootState.community.data.url)
                    await context.dispatch('GET_COMMUNITY', context.rootState.community.data.url);
                    if (response.data.member) {
                        context.commit('setMember', response.data.member);
                    }
                    context.commit('showModal', {
                        type: 'SubscriptionPurchaseSucceeded',
                        transparent: true
                    });
                }
            }
        }).catch(errors => {
            notify({
                text: errors.response.data.message,
                type: 'error'
            });
        });
    },

    async SUBSCRIPTION_REACTIVATE(context)
    {
        await axios.post('/community/subscription/reactivate', {
            communityId: context.rootState.community.data.id,
            memberId: context.rootState.member.data.id
        }).then(response => {
            console.log('subscription_reactivate response', response);

            if (response.data.success) {
                if (response.data.member) {
                    context.commit('setMember', response.data.member);
                }
                notify({
                    text: response.data.message,
                    type: 'success'
                });
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('subscription_reactivate error', errors.response.data);
        });
    },

    /**
     * Save member
     */
    async SAVE_MEMBER(context, payload) {
        console.log('save_member', payload)

        if (typeof payload.communityId === 'undefined') {
            payload.communityId = context.rootState.community.data.id;
        }
        await axios.post(`/c/communities/${context.rootState.community.data.id}/member/save`, payload).then(async response => {
            console.log("save_member", response.data);
            if (response.data.success) {
                context.commit('setBannedMembers', response.data.members.banned);
                context.commit('setPendingMembers', response.data.members.pending);
                context.commit('setAllowedMembers', response.data.members.allowed);
                context.commit('setAdminMembers', response.data.members.admin);
                context.commit('setModeratorMembers', response.data.members.moderator);

                await context.dispatch('GET_PAGINATED_COMMUNITY_MEMBERS');
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.log('save_member', error);
        });
    },

    /**
     * Ban member
     */
    async BAN_MEMBER(context, payload) {
        await axios.post(`/c/communities/${context.rootState.community.data.id}/member/ban`, {
            communityId: context.rootState.community.data.id,
            memberId: payload
        }).then(async response => {
            console.log("ban_member", response.data);

            if (response.data.success) {
                context.commit('setBannedMembers', response.data.members.banned);
                context.commit('setPendingMembers', response.data.members.pending);
                context.commit('setAllowedMembers', response.data.members.allowed);
                context.commit('setAdminMembers', response.data.members.admin);
                context.commit('setModeratorMembers', response.data.members.moderator);

                await context.dispatch('GET_PAGINATED_COMMUNITY_MEMBERS');
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.log('ban_member error', error);
        });
    },

    async CANCEL_MEMBER_SUBSCRIPTION(context, payload) {
        await axios.post(`/c/communities/${context.rootState.community.data.id}/member/subscription/cancel`, {
            communityId: context.rootState.community.data.id,
            selectedMember: payload
        }).then(async response => {
            console.log("cancel_member_subscription", response.data);

            if (response.data.success) {
                context.commit('setBannedMembers', response.data.members.banned);
                context.commit('setPendingMembers', response.data.members.pending);
                context.commit('setAllowedMembers', response.data.members.allowed);
                context.commit('setAdminMembers', response.data.members.admin);
                context.commit('setModeratorMembers', response.data.members.moderator);

                await context.dispatch('GET_PAGINATED_COMMUNITY_MEMBERS');

                notify({
                    text: response.data.message,
                    type: 'success'
                });
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(error => {
            console.log('cancel_member_subscription error', error);
        });
    },

    async GET_SUBSCRIPTION_VAT_RATE(context)
    {
        await axios.post('/community/subscription/vat-rate/get').then(response => {
            console.log('get_subscription_vat_rate response', response);
            context.commit('setMemberVatRate', response.data.rate);
        }).catch(errors => {
            console.error('get_subscription_vat_rate error', errors.response.data);
        });
    },

    async ASK_FOR_PAYOUT(context)
    {
      await axios.post('/community/payout/ask', {
          communityId: context.rootState.community.data.id
      }).then(response => {
          console.log('ask_for_payout response', response);

          context.commit('setCommunityPayoutsBalance', 0);
          context.commit('addCommunityPayout', response.data.payout);
          notify({
              text: response.data.message,
              type: 'success'
          });
      }).catch(errors => {
          console.error('ask_for_payout error', data);
      });
    },

    // @todo - test
    async UPDATE_SUBSCRIPTION_PAYMENT_METHOD(context, payload)
    {
        console.log('update_subscription_payment_method', payload);

        await axios.post('/community/subscription/card/update', payload).then(async response => {
            console.log('update_subscription_payment_method response', response);

            // 3DS check
            let pending_setup_intent = response.pending_setup_intent;
            if (pending_setup_intent && payload.stripeInstance) {
                const { result2, error } = await payload.stripeInstance.confirmCardSetup(pending_setup_intent);
                if (error) {
                    notify({
                        text: error.message,
                        type: 'error'
                    });
                    return;
                }
            }

            if (response.data.success) {
                if (response.data.member) {
                    context.commit('setMember', response.data.member);
                }
                context.commit('setModalExtraData', 'membership');
                notify({
                    text: response.data.message,
                    type: 'success'
                });
            } else {
                notify({
                    text: response.data.message,
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('update_subscription_payment_method error', errors.response.data);
        });
    },

    async SAVE_COMMUNITY_PAYMENT_NOTIFICATION(context, payload)
    {
        console.log('save_community_payment_notification', payload);

        await axios.post('/community/notifications/save', {
            communityId: context.rootState.community.data.id,
            notifications: {
                new_payment: context.rootState.community.newPaymentNotification
            },
        }).then(response => {
            console.log('save_community_payment_notification response', response);
            if (response.data.success) {
                notify({
                    text: response.data.message,
                    type: 'success'
                });
            }
        }).catch(errors => {
            console.error('save_community_payment_notification error', errors);
        });
    },

    async USER_TRANSACTION_INVOICE_DOWNLOAD(context, payload)
    {
        await axios.post('/community/subscription/invoice/download', {
            communityId: context.rootState.community.data.id,
            invoice: payload.invoice
        }).then(response => {
            console.log('user_transaction_invoice_download response', response);
            if (response.data.success && response.data.download_url) {
                console.log('response.data.download_url', response.data.download_url)
                window.open(response.data.download_url, '_blank');
            }
        }).catch(errors => {
            console.error('user_transaction_invoice_download error', errors);
        });
    },

    async TWO_FACTOR_AUTHENTICATION_INIT(context, payload)
    {
        await axios.post('/2fa/init').then(response => {
            console.error('two_factor_authentication_init response', response);
            context.commit('setVerificationCode', response.data.code);
            context.commit('setTwoFactorExpires', response.data.expires);
        }).catch(errors => {
            console.error('two_factor_authentication_init error', errors);
        });
    },

    async TWO_FACTOR_AUTHENTICATION_CHECK(context, payload)
    {
        await axios.post('/2fa/check', {
            code: payload.code,
            send_again: payload.send_again
        }).then(response => {
            console.error('two_factor_authentication response', response);

            if (response.data.success) {
                context.commit('setVerificationCode', null);
                context.commit('setTwoFactorExpires', response.data.expires);
                notify({
                    text: 'Code validated',
                    type: 'success'
                });
            } else {
                notify({
                    text: 'Wrong code',
                    type: 'error'
                });
            }
        }).catch(errors => {
            console.error('two_factor_authentication error', errors);
        });
    },

    async UPDATE_USER_INVOICE_DATA(context, payload)
    {
        await axios.post('/community/invoice-data/save', {
            communityId: context.rootState.community.data.id,
            data: payload
        }).then(response => {
            console.log('update_user_invoice_data response', response);
            notify({
                text: response.data.message,
                type: 'success'
            });
        }).catch(errors => {
            console.error('update_user_invoice_data error', errors);
        });
    },

    async GET_PAYOUTS_DATA(context, payload)
    {
        await axios.post('/community/payouts/data', {
            communityId: context.rootState.community.data.id,
            year: payload
        }).then(response => {
            console.log('get_payouts_data response', response);
            context.commit('setCommunityPayouts', response.data.payouts);
            context.commit('setCommunityTransactions', response.data.transactions);
        }).catch(errors => {
            console.error('get_payouts_data error', errors);
        });
    },

    /**
     * Get community admin stats data
     */
    async GET_ADMIN_STATS_DATA(context) {
        context.commit('setCommunityLoading', true);
        try {
            const { data: result } = await axios.get(`/c/communities/${context.rootState.community.data.id}/stats`);
            if (result.success) {
                result.data.lastUpdated = moment().unix();
                context.commit('setCommunitySettingsProperty', {
                    key: 'statsData',
                    v: result.data || null
                });
            } else {
                notify({
                    text: result.message || 'Unknown Error',
                    type: 'error'
                });
            }
        } catch (e) {
            context.commit('setCommunityPlan', {});
        }
        context.commit('setCommunityLoading', false);
    },

    async REGISTER_VISITOR(context)
    {
        let selectedTab = context.state.selectedTab;
        if ([TrackedPages.ABOUT, TrackedPages.START].includes(selectedTab)) {
            await axios.post('/visitor/save', {
                communityId: context.rootState.community.data.id,
                page: selectedTab
            }).then(response => {
                console.log('register_visitor response', response);
                if (!response.data.success) {
                    notify({
                        text: response.data.message,
                        type: 'error'
                    });
                }
            }).catch(errors => {
                console.error('register_visitor error', errors.response.data);
            });
        }
    }
};
