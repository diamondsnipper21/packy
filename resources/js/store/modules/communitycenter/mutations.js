import moment from 'moment'

export const mutations = {

    /**
     * Set invite Refer Member
     */
    setInviteReferMember(state, payload) {
        state.inviteReferMember = payload;
    },

    /**
     * Set invite share link
     */
    setInviteShareLink(state, payload) {
        state.inviteShareLink = payload;
    },

    /**
     * Reset the data for the community center on logout
     */
    resetCommunityData(state) {
        state.bannedMembers = [];
        state.allowedMembers = [];
        state.pendingMembers = [];
        state.moderatorMembers = [];
        state.adminMembers = [];
        state.settings = {};
        state.community = {};
        state.wolfeoLoginUrl = '';
        state.leaderboard.neededPoints = null;
        state.scheduledPosts = [];
        state.communityPosts = [];
    },

    /**
     * Initalize the data for the community center on login / dashboard
     */
    initCommunityData(state, payload) {
        state.settings = payload.settings;
        state.wolfeoLoginUrl = payload.wolfeoLoginUrl;
        state.leaderboard.neededPoints = payload.neededPoints;
        state.affSettings = payload.affSettings;
    },

    /**
     * Set banned members
     *
     * @param state
     * @param payload
     */
    setBannedMembers(state, payload) {
        state.bannedMembers = payload;
    },

    /**
     * Set pending members
     *
     * @param state
     * @param payload
     */
    setPendingMembers(state, payload) {
        state.pendingMembers = payload;
    },

    /**
     * Set all members
     *
     * @param state
     * @param payload
     */
    setAllowedMembers(state, payload) {
        state.allowedMembers = payload;
    },

    /**
     * Set moderator members
     *
     * @param state
     * @param payload
     */
    setModeratorMembers(state, payload) {
        state.moderatorMembers = payload;
    },

    /**
     * Set admin members
     *
     * @param state
     * @param payload
     */
    setAdminMembers(state, payload) {
        state.adminMembers = payload;
    },

    /**
     * Change loading status
     */
    communityLoading(state, payload) {
        state.loading = payload;
    },

    /**
     * Mobile only - toggle the status of the navbar
     */
    closeCommunityNav(state) {
        state.navBarOpen = false;
    },

    /**
     * Mobile only - toggle the status of the navbar
     */
    toggleCommunityNav(state) {
        state.navBarOpen = !state.navBarOpen;
    },

    /**
     * Clear any errors for this form_key
     */
    clearCommunityCenterErrors(state, payload) {
        if (state.errors.hasOwnProperty(payload) && payload !== "all") {
            delete state.errors[payload];
        } else if (payload === "all") {
            // Clears all errors
            state.errors = {};
        }
    },

    /**
     * Set community tab
     */
    setCommunityTab(state, payload) {
        state.selectedTab = payload;
    },

    ////////////////////////////////////////////

    /**
     * Set community events
     */
    setCommunityEvents(state, payload) {
        state.communityEvents = payload;
    },

    /**
     * Set monthly events
     */
    setMonthlyEvents(state, payload) {
        state.monthlyEvents = payload;
    },

    /**
     * Update classrooms
     *
     */
    updateClassrooms(state, payload) {
        if (state.communityClassrooms.hasOwnProperty('data')) {
            let classrooms = JSON.parse(JSON.stringify(state.communityClassrooms.data));
            classrooms = classrooms.map((item) => {
                if (item.id === payload.id) {
                    item = payload;
                }

                return { ...item };
            });
            state.communityClassrooms.data = classrooms;
        }
    },

    /**
     * Update community classrooms after moving
     */
    updateCommunityClassroomsAfterMove(state, payload) {
        if (state.communityClassrooms.hasOwnProperty('data') && payload.hasOwnProperty('classroom') && payload.hasOwnProperty('nextClassroom')) {
            let classrooms = JSON.parse(JSON.stringify(state.communityClassrooms.data));
            classrooms = classrooms.map((classroom) => {
                if (classroom.id === payload.classroom.id) {
                    classroom = payload.classroom;
                } else if (classroom.id === payload.nextClassroom.id) {
                    classroom = payload.nextClassroom;
                }

                return { ...classroom };
            });

            classrooms.sort((a, b) => a.order - b.order);

            state.communityClassrooms.data = classrooms;
        }
    },

    /**
     * Set community classroom
     */
    setCommunityClassroom(state, payload) {
        state.communityClassroom = payload;
    },

    /**
     * Set community post by id
     */
    setCommunityEventById(state, payload) {
        let communityEvents = [...state.communityEvents];
        let communityEvent = communityEvents.filter(event => parseInt(event.id) === parseInt(payload));
        if (communityEvent.length === 1) {
            state.communityEvent = communityEvent[0];
        }
    },

    /**
     * Set community event
     */
    setCommunityEvent(state, payload) {
        state.communityEvent = payload;
    },

    /**
     * Set community members
     */
    setPaginatedMembers(state, payload) {
        state.paginatedMembers = payload;
    },

    /**
     * Set community members filter
     */
    setMemberFilter(state, payload) {
        state.memberFilter = payload;
    },

    setCalendarViewMode(state, payload) {
        state.calendarViewMode = payload;
    },

    decreaseMonth(state) {
        let currentMonth = state.eventMonth;
        state.eventMonth = currentMonth - 1;
    },

    increaseMonth(state) {
        let currentMonth = state.eventMonth;
        state.eventMonth = currentMonth + 1;
    },

    /**
     * Set community settings property
     */
    setCommunitySettingsProperty(state, payload) {
        state.communitySettings[payload.key] = payload.v;
    },

    /**
     * Set community settings property
     */
    setCommunitySettingsFileProperty(state, payload) {
        if (state.communitySettings.lessonFile !== null) {
            state.communitySettings.lessonFile[payload.key] = payload.v;
        }
    },

    /**
     * Set community settings classroom property
     */
    setCommunitySettingsClassroomProperty(state, payload) {
        state.communitySettings.classroom[payload.key] = payload.v;
    },

    /**
     * Set community settings set property
     */
    setCommunitySettingsSetProperty(state, payload) {
        state.communitySettings.set[payload.key] = payload.v;
    },

    /**
     * Set community settings lesson property
     */
    setCommunitySettingsLessonProperty(state, payload) {
        state.communitySettings.lesson[payload.key] = payload.v;
    },

    /**
     * Set community settings calendar event property
     */
    setCommunitySettingsCalendarEventProperty(state, payload) {
        state.communitySettings.calendarEvent[payload.key] = payload.v;
    },

    /**
     * Reset community settings property
     */
    resetSettingModalContent(state) {
        state.communitySettings.ruleShow = 'list';
        state.communitySettings.groupShow = 'list';
        state.communitySettings.categoryShow = 'list';
        state.communitySettings.extensionShow = 'list';
        state.communitySettings.linkShow = 'list';
        state.communitySettings.calendarShow = 'list';
        state.communitySettings.classroomShow = 'list';
        state.communitySettings.billingSection = 1;
    },

    /**
     * Set all event.dropdown to false
     */
    resetEventDropdownItem(state) {
        if (state.monthlyEvents.hasOwnProperty('data')) {
            let events = JSON.parse(JSON.stringify(state.monthlyEvents.data));
            events.forEach(event => {
                event.dropdown = false;
            });
            state.monthlyEvents.data = events;
        }
    },

    /**
     * Toggle event dropdown
     *
     * Set all other event.dropdown to false
     */
    toggleEventDropdownItem(state, payload) {
        if (state.monthlyEvents.hasOwnProperty('data')) {
            let events = JSON.parse(JSON.stringify(state.monthlyEvents.data));
            events.forEach(item => {
                item.dropdown = (item.id === payload.id) ? !item.dropdown : false;
            });
            state.monthlyEvents.data = events;
        }
    },

    /**
     * Set community property
     */
    setDropzoneError(state, payload) {
        state.dropzoneError[payload.key] = payload.v;
    },

    /**
     * Reset community property
     */
    resetDropzoneError(state) {
        state.dropzoneError = {
            'logo': null,
            'summary_photo': null
        };
    },

    /**
     * Set all classroom.dropdown to false
     */
    resetClassroomDropdownItem(state) {
        if (state.communityClassrooms.hasOwnProperty('data')) {
            let classrooms = JSON.parse(JSON.stringify(state.communityClassrooms.data));
            classrooms.forEach(classroom => {
                classroom.dropdown = false;
            });
            state.communityClassrooms.data = classrooms;
        }
    },

    /**
     * Toggle set dropdown
     *
     * Set all other set.dropdown to false
     */
    toggleSetDropdownItem(state, payload) {
        if (state.communitySettings.classroom !== null && typeof state.communitySettings.classroom.sets !== 'undefined' && state.communitySettings.classroom.sets.length > 0) {
            let sets = JSON.parse(JSON.stringify(state.communitySettings.classroom.sets));
            sets.forEach(item => {
                item.dropdown = (item.id === payload.id) ? !item.dropdown : false;
            });
            state.communitySettings.classroom.sets = sets;
        }
    },

    /**
     * Toggle classroom set dropdown
     *
     * Set all other set.dropdown to false
     */
    toggleClassroomSetDropdownItem(state, payload) {
        if (state.communityClassrooms.hasOwnProperty('data')) {
            let classrooms = JSON.parse(JSON.stringify(state.communityClassrooms.data));
            classrooms.forEach(classroom => {
                if (classroom.id === payload.classroomId) {
                    if (typeof classroom.sets !== 'undefined' && classroom.sets.length > 0) {
                        let sets = JSON.parse(JSON.stringify(classroom.sets));
                        sets.forEach(item => {
                            item.dropdown = (item.id === payload.setId) ? !item.dropdown : false;
                        });
                        classroom.sets = sets;
                    }
                }
            });
            state.communityClassrooms.data = classrooms;
        }
    },

    /**
     * Toggle classroom set dropdown
     *
     * Set all other set.dropdown to false
     */
    toggleClassroomSetLessonDropdownItem(state, payload) {
        if (state.communityClassrooms.hasOwnProperty('data')) {
            let classrooms = JSON.parse(JSON.stringify(state.communityClassrooms.data));
            classrooms.forEach(classroom => {
                if (classroom.id === payload.classroomId) {
                    if (typeof classroom.sets !== 'undefined' && classroom.sets.length > 0) {
                        let sets = JSON.parse(JSON.stringify(classroom.sets));
                        sets.forEach(set => {
                            if (set.id === payload.setId && set.lessons.length > 0) {
                                let lessons = JSON.parse(JSON.stringify(set.lessons));
                                lessons.forEach(item => {
                                    item.dropdown = (item.id === payload.lessonId) ? !item.dropdown : false;
                                });
                                set.lessons = lessons;
                            }
                        });
                        classroom.sets = sets;
                    }
                }
            });
            state.communityClassrooms.data = classrooms;
        }
    },

    /**
     * Toggle classroom set dropdown
     *
     * Set all other set.dropdown to false
     */
    toggleClassroomLessonDropdownItem(state, payload) {
        if (state.communityClassrooms.hasOwnProperty('data')) {
            let classrooms = JSON.parse(JSON.stringify(state.communityClassrooms.data));
            classrooms.forEach(classroom => {
                if (classroom.id === payload.classroomId) {
                    if (typeof classroom.lessons !== 'undefined' && classroom.lessons.length > 0) {
                        let lessons = JSON.parse(JSON.stringify(classroom.lessons));
                        lessons.forEach(item => {
                            item.dropdown = (item.id === payload.lessonId) ? !item.dropdown : false;
                        });
                        classroom.lessons = lessons;
                    }
                }
            });
            state.communityClassrooms.data = classrooms;
        }
    },

    /**
     * Expand classroom set content
     */
    expandClassroomSetContent(state, payload) {
        if (state.communityClassrooms.hasOwnProperty('data')) {
            let classrooms = JSON.parse(JSON.stringify(state.communityClassrooms.data));
            classrooms.forEach(classroom => {
                if (classroom.id === payload.classroomId) {
                    if (typeof classroom.sets !== 'undefined' && classroom.sets.length > 0) {
                        let sets = JSON.parse(JSON.stringify(classroom.sets));
                        sets.forEach(item => {
                            item.expand = false;
                            if (item.id === payload.setId) {
                                item.expand = true;
                            }
                        });
                        classroom.sets = sets;
                    }
                }
            });
            state.communityClassrooms.data = classrooms;
        }
    },

    /**
     * Collapse classroom set content
     */
    collapseClassroomSetContent(state) {
        if (state.communityClassrooms.hasOwnProperty('data')) {
            let classrooms = JSON.parse(JSON.stringify(state.communityClassrooms.data));
            classrooms.forEach(classroom => {
                if (typeof classroom.sets !== 'undefined' && classroom.sets.length > 0) {
                    let sets = JSON.parse(JSON.stringify(classroom.sets));
                    sets.forEach(item => {
                        item.expand = false;
                    });
                    classroom.sets = sets;
                }
            });
            state.communityClassrooms.data = classrooms;
        }
    },

    /**
     * Expand set content
     */
    expandSetContent(state, payload) {
        if (state.communitySettings.classroom !== null && typeof state.communitySettings.classroom.sets !== 'undefined' && state.communitySettings.classroom.sets.length > 0) {
            let sets = JSON.parse(JSON.stringify(state.communitySettings.classroom.sets));
            sets.forEach(item => {
                item.expand = false;
                if (item.id === payload) {
                    item.expand = true;
                }
            });
            state.communitySettings.classroom.sets = sets;
        }
    },

    /**
     * Expand one set content
     */
    expandOneSetContent(state, payload) {
        if (state.communitySettings.classroom !== null && typeof state.communitySettings.classroom.sets !== 'undefined' && state.communitySettings.classroom.sets.length > 0) {
            let sets = JSON.parse(JSON.stringify(state.communitySettings.classroom.sets));
            sets.forEach(item => {
                if (item.id === payload) {
                    item.expand = true;
                }
            });
            state.communitySettings.classroom.sets = sets;
        }
    },

    /**
     * Collapse set content
     */
    collapseSetContent(state) {
        if (state.communitySettings.classroom !== null && typeof state.communitySettings.classroom.sets !== 'undefined' && state.communitySettings.classroom.sets.length > 0) {
            let sets = JSON.parse(JSON.stringify(state.communitySettings.classroom.sets));
            sets.forEach(item => {
                item.expand = false;
            });
            state.communitySettings.classroom.sets = sets;
        }
    },

    /**
     * Collapse one set content
     */
    collapseOneSetContent(state, payload) {
        if (state.communitySettings.classroom !== null && typeof state.communitySettings.classroom.sets !== 'undefined' && state.communitySettings.classroom.sets.length > 0) {
            let sets = JSON.parse(JSON.stringify(state.communitySettings.classroom.sets));
            sets.forEach(item => {
                if (item.id === payload) {
                    item.expand = false;
                }
            });
            state.communitySettings.classroom.sets = sets;
        }
    },

    /**
     * Expand classroom content
     */
    expandClassroomContent(state, payload) {
        if (state.communityClassrooms.hasOwnProperty('data')) {
            let classrooms = JSON.parse(JSON.stringify(state.communityClassrooms.data));
            classrooms.forEach(classroom => {
                classroom.expand = false;
                if (classroom.id === payload) {
                    classroom.expand = true;
                }
            });
            state.communityClassrooms.data = classrooms;
        }
    },

    /**
     * Collapse classroom content
     */
    collapseClassroomContent(state) {
        if (state.communityClassrooms.hasOwnProperty('data')) {
            let classrooms = JSON.parse(JSON.stringify(state.communityClassrooms.data));
            classrooms.forEach(classroom => {
                classroom.expand = false;
            });
            state.communityClassrooms.data = classrooms;
        }
    },

    /**
     * Set all lesson.dropdown to false
     */
    resetLessonDropdownItem(state) {
        if (state.communitySettings.classroom !== null && typeof state.communitySettings.classroom.lessons !== 'undefined' && state.communitySettings.classroom.lessons.length > 0) {
            let lessons = JSON.parse(JSON.stringify(state.communitySettings.classroom.lessons));
            lessons.forEach(lesson => {
                lesson.dropdown = false;
            });
            state.communitySettings.classroom.lessons = lessons;
        }
    },

    /**
     * Toggle lesson dropdown
     *
     * Set all other lesson.dropdown to false
     */
    toggleLessonDropdownItem(state, payload) {
        if (state.communitySettings.classroom !== null && typeof state.communitySettings.classroom.lessons !== 'undefined' && state.communitySettings.classroom.lessons.length > 0) {
            let lessons = JSON.parse(JSON.stringify(state.communitySettings.classroom.lessons));
            lessons.forEach(item => {
                item.dropdown = (item.id === payload.id) ? !item.dropdown : false;
            });
            state.communitySettings.classroom.lessons = lessons;
        }

        // reset all set's lesson dropdown
        if (state.communitySettings.classroom !== null && typeof state.communitySettings.classroom.sets !== 'undefined' && state.communitySettings.classroom.sets.length > 0) {
            let sets = JSON.parse(JSON.stringify(state.communitySettings.classroom.sets));
            sets = sets.map((set) => {
                if (set.lessons.length > 0) {
                    let lessons = JSON.parse(JSON.stringify(set.lessons));
                    lessons.forEach(item => {
                        item.dropdown = false;
                    });
                    set.lessons = lessons;
                }

                return { ...set };
            });

            state.communitySettings.classroom.sets = sets;
        }
    },

    /**
     * Set all set lesson.dropdown to false
     */
    resetSetLessonDropdownItem(state) {
        if (state.communitySettings.classroom !== null && typeof state.communitySettings.classroom.sets !== 'undefined' && state.communitySettings.classroom.sets.length > 0) {
            let sets = JSON.parse(JSON.stringify(state.communitySettings.classroom.sets));
            sets = sets.map((set) => {
                if (set.lessons.length > 0) {
                    let lessons = JSON.parse(JSON.stringify(set.lessons));
                    lessons.forEach(item => {
                        item.dropdown = false;
                    });
                    set.lessons = lessons;
                }

                return { ...set };
            });

            state.communitySettings.classroom.sets = sets;
        }
    },

    /**
     * Toggle set lesson dropdown
     *
     * Set all other lesson.dropdown to false
     */
    toggleSetLessonDropdownItem(state, payload) {
        if (state.communitySettings.classroom !== null && typeof state.communitySettings.classroom.sets !== 'undefined' && state.communitySettings.classroom.sets.length > 0) {
            let sets = JSON.parse(JSON.stringify(state.communitySettings.classroom.sets));
            sets = sets.map((set) => {
                if (set.lessons.length > 0) {
                    if (set.id === payload.setId) {
                        let lessons = JSON.parse(JSON.stringify(set.lessons));
                        lessons.forEach(item => {
                            item.dropdown = (item.id === payload.lessonId) ? !item.dropdown : false;
                        });
                        set.lessons = lessons;
                    } else {
                        let lessons = JSON.parse(JSON.stringify(set.lessons));
                        lessons.forEach(item => {
                            item.dropdown = false;
                        });
                        set.lessons = lessons;
                    }
                }

                return { ...set };
            });

            state.communitySettings.classroom.sets = sets;
        }

        // reset all classroom lesson dropdown
        if (state.communitySettings.classroom !== null && typeof state.communitySettings.classroom.lessons !== 'undefined' && state.communitySettings.classroom.lessons.length > 0) {
            let lessons = JSON.parse(JSON.stringify(state.communitySettings.classroom.lessons));
            lessons.forEach(item => {
                item.dropdown = false;
            });
            state.communitySettings.classroom.lessons = lessons;
        }
    },

    /**
     * Update membership
     *
     * @param state
     * @param payload
     */
    updateMembership(state, payload) {
        state.community = payload;
    },

    /**
     * Set notifications
     *
     * @param state
     * @param payload
     */
    setNotifications(state, payload) {
        state.notifications = payload;
    },

    /**
     * Set chat users
     *
     * @param state
     * @param payload
     */
    setChatUsers(state, payload) {
        let { users, userId } = payload;
        let unreadUsersToUser = [];
        let unreadUsersFromUser = [];
        let readUsers = [];
        let belowUsers = [];
        if (users.length > 0) {
            users.sort(function (a, b) {
                let comparison = 0;
                if (a.lastChat?.created_at > b.lastChat?.created_at) {
                    comparison = -1;
                } else if (a.lastChat?.created_at < b.lastChat?.created_at) {
                    comparison = 1;
                }
                return comparison
            });

            unreadUsersToUser = users.filter(el => el.lastChat?.read_at === null && el.lastChat?.to_id === userId);
            unreadUsersFromUser = users.filter(el => el.lastChat?.read_at === null && el.lastChat?.from_id === userId);
            readUsers = users.filter(el => el.lastChat?.read_at !== null);
            belowUsers = unreadUsersFromUser.concat(readUsers);
            belowUsers.sort(function (a, b) {
                let comparison = 0;
                if (a.lastChat?.created_at > b.lastChat?.created_at) {
                    comparison = -1;
                } else if (a.lastChat?.created_at < b.lastChat?.created_at) {
                    comparison = 1;
                }
                return comparison
            });

            users = unreadUsersToUser.concat(belowUsers);
        }

        state.chatUsers = users;
    },

    /**
     * Set unread chats count
     *
     * @param state
     * @param payload
     */
    setUnreadChatsCnt(state, payload) {
        state.unreadChatsCnt = payload;
    },

    setMembersCount(state, payload) {
        state.membersCount = payload;
    },

    /**
     * Set unread notifications count
     *
     * @param state
     * @param payload
     */
    setUnreadNotificationsCnt(state, payload) {
        state.unreadNotificationsCnt = payload;
    },

    /**
     * Set blocked user ids
     *
     * @param state
     * @param payload
     */
    setBlockedUserIds(state, payload) {
        state.blockedUserIds = payload;
    },

    /**
     * Set community notification filter
     */
    setNotificationFilter(state, payload) {
        state.notificationFilter = payload;
    },

    /**
     * Set community chat user filter
     */
    setChatUserFilter(state, payload) {
        state.chatUserFilter = payload;
    },

    /**
     * Set search user filter
     */
    setSearchUserFilter(state, payload) {
        state.searchUserFilter = payload;
    },

    /**
     * Set search member filter
     */
    setSearchMemberFilter(state, payload) {
        state.searchMemberFilter = payload;
    },

    /**
     * Reset new chat
     */
    resetNewChat(state) {
        state.newChat = {
            content: '',
            medias: []
        };
    },

    /**
     * Set new chat content
     */
    setNewChatContent(state, payload) {
        state.newChat.content = payload;
    },

    /**
     * Add new chat media
     */
    addNewChatMedia(state, payload) {
        state.newChat.medias.push(payload);
    },

    /**
    * Removes a post comment media
    */
    removeNewChatMedia(state, payload) {
        state.newChat.medias = state.newChat.medias.filter(item => item.path !== payload.path);
    },

    /**
     * Set chat to_id
     */
    setChatToId(state, payload) {
        state.chatToId = payload;
    },

    /**
     * Set chat messages
     */
    setChatMessages(state, payload) {
        state.chatMessages = payload;
    },

    /**
     * Add new chat message
     */
    addNewChatMessage(state, payload) {
        state.chatMessages.push(payload);
    },

    /**
     * Set chat opponent user
     */
    setChatOpponentUser(state, payload) {
        state.chatOpponentUser = payload;
    },

    /**
     * Set nav content tab
     */
    setNavContentTab(state, payload) {
        state.navContentTab = payload;
    },

    /**
     * Set paginated users
     */
    setPaginatedUsers(state, payload) {
        state.paginatedUsers = payload;
    },

    /**
     * Set paginated user search
     */
    setPaginatedUserSearch(state, payload) {
        state.paginatedUserSearch = payload;
    },

    /**
     * Set uploading status
     *
     * @param state
     * @param payload
     */
    setUploading(state, payload) {
        state.uploading = payload;
    },

    /**
     * Set decline feedback comment 
     *
     * @param state
     * @param payload
     */
    setDeclineJoinFeedback(state, payload) {
        state.declineJoinFeedback = payload;
    },

    /**
     * Set decline share notification status
     *
     * @param state
     * @param payload
     */
    setDeclineJoinShareNotify(state, payload) {
        state.declineJoinShareNotify = payload;
    },

    setDeclineJoinShareNotify(state, payload) {
        state.declineJoinShareNotify = payload;
    },

    setVerificationCode(state, payload) {
        state.verificationCode = payload;
    },

    setTwoFactorExpires(state, payload) {
        state.twoFactorExpires = payload;
    },

    setCommunities(state, payload) {
        state.communities = payload;
    }
};
