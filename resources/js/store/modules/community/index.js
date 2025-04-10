import { actions } from './actions'
import { mutations } from './mutations'

const state = {
    loading: false,
    data: {
        id: 0,
        last_sent_notification: ''
    },
    plan: {},
    clone: {},

    lastSentNotification: null,
    broadcast: 0,

    levels: [],
    rewardLevel: {},

    price: {
        id: null,
        amount_monthly: null,
        amount_yearly: null,
        type: null
    },
    displayPriceForm: false,
    incubateurStartStep: 1,
    onboarding: {
        stripe: {
            step: 1
        }
    },
    extension: {},

    newPaymentNotification: false,
    tempMedia: {},

    aboutDescriptionView: 'view',

    mentionedMembers: [],
    mentionedMemberInfo: null,
    payoutFilterYear: new Date().getFullYear()
};

export const community = {
    state,
    actions,
    mutations
};
