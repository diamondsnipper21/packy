const MemberAccess = Object.freeze({
    ALLOWED: 1,
    PENDING: 0,
    DECLINE: -1,
    REMOVED: -2,
    BANNED: -3
});

const MemberRole = Object.freeze({
    MEMBER: 'member',
    MODERATOR: 'moderator',
    ADMIN: 'admin',
    OWNER: 'owner',
});

const MemberFilter = Object.freeze({
    ALL: 'all',
    MODERATOR: 'moderator',
    ADMIN: 'admin',
    ALLOWED: 'allowed',
    ONLINE: 'online',
    PENDING: 'pending',
    DECLINED: 'declined',
    BANNED: 'banned'
});

const BillingSectionType = Object.freeze({
    OVERVIEW: 1,
    UPDATE_CARD: 2,
    MANAGE_SUBSCRIPTION: 3,
    VIEW_HISTORY: 4,
    CANCEL_SUBSCRIPTION: 5,
    REACTIVATE: 6,
    CUSTOMIZE_INVOICES: 7
});

const SubscriptionStatus = Object.freeze({
    INCOMPLETE: 'incomplete',
    INCOMPLETE_EXPIRED: 'incomplete_expired',
    TRIALING: 'trialing',
    ACTIVE: 'active',
    PAST_DUE: 'past_due',
    CANCELED: 'canceled',
    UNPAID: 'unpaid',
    PAUSED: 'paused'
});

const InvoicePageLimit = 10;

const CommunityStatus = Object.freeze({
    PUBLISHED: 1,
    SUSPENDED: 2,
    INACTIVE: 3,
    PENDING: 4
});

const CommunityPrivacy = Object.freeze({
    PRIVATE: 'private',
    PUBLIC: 'public',
});

const CommunityOnboardSteps = Object.freeze({
    CREATED: 0,
    COVER_IMAGE: 1,
    DESCRIPTION: 2,
    INVITE_FRIEND: 3,
    POST: 4,
    COMPLETED: 5,
});

const PostType = Object.freeze({
    SCHEDULE: 1,
    REGULAR: 0,
});

const PostVisibility = Object.freeze({
    PENDING: 0,
    APPROVED: 1,
    REFUSED: 2,
});

const PostAction = Object.freeze({
    PIN_TO_PAGE: 'pin_to_page',
    UNPIN_FROM_PAGE: 'unpin_from_page',
});

const ClassroomViewType = Object.freeze({
    LIST: 0,
    ADD_CLASSROOM: 1,
    EDIT_CLASSROOM: 2,
    OVERVIEW: 3,
    ADD_SET: 4,
    EDIT_SET: 5,
    ADD_LESSON: 6,
    EDIT_LESSON: 7,
    ADD_LESSON_LINK: 8,
    EDIT_LESSON_LINK: 9,
    ADD_LESSON_FILE: 10,
    EDIT_LESSON_FILE: 11,
});

const RewardsSectionType = Object.freeze({
    OVERVIEW: 1,
    UPDATE_LEVEL: 2,
});

const IncubateurStartStep = Object.freeze({
    FIRST_STEP: 1,
    SECOND_STEP: 2,
    THIRD_STEP: 3,
    FORTH_STEP: 4,
    COMPLETED: 5
});

const ExtensionViewType = Object.freeze({
    LIST: 'list',
    EDIT: 'edit',
    AUTO_DM_FROM: 'auto_dm_from'
});

const MembershipSettingViewType = Object.freeze({
    MEMBERSHIP: 'membership',
    MEMBERSHIP_ROLE: 'membership_role',
    PAYMENTS: 'payments'
});

const TrackedPages = Object.freeze({
    ABOUT: 'about',
    START: 'start'
});

module.exports = {
    MemberAccess,
    MemberRole,
    MemberFilter,
    BillingSectionType,
    SubscriptionStatus,
    InvoicePageLimit,
    CommunityStatus,
    CommunityOnboardSteps,
    CommunityPrivacy,
    PostType,
    PostVisibility,
    PostAction,
    ClassroomViewType,
    RewardsSectionType,
    IncubateurStartStep,
    ExtensionViewType,
    MembershipSettingViewType,
    TrackedPages
}