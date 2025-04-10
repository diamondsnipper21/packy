import { mutations } from './mutations'

const state = {
    action: '', // action to dispatch
    actionParam: null, // action parameter
    button: '', // text on the button to display
    buttonStyle: 'is-medium community-blue-btn mb1em',
    show: false,
    title: '',
    description: '',
    type: '',
    transparent: false, // hide modal contents (Credit card)
    disabledConfirm: true,
    infoMessage: null,
    extraData: null,
    extraAction: null,
    size: null,

    childShow: false,
    childModalType: '',
    childModalExtraData: '',
    childVideoUploadShow: false,
    mediaModalLink: {},

    confirmTitle: '',
    confirmDesc: '',
    confirmAction: '',
    confirmParam: null
}

export const modal = {
    state,
    mutations
}
