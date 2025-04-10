import { actions } from './actions'
import { mutations } from './mutations'

const state = {
    app_name: null,
    scheduledItems: [],
    scheduledLoading: false,

    items: [],
    loading: false,

    pagination: {},

    data: {
        id: 0,
        member_id: 0,
        member: null,
        community_id: 0,
        title: '',
        content: null,
        path: '',
        medias: [],
        pinned: 0,
        broadcast: 0,
        category_id: 0,
        commentsCount: 0,
        comments: [],
        hierarchicalComments: [],
        disable_comment: 0,
        likes: null,
        polls: [],
        allowMultipleAnswersChecked: false,

        scheduled: false,
        publish_at: '',
        publish_timezone: '',
        repeat: false,
        repeat_end_at: '',
        repeat_every: '',
        repeat_on: '',
        origin_id: 0,
        send_notification: false,
        tab_classrooms: true,
        tab_calendar: true,
        tab_leaderboard: true,
        invoice_data: {}
    },

    selectedCategoryId: 0,
    selectedFilter: 'none',
    selectedSort: 'newest',
    
    addPostShow: false,

    comment: {},
    editedComment: {},
    replyComment: {},

    viewPostMode: 'view',
    parentPage: ''
}

export const post = {
    state,
    actions,
    mutations
};
