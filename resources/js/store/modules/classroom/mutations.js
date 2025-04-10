export const mutations = {
    setRoomLoading(state, payload) {
        state.loading = payload
    },

    setRoomItemLoading(state, payload) {
        state.dataLoading = payload
    },

    setLessonLoading(state, payload) {
        state.lessonLoading = payload
    },

    setLessonEdit(state, payload) {
        state.lessonEdit = payload
    },

    setClassrooms(state, payload) {
        state.items = payload;
    },

    setClassroomPagination(state, payload) {
        state.pagination = payload
    },

    setClassroomInfo(state, payload) {
        state.data = payload;
    },

    updateClassroomInfo(state, payload) {
        state.data = {
            ...state.data,
            ...payload
        };
    },

    updateClassroomList(state, payload) {
        const items = state.items || []
        const itemIndex = items.findIndex(elem => elem.id == payload.id)
        items[itemIndex] = payload
        state.items = items
    },

    setClassroomExpand(state, payload) {
        const items = state.items || []
        const itemIndex = items.findIndex(elem => elem.id == payload.id)
        items[itemIndex].expand = payload.expand
        state.items = items
    },

    setClassroomListSet(state, payload) {
        const items = state.items || []
        const itemIndex = items.findIndex(elem => elem.id == payload.classroom_id)
        const navItems = items[itemIndex].items || []
        const navItemIndex = navItems.findIndex(elem => elem.id == payload.id && elem.type == 'set')
        if (navItemIndex != -1) {
            navItems[navItemIndex] = {
                ...navItems[navItemIndex],
                ...payload
            }
            items[itemIndex].items = navItems;
        }
        state.items = items
    },

    updateClassroomsAfterDelete(state, payload) {
        const classrooms = (state.items || []).filter(item => item.id !== payload.id);
        state.items = classrooms;
    },

    setClassroomSetExpand(state, payload) {
        const items = state.data.items || []
        const itemIndex = items.findIndex(elem => elem.id == payload.id && elem.type == 'set')
        if (itemIndex != -1) {
            if (payload.expand != undefined) items[itemIndex].expand = payload.expand
            state.data.items = items;
        }
    },

    setClassroomSet(state, payload) {
        const items = state.data.items || []
        const itemIndex = items.findIndex(elem => elem.id == payload.id && elem.type == 'set')
        if (itemIndex != -1) {
            items[itemIndex] = {
                ...items[itemIndex],
                ...payload
            }
            state.data.items = items;
        }
    },

    addClassroomSet(state, payload) {
        const items = state.data.items || []
        items.push(payload)
        state.data.items = items;

        // Update classrooms also
        let classroom = JSON.parse(JSON.stringify(state.data));
        let classrooms = JSON.parse(JSON.stringify(state.items));
        classrooms = classrooms.map((item) => {
            if (item.id === classroom.id) {
                item = classroom;
            }

            return { ...item };
        });
        state.items = JSON.parse(JSON.stringify(classrooms));
    },

    removeClassroomSet(state, payload) {
        const items = (state.data.items || []).filter(elem => !(elem.id == payload.id && elem.type == 'set'))
        state.data.items = items;
    },

    setSelectedSet(state, payload) {
        state.selectedSet = JSON.parse(JSON.stringify(payload))
    },

    setSelectedSetProp(state, payload) {
        state.selectedSet[payload.key] = payload.v
    },

    setSelectedLesson(state, payload) {
        state.selectedLesson = payload;
    },

    setSelectedLessonProp(state, payload) {
        state.selectedLesson[payload.key] = payload.v
    },

    addLessonInSet(state, payload) {
        const items = state.data.items || []
        if (payload.set_id) {
            const itemIndex = items.findIndex(elem => elem.id == payload.set_id && elem.type == 'set')
            if (itemIndex != -1) {
                if (items[itemIndex].lessons) {
                    items[itemIndex].lessons.push(payload);
                } else {
                    items[itemIndex].lessons = [payload];
                }
            }
        } else {
            items.push(payload)
        }
        state.data.items = items;

        // Update classrooms also
        let classroom = JSON.parse(JSON.stringify(state.data));
        let classrooms = JSON.parse(JSON.stringify(state.items));
        classrooms = classrooms.map((item) => {
            if (item.id === classroom.id) {
                item = classroom;
            }

            return { ...item };
        });
        state.items = JSON.parse(JSON.stringify(classrooms));
    },

    updateLessonInSet(state, payload) {
        let items = state.data.items || [];
        items = JSON.parse(JSON.stringify(items));
        if (payload.set_id) {
            const itemIndex = items.findIndex(elem => elem.id == payload.set_id && elem.type == 'set');
            if (itemIndex != -1) {
                if (typeof items[itemIndex].lessons === 'undefined') {
                    items[itemIndex].lessons = [];
                }

                const lessonIndex = items[itemIndex].lessons.findIndex(elem => elem.id == payload.id);
                if (lessonIndex != -1) {
                    items[itemIndex].lessons[lessonIndex] = payload;
                } else {
                    items[itemIndex].lessons.push(payload);
                }

                items[itemIndex].lessons.sort((a, b) => a.order - b.order);
            }

            // Remove existing old direct lesson
            items = items.filter(item => ((item.type === 'set') || (item.type === 'lesson' && item.id !== payload.id)));

            // Remove existing old lesson from set
            items = items.map((item) => {
                if (item.type === 'set') {

                    if (item.id === payload.set_id) {
                        item.expand = true;
                    } else {
                        item.expand = false;
                        if (typeof item.lessons !== 'undefined') {
                            let lessons = JSON.parse(JSON.stringify(item.lessons));
                            lessons = lessons.filter(elem => elem.id !== payload.id);
                            item.lessons = lessons;
                        }
                    }
                        
                }
                return { ...item };
            });
        } else {
            items = items.map((item) => {
                if (item.type === 'set') {
                    item.expand = false;
                    if (typeof item.lessons !== 'undefined') {
                        let lessons = JSON.parse(JSON.stringify(item.lessons));
                        lessons = lessons.filter(elem => elem.id !== payload.id);
                        item.lessons = lessons;
                    }
                }
                return { ...item };
            });

            const itemIndex = items.findIndex(elem => elem.id == payload.id && elem.type == 'lesson');
            if (itemIndex != -1) {
                items[itemIndex] = payload;
            } else {
                items.push(payload);
            }
        }

        items.sort((a, b) => a.order - b.order);

        state.data.items = items;
    },

    removeClassroomLesson(state, payload) {
        const items = state.data.items || []
        if (payload.set_id) {
            const itemIndex = items.findIndex(elem => elem.id == payload.set_id && elem.type == 'set')
            if (itemIndex != -1) {
                items[itemIndex].lessons = (items[itemIndex].lessons || []).filter(elem => !(elem.id == payload.id));
            }
            state.data.items = items;
        } else {
            state.data.items = items.filter(elem => !(elem.id == payload.id && elem.type == 'lesson'));
        }
    },

    setResourceInCloneLesson(state, payload) {
        const resources = state.cloneLesson.resources || []
        const itemIndex = resources.findIndex(elem => elem.id == payload.id)
        if (itemIndex == -1) {
            resources.push(payload);
        } else {
            resources[itemIndex] = payload;
        }

        state.cloneLesson.resources = resources;
    },
    removeResourceInCloneLesson(state, payload) {
        const resources = state.cloneLesson.resources || []
        state.cloneLesson.resources = resources.filter(elem => elem.id != payload.id);
    },

    setCloneLessonContent(state, payload) {
        state.cloneLesson.content = payload;
    },

    setCloneLesson(state, payload) {
        state.cloneLesson = payload;
    },

    updateCloneLessonMedia(state, payload) {
        state.cloneLesson.media = payload;
    },

    setCloneLessonPosts(state, payload) {
        state.cloneLesson.posts = payload;
    },

    setClassroomCloneData(state, payload) {
        state.cloneData = payload;
    },

    updateClassroomCloneData(state, payload) {
        state.cloneData = {
            ...state.cloneData,
            ...payload
        };
    },

    setClassroomCloneSet(state, payload) {
        state.cloneSet = payload;
    },

    updateClassroomCloneSet(state, payload) {
        state.cloneSet = {
            ...state.cloneSet,
            ...payload
        };
    },

    setLessonLink(state, payload) {
        state.lessonLink = payload;
    },

    setLessonFile(state, payload) {
        state.lessonFile = payload;
    },

    updateLessonFile(state, payload) {
        state.lessonFile = {
            ...state.lessonFile,
            ...payload
        };
    },

    setClassroomShow(state, payload) {
        state.classroomShow = payload;
    },

    toggleClassroomDropdownItem(state, payload) {
        state.dropdownId = payload;
    },

    resetSetDropdownItem(state) {
        const items = (state.data.items || []).map(item => {
            return {
                ...item,
                dropdown: false
            }
        })
        state.data.items = items;
    },

    swapClassroomOrder(state, payload) {
        const items = state.items || []
        const { current, next } = payload
        if (!next?.id) return

        const currentIndex = items.findIndex(elem => elem.id == current.id)
        const nextIndex = items.findIndex(elem => elem.id == next.id)
        if (nextIndex != -1) {
            const currentItem = items[currentIndex]
            items[currentIndex] = items[nextIndex]
            items[nextIndex] = currentItem
        } else {
            items[currentIndex] = next
        }

        state.items = items;
    },

    setIsSimpleClassrooms(state, payload) {
        state.isSimpleClassrooms = payload;
    },

    removeClassroomListSet(state, payload) {
        const items = state.items || []
        const itemIndex = items.findIndex(elem => elem.id == payload.classroom_id)
        const navItems = items[itemIndex].items || []
        items[itemIndex].items = navItems.filter(elem => !(elem.id == payload.id && elem.type == 'set'))
        state.items = items
    },

    swapClassroomListNavItemOrder(state, payload) {
        const items = state.items || []
        const { current, next, classroomId } = payload
        if (!next?.id) return
        const roomIndex = items.findIndex(elem => elem.id == classroomId)

        const navItems = items[roomIndex].items || []

        if (current.type == 'lesson' && current.set_id) {
            const currentSetIndex = navItems.findIndex(elem => elem.id == current.set_id && elem.type == 'set')
            if (currentSetIndex == -1) return;
            const currentSet = navItems[currentSetIndex]
            const navLessons = currentSet.lessons || [];

            const currentIndex = navLessons.findIndex(elem => elem.id == current.id && elem.type == current.type)
            const nextIndex = navLessons.findIndex(elem => elem.id == next.id && elem.type == next.type)

            if (nextIndex != -1) {
                const currentItem = navLessons[currentIndex]
                navLessons[currentIndex] = navLessons[nextIndex]
                navLessons[nextIndex] = currentItem
            } else {
                navLessons[currentIndex] = next
            }
            items[roomIndex].items[currentSetIndex].lessons = navLessons
        } else {
            const currentIndex = navItems.findIndex(elem => elem.id == current.id && elem.type == current.type)
            const nextIndex = navItems.findIndex(elem => elem.id == next.id && elem.type == next.type)
            if (nextIndex != -1) {
                const currentItem = navItems[currentIndex]
                navItems[currentIndex] = navItems[nextIndex]
                navItems[nextIndex] = currentItem
            } else {
                navItems[currentIndex] = next
            }
            items[roomIndex].items = navItems
        }

        state.items = JSON.parse(JSON.stringify(items));
    },

    setLessons(state, payload) {
        state.lessons = payload;
    },
}