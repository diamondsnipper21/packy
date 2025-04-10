import { notify } from "@kyvg/vue3-notification";
import { MemberRole } from '../../../data/enums';
import router from '../../../router';
export const actions = {

    ///// Classrooms /////////
    async GET_CLASSROOMS(context, payload) {
        context.commit('setRoomLoading', true);
        const simple = context.state.isSimpleClassrooms;
        try {
            const { data: result } = await axios.get(`/c/communities/${context.rootState.community.data.id}/classrooms`, { 
                params: { 
                    role: context.rootState.member.data?.role,
                    memberId: context.rootState.member.data?.id,
                    page: payload, 
                    simple 
                } 
            });

            const { data: classrooms, total, current_page, per_page, to } = result.data || {}
            const role = context.rootState.member.data?.role;
            if (!simple && (total === to || (total === 0 && to === null)) && ([MemberRole.OWNER, MemberRole.ADMIN, MemberRole.MODERATOR].includes(role))) {
                classrooms.push({
                    id: 0,
                    title: '',
                    content: '',
                    publish: false,
                    photo: '',
                    media: '',
                    access_type: 'all',
                    access_value: '',
                    level: 1,
                    sets: [],
                    lessons: [],
                    setsCount: 0,
                    lessonsCount: 0
                });
            }
            context.commit("setClassrooms", classrooms);
            context.commit("setClassroomPagination", { total, current_page, per_page, to });
        } catch (e) {
            context.commit("setClassrooms", []);
            context.commit("setClassroomPagination", {});
        }
        context.commit('setRoomLoading', false);
    },

    async GET_CLASSROOM(context, payload) {
        context.commit('setRoomItemLoading', true);

        try {
            const { data: result } = await axios.get(`/c/communities/${context.rootState.community.data.id}/classrooms/${payload.cid}`, { 
                params: {
                    memberId: context.rootState.member.data?.id,
                    role: context.rootState.member.data?.role,
                    level: context.rootState.member.data?.level
                } 
            });
            const classroom = result.data || {}
            classroom.expand = true;

            context.commit("setClassroomInfo", classroom);
            context.commit("updateClassroomList", classroom);
            context.commit("setClassroomCloneData", classroom);
        } catch (e) {
            notify({
                text: e.response?.data?.message || 'Unknown Error',
                type: 'error'
            });
            context.commit("setClassroomInfo", {});
        }
        context.commit('setRoomItemLoading', false);
    },

    async CREATE_CLASSROOM(context, payload) {
        context.commit('setRoomItemLoading', true);
        try {
            const { data: result } = await axios.post(`/c/communities/${context.rootState.community.data.id}/classrooms`, payload);
            const classroom = result.data || {}

            context.commit("setClassroomInfo", classroom);
        } catch (e) {
            context.commit("setClassroomInfo", {});
        }
        context.commit('setRoomItemLoading', false);
    },

    async UPDATE_CLASSROOM(context, payload) {
        context.commit('setRoomItemLoading', true);
        try {
            const { data: result } = await axios.put(`/c/communities/${context.rootState.community.data.id}/classrooms/${payload.id}`, payload);
            const classroom = result.data || {}

            context.commit("updateClassroomInfo", classroom);
        } catch (e) {
            notify({
                text: e.response?.data?.message || 'Unknown Error',
                type: 'error'
            });
        }
        context.commit('setRoomItemLoading', false);
    },

    async DELETE_CLASSROOM(context, payload) {
        context.commit('setRoomItemLoading', true);
        try {
            await axios.delete(`/c/communities/${context.rootState.community.data.id}/classrooms/${payload.id}`);
            context.commit('updateClassroomsAfterDelete', payload);
            context.commit("setClassroomInfo", {});
            if (payload.from == 'detail') {
                await router.push('/' + context.rootState.community.data?.url + '/ressources')
            }
        } catch (e) {
        }
        context.commit('setRoomItemLoading', false);
    },

    async MOVE_CLASSROOM(context, payload) {
        console.log('move_classroom', payload);
        try {
            const { data: result } = await axios.post(`/c/communities/${context.rootState.community.data.id}/classrooms/${payload.id}/move`, payload);
            const { current, next } = result.data || {}
            context.commit("swapClassroomOrder", { current, next });
        } catch (e) {
        }
    },

    async GET_CLASSROOM_SET(context, payload) {
        try {
            const { data: result } = await axios.get(`/c/communities/${context.rootState.community.data.id}/classrooms/${payload.classroom_id || context.state.data.id}/sets/${payload.id}`, { 
                params: {
                    memberId: context.rootState.member.data?.id,
                    role: context.rootState.member.data?.role,
                    level: context.rootState.member.data?.level
                } 
            });
            const set = result.data || {}
            set.expand = true
            context.commit("setClassroomSet", set);
            context.commit("setSelectedSet", set);
            context.commit("setClassroomListSet", set);
            context.commit("setClassroomCloneSet", set);
            return set
        } catch (e) {
            notify({
                text: e.response?.data?.message || 'Unknown Error',
                type: 'error'
            });
        }
        return {}
    },

    async CREATE_CLASSROOM_SET(context, payload) {
        try {
            const { data: result } = await axios.post(`/c/communities/${context.rootState.community.data.id}/classrooms/${context.state.data.id}/sets`, payload);
            const set = result.data || {}
            set.type = 'set'
            context.commit("addClassroomSet", set);
        } catch (e) {
        }
    },

    async UPDATE_CLASSROOM_SET(context, payload) {
        try {
            const { data: result } = await axios.put(`/c/communities/${context.rootState.community.data.id}/classrooms/${context.state.data.id}/sets/${payload.id}`, payload);
            const set = result.data || {}
            context.commit("setClassroomSet", set);
        } catch (e) {
            notify({
                text: e.response?.data?.message || 'Unknown Error',
                type: 'error'
            });
        }
    },

    async DELETE_CLASSROOM_SET(context, payload) {
        try {
            await axios.delete(`/c/communities/${context.rootState.community.data.id}/classrooms/${context.state.data.id}/sets/${payload.id}`);
            context.commit("removeClassroomSet", payload);
            context.commit("removeClassroomListSet", payload);
        } catch (e) {
        }
    },

    async MOVE_CLASSROOM_SET(context, payload) {
        console.log('move_classroom_set', payload);
        try {
            const { data: result } = await axios.post(`/c/communities/${context.rootState.community.data.id}/classrooms/${payload.classroom_id}/sets/${payload.id}/move`, {
                direction: payload.direction
            });
            const { current, next } = result.data || {}
            context.commit("swapClassroomListNavItemOrder", { current, next, classroomId: payload.classroom_id });
        } catch (e) {
        }
    },

    async GET_CLASSROOM_LESSON(context, payload) {
        context.commit('setLessonLoading', true);
        let lesson = {}

        let classroomId = context.state.data.id;
        if (typeof payload.classroom_id !== 'undefined' && payload.classroom_id > 0) {
            classroomId = payload.classroom_id;
        }

        try {
            const { data: result } = await axios.get(`/c/communities/${context.rootState.community.data.id}/classrooms/${classroomId}/sets/${payload.set_id || 0}/lessons/${payload.id}`, { 
                params: {
                    memberId: context.rootState.member.data?.id,
                    role: context.rootState.member.data?.role,
                    level: context.rootState.member.data?.level
                }
            });
            lesson = result.data || {}

            let posts = [];
            if (typeof lesson.posts !== 'undefined' && lesson.posts.length > 0) {
                let lessonPosts = JSON.parse(JSON.stringify(lesson.posts));
                lessonPosts.map((lessonPost, index) => {

                    if (typeof lessonPost.post !== 'undefined') {
                        posts.push(lessonPost.post);
                    }
                });
                lesson.posts = posts;
            }

            context.commit("setSelectedLesson", lesson);
            context.commit("setCloneLesson", lesson);
        } catch (e) {
            notify({
                text: e.response?.data?.message || 'Unknown Error',
                type: 'error'
            });
        }
        context.commit('setLessonLoading', false);
        return lesson
    },

    async CREATE_CLASSROOM_LESSON(context, payload) {
        try {
            const { data: result } = await axios.post(`/c/communities/${context.rootState.community.data.id}/classrooms/${context.state.data.id}/sets/${payload.set_id || 0}/lessons`, payload);
            const lesson = result.data || {}
            const lessonItem = {
                type: 'lesson',
                id: lesson.id,
                name: lesson.title,
                publish: lesson.publish,
                order: lesson.order,
                completed: lesson.completed,
                set_id: lesson.set_id || 0
            }
            context.commit("addLessonInSet", lessonItem);
            context.commit("setSelectedLesson", lesson);
        } catch (e) {
        }
    },

    async DUPLICATE_CLASSROOM_LESSON(context, payload) {
        try {
            const { data: result } = await axios.post(`/c/communities/${context.rootState.community.data.id}/classrooms/${context.state.data.id}/sets/${payload.set_id || 0}/lessons/${payload.id}/clone`, payload);
            const lesson = result.data || {}
            const lessonItem = {
                type: 'lesson',
                id: lesson.id,
                name: lesson.title,
                publish: lesson.publish,
                order: lesson.order,
                completed: lesson.completed,
                set_id: lesson.set_id || 0
            }
            context.commit("addLessonInSet", lessonItem);
            context.commit("setSelectedLesson", lesson);
        } catch (e) {
        }
    },

    async UPDATE_CLASSROOM_LESSON(context, payload) {
        try {
            const { data: result } = await axios.put(`/c/communities/${context.rootState.community.data.id}/classrooms/${context.state.data.id}/sets/${payload.set_id || 0}/lessons/${payload.id}`, payload);
            const lesson = result.data || {}
            const lessonItem = {
                type: 'lesson',
                id: lesson.id,
                name: lesson.title,
                publish: lesson.publish,
                order: lesson.order,
                completed: lesson.completed,
                set_id: lesson.set_id || 0
            }
            context.commit("updateLessonInSet", lessonItem);
            context.commit("setSelectedLesson", lesson);
        } catch (e) {
            notify({
                text: e.response?.data?.message || 'Unknown Error',
                type: 'error'
            });
        }
    },

    async DELETE_CLASSROOM_LESSON(context, payload) {
        try {
            const roomId = payload.classroom_id || context.state.data.id
            await axios.delete(`/c/communities/${context.rootState.community.data.id}/classrooms/${roomId}/sets/${payload.set_id}/lessons/${payload.id}`);
            context.commit("removeClassroomLesson", payload);
        } catch (e) {
        }
    },

    async MOVE_CLASSROOM_LESSON(context, payload) {
        console.log('move_classroom_lesson', payload);
        try {
            const roomId = payload.classroom_id || context.state.data.id
            const { data: result } = await axios.post(`/c/communities/${context.rootState.community.data.id}/classrooms/${roomId}/sets/${payload.set_id}/lessons/${payload.id}/move`, {
                direction: payload.direction
            });
            const { current, next } = result.data || {}
            context.commit("swapClassroomListNavItemOrder", { current, next, classroomId: payload.classroom_id });
        } catch (e) {
        }
    },

    async COMPLETE_CLASSROOM_LESSON(context, payload) {
        try {
            const { data: result } = await axios.post(`/c/communities/${context.rootState.community.data.id}/classrooms/${context.state.data.id}/sets/${payload.set_id}/lessons/${payload.id}/complete`);
            const { lesson, completion } = result.data || {}
            const lessonItem = {
                type: 'lesson',
                id: lesson.id,
                name: lesson.title,
                publish: lesson.publish,
                order: lesson.order,
                completed: lesson.completed,
                set_id: lesson.set_id || 0
            }
            context.commit("updateLessonInSet", lessonItem);
            context.commit("setSelectedLessonProp", {
                key: 'completed',
                v: lesson.completed
            });
            context.commit("updateClassroomInfo", {
                completion
            });
        } catch (e) {
        }
    },

    async UPDATE_LESSON_RESOURCE(context, payload) {
        try {
            const { data: result } = await axios.put(`/c/communities/${context.rootState.community.data.id}/classrooms/${context.state.data.id}/sets/${context.state.cloneLesson.set_id || 0}/lessons/${context.state.cloneLesson.id}/resources/${payload.id || 0}`, payload);
            const resource = result.data || {}
            context.commit("setResourceInCloneLesson", resource);
        } catch (e) {
        }
    },

    async DELETE_LESSON_RESOURCE(context, payload) {
        try {
            if (context.state.cloneLesson.id && typeof payload.id !== 'string') {
                await axios.delete(`/c/communities/${context.rootState.community.data.id}/classrooms/${context.state.data.id}/sets/${context.state.cloneLesson.set_id || 0}/lessons/${context.state.cloneLesson.id}/resources/${payload.id}`);
            }
            
            context.commit("removeResourceInCloneLesson", payload);
        } catch (e) {
        }
    },

    async GET_LESSONS(context, payload) {
        try {
            const { data: result } = await axios.get(`/c/communities/${context.rootState.community.data.id}/lessons`, { 
                params: {
                    postId: payload.id,
                    action: payload.action
                }
            });
            context.commit("setLessons", result.data || []);
        } catch (e) {
            context.commit("setLessons", []);
        }
    },
};