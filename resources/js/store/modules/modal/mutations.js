import router from '../../../router';
export const mutations = {

    /**
     * Hide the modal
     */
    hideModal(state)
    {
        state.show = false;

        document.documentElement.style.overflow = "auto";
        if (typeof document.getElementById("community_modal_container") !== 'undefined' && document.getElementById("community_modal_container")) {
            document.getElementById("community_modal_container").classList.remove("show");
        }

        state.size = null;
        state.extraAction = null;
        state.actionParam = null;
        state.extraData = null;

        // Remove url search params when hiding modal
        const currentUrl = window.location.href;
        const urlObj = new URL(currentUrl);
        const urlDomain = urlObj.origin;

        if (urlObj.search !== '' || state.type === 'ViewCommunityPost') {
            urlObj.search = '';
            urlObj.hash = '';
            const resultUrl = urlObj.toString();
            const resultPath = resultUrl.replace(urlDomain, '');
            const params = resultPath.split("/");

            if (typeof params[1] !== 'undefined') {
                router.push("/" + params[1]).catch(err => {});
            }
        }
    },

    /**
     * Show the modal
     */
    showModal(state, payload)
    {
        state.show = true;
        state.type = payload.type;
        state.title = payload.title;
        state.transparent = false;

        if (typeof payload.action !== 'undefined') {
            state.action = payload.action;
        }

        if (typeof payload.actionParam !== 'undefined') {
            state.actionParam = payload.actionParam;
        }

        if (typeof payload.description !== 'undefined') {
            state.description = payload.description;
        }

        if (typeof payload.extraAction !== 'undefined') {
            state.extraAction = payload.extraAction;
        }

        if (typeof payload.button !== 'undefined') {
            state.button = payload.button;
        }

        if (typeof payload.transparent !== 'undefined') {
            state.transparent = payload.transparent;
        }

        if (typeof payload.disabledConfirm !== 'undefined') {
            state.disabledConfirm = payload.disabledConfirm;
        }

        if (typeof payload.infoMessage !== 'undefined') {
            state.infoMessage = payload.infoMessage;
        }

        if (typeof payload.buttonStyle !== 'undefined') {
            state.buttonStyle = payload.buttonStyle;
        }

        if (typeof payload.extraData !== 'undefined') {
            state.extraData = payload.extraData;
        }

        document.documentElement.style.overflow = "hidden";
    },

    setModalExtraData(state, payload)
    {
        state.extraData = payload;
    },

    /**
     * Set modal extra data property
     */
    setModalExtraDataProperty(state, payload)
    {
        state.extraData[payload.key] = payload.v;
    },

    setModalSize(state, payload)
    {
        state.size = payload;
    },

    setDisabledConfirm(state, payload)
    {
        state.disabledConfirm = payload;
    },

    /**
     * show child modal
     */
    showChildModal(state, payload)
    {
        state.childModalType = payload.modalType;
        state.childVideoUploadShow = false;
        if (typeof payload.extraData !== 'undefined') {
            state.childModalExtraData = payload.extraData;

            if (state.childModalExtraData.type === 'video') {
                state.childVideoUploadShow = true;
            }
        }

        state.mediaModalLink = {};
        state.childShow = true;
    },

    /**
     * Reset child video upload show
     */
    resetChildVideoUploadShow(state)
    {
        state.childVideoUploadShow = false;
    },

    /**
     * Set child modal extra data property
     */
    setChildModalExtraDataProperty(state, payload)
    {
        state.childModalExtraData[payload.key] = payload.v;
    },

    /**
     * Set media modal link
     */
    setMediaModalLink(state, payload)
    {
        state.mediaModalLink = payload;
    },

    /**
     * Set media modal link property
     */
    setMediaModalLinkProperty(state, payload)
    {
        state.mediaModalLink[payload.key] = payload.v;
    },

    /**
     * Hide child modal
     */
    hideChildModal(state)
    {
        state.childShow = false;
        setTimeout(() => {
            state.childModalType = '';
            state.childModalExtraData = '';
            state.childVideoUploadShow = false;
        }, 500);
    },

    showResetModal(state){
        state.type = 'BlankModal'
    }
};
