import { actions } from './actions'
import { mutations } from './mutations'
import { ClassroomViewType } from '../../../data/enums'
const state = {
    data: {},
    dataLoading: false,
 
    items: [],
    pagination: {},

    loading: false,

    selectedLesson: null,
    selectedSet: {},
    cloneLesson: null,

    lessonLoading: false,
    lessonEdit: false,

    cloneData: {},
    cloneSet: {},

    classroomShow: ClassroomViewType.LIST,

    isSimpleClassrooms: 0,

    dropdownId: '',
    
    lessonLink: {},
    lessonFile: {},

    lessons: []
}

export const classroom = {
    state,
    actions,
    mutations
};
