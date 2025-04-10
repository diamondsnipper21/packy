import { actions } from './actions'
import { mutations } from './mutations'

const state = {  
    loading: true,

    settings: {},

    affSettings: {
        paypal: '', // email to receive affiliate payouts
        iban: '', // iban to receive affiliate payouts
        lang: 'fr', // language to display
        paypalSelected: false, // commissions payment method
        ibanSelected: false,  // commissions payment method
    },

    selectedTab: 'community',

    community: {},
    newCommunity: {},

    communityEvent: {},
    communityEvents: [],
    monthlyEvents: [],
    eventMonth: 0,

    eventLinks: [
        'google',
        'apple',
        'outlook',
        'outlookcom',
        'yahoo'
    ],

    communityClassrooms: [],
    communityClassroom: {},

    navBarOpen: false, // mobile view,
    wolfeoLoginUrl: '',

    notifications: [],
    notificationFilter: 'all',
    unreadNotificationsCnt: 0,

    chatUsers: [],
    chatUserFilter: 'all',
    blockedUserIds: [],
    unreadChatsCnt: 0,
    membersCount: 0,

    newChat: {},
    chatToId: 0,
    chatMessages: [],

    chatOpponentUser: null,

    searchUserFilter: '',
    searchMemberFilter: '',

    errors: {}, // any errors from backend form validation,

    calendarViewMode: 'date_view',

    leaderboard: {
        neededPoints: null
    },

    moderatorMembers: [],
    adminMembers: [],
    allowedMembers: [],
    pendingMembers: [],
    paginatedMembers: [],
    bannedMembers: [],
    memberFilter: 'all',

    communitySettings: {
        contentLoading: false,
        chat: 'off',
        ruleShow: 'list',
        groupShow: 'list',
        categoryShow: 'list',
        extensionShow: 'list',
        linkShow: 'list',
        calendarShow: 'list',
        calendarEvent: null,
        classroomShow: 'list',
        classroom: null,
        set: null,
        lesson: null,
        lessonLink: null,
        lessonFile: null,
        statsData: null,
        billingSection: 1
    },

    customer: {},

    dropzoneError: {
        'logo': null,
        'summary_photo': null,
        'user_photo': null
    },

    inviteReferMember: null,

    inviteShareLink: null,

    navContentTab: 'notification',

    paginatedUsers: [],
    paginatedUserSearch: '',
    communities: [],

    uploading: false,

    declineJoinFeedback: '',
    declineJoinShareNotify: false,

    verificationCode: null,
    twoFactorExpires: null,

    displayCommunitySwitch: false,
    displayCommunitySwitchMobile: false,
};

export const communitycenter = {
    state,
    actions,
    mutations
};
