<template>
    <transition name="modal">
        <div @mousedown="close" class="modal-mask modal-flex" :class="getModalMaskContainer">
            <div
                @mousedown.stop
                :class="getOuterContainer"
                id="community_modal_container"
            >
                <!-- Header -->
                <div class="modal-header" v-show="!transparent">
                    <h1 class="title is-4 font-weight-600 mt-05">{{ this.title }}</h1>
                    <!-- Close button -->
                    <font-awesome-icon icon="fa fa-times" class="top-right hide-mobile" @click="close" />
                </div>

                <!-- Main content -->
                <component
                    v-if="isShow"
                    :is="modalTypes[this.type]"
                    :type="this.type"
                />

                <!-- Footer -->
                <div v-show="!transparent">

                    <!-- Cancel, Submit on non-mobile -->
                    <div class="hide-mobile">
                        <button class="button is-medium" @click="close">
                            {{ $t('common.cancel') }}
                        </button>

                        <button
                            :class="button"
                            @click="action"
                            :disabled="disabledConfirm"
                            class="ml-05"
                        >{{ this.buttonText }}</button>
                    </div>

                    <!-- Submit, Cancel on mobile only -->
                    <div class="mobile-only">
                        <button
                            :class="button"
                            @click="action"
                            :disabled="disabledConfirm"
                            style="margin-bottom: .5em;"
                        >{{ this.buttonText }}</button>

                        <button class="button is-medium ml-05"
                                :class="(isInvisible) ? 'invisable-button-border-mobile' : ''"
                                @click="close">
                            {{ $t('common.cancel') }}
                        </button>
                    </div>
                </div>

                <!-- Info footer message -->
                <div v-if="infoMessage !== null && typeof infoMessage !== 'undefined'">
                    <p class="info-title">
                        <font-awesome-icon icon="fa fa-question-circle" /> &nbsp; {{ infoMessage }}
                    </p>
                </div>

                <MentionTooltip id="mention_tooltip_modal_container" />
            </div>
        </div>
    </transition>
</template>

<script>

import { shallowRef } from 'vue'
import ViewCommunityPostComp from './ViewCommunityPost';
import ViewCommunityEventComp from './ViewCommunityEvent';
import LevelAccessAlertComp from './LevelAccessAlert';
import PointsInfoComp from './PointsInfo';
import CommunitySettingComp from './CommunitySetting';
import MembershipSettingComp from './MembershipSetting';
import NewCommunityComp from './NewCommunity';
import NewTempCommunityComp from './NewTempCommunity';
import LoginComp from './Login';
import SignupComp from './Signup';
import ForgotPasswordComp from './ForgotPassword';
import ResetPasswordComp from './ResetPassword';
import CheckYourEmailComp from './CheckYourEmail';
import InviteUserComp from './InviteUser';
import CompleteProfileComp from './CompleteProfile';
import JoinComp from './Join';
import PendingComp from './Pending';
import ChatDetailComp from './ChatDetail';
import TopRightNavContentComp from './TopRightNavContent';
import DeleteConfirmComp from './DeleteConfirm';
import AddMediaConfirmComp from './AddMediaConfirm';
import DeclineMemberRequestComp from './DeclineMemberRequest';
import AddEditClassroomComp from './classroom/AddEditClassroom';
import EditSetComp from './classroom/EditSet';
import AddEditLessonResourceComp from './classroom/AddEditLessonResource';
import SchedulePostComp from './SchedulePost';
import BlankModalComp from './BlankModal';
import SubscriptionPurchaseComp from "./SubscriptionPurchase.vue";
import SubscriptionPurchaseSucceededComp from "./SubscriptionPurchaseSucceeded.vue";
import SubscriptionOverdueSucceededComp from "./SubscriptionOverdueSucceeded.vue";
import CompletePayoutComp from "./CompletePayout.vue";

import MentionTooltip from "../General/Elements/MentionTooltip.vue";

export default {
    name: 'Modal',
    components: {
        MentionTooltip
    },
    data ()
    {
        return {
            processing: false,
            modalTypes: {}
        };
    },
    created () {
        const ViewCommunityPost = shallowRef(ViewCommunityPostComp);
        const ViewCommunityEvent = shallowRef(ViewCommunityEventComp);
        const LevelAccessAlert = shallowRef(LevelAccessAlertComp);
        const PointsInfo = shallowRef(PointsInfoComp);
        const CommunitySetting = shallowRef(CommunitySettingComp);
        const MembershipSetting = shallowRef(MembershipSettingComp);
        const NewCommunity = shallowRef(NewCommunityComp);
        const NewTempCommunity = shallowRef(NewTempCommunityComp);
        const Login = shallowRef(LoginComp);
        const Signup = shallowRef(SignupComp);
        const ForgotPassword = shallowRef(ForgotPasswordComp);
        const ResetPassword = shallowRef(ResetPasswordComp);
        const CheckYourEmail = shallowRef(CheckYourEmailComp);
        const InviteUser = shallowRef(InviteUserComp);
        const CompleteProfile = shallowRef(CompleteProfileComp);
        const Join = shallowRef(JoinComp);
        const Pending = shallowRef(PendingComp);
        const ChatDetail = shallowRef(ChatDetailComp);
        const TopRightNavContent = shallowRef(TopRightNavContentComp);
        const DeleteConfirm = shallowRef(DeleteConfirmComp);
        const AddMediaConfirm = shallowRef(AddMediaConfirmComp);
        const DeclineMemberRequest = shallowRef(DeclineMemberRequestComp);
        const AddEditClassroom = shallowRef(AddEditClassroomComp);
        const EditSet = shallowRef(EditSetComp);
        const AddEditLessonResource = shallowRef(AddEditLessonResourceComp);
        const SchedulePost = shallowRef(SchedulePostComp);
        const BlankModal = shallowRef(BlankModalComp);
        const SubscriptionPurchase = shallowRef(SubscriptionPurchaseComp);
        const SubscriptionPurchaseSucceeded = shallowRef(SubscriptionPurchaseSucceededComp);
        const SubscriptionOverdueSucceeded = shallowRef(SubscriptionOverdueSucceededComp);
        const CompletePayout = shallowRef(CompletePayoutComp);
        
        this.modalTypes = {
            ViewCommunityPost,
            ViewCommunityEvent,
            LevelAccessAlert,
            PointsInfo,
            CommunitySetting,
            MembershipSetting,
            NewCommunity,
            NewTempCommunity,
            Login,
            Signup,
            ForgotPassword,
            ResetPassword,
            CheckYourEmail,
            InviteUser,
            CompleteProfile,
            Join,
            Pending,
            ChatDetail,
            TopRightNavContent,
            DeleteConfirm,
            AddMediaConfirm,
            DeclineMemberRequest,
            AddEditClassroom,
            EditSet,
            AddEditLessonResource,
            SchedulePost,
            BlankModal,
            SubscriptionPurchase,
            SubscriptionPurchaseSucceeded,
            SubscriptionOverdueSucceeded,
            CompletePayout
        };
    },
    mounted ()
    {
        // Close modal with 'esc' key
        document.addEventListener('keydown', (e) => {
            if (e.keyCode == 27)
            {
                this.close();
            }
        });

        const mentionTooltipModalRef = document.getElementById('mention_tooltip_modal_container');
        if (mentionTooltipModalRef) {
            mentionTooltipModalRef.hidden = true;
        }
    },
    computed: {
        /**
         * Returns community
         */
        community ()
        {
            return this.$store.state.community.data;
        },

        /**
         * Add ' is-loading' when processing
         */
        button ()
        {
            let button = 'button ';

            return (this.processing)
                ? button + this.$store.state.modal.buttonStyle + ' is-loading'
                : button + this.$store.state.modal.buttonStyle;
        },

        /**
         * Text to display on the action button
         */
        buttonText ()
        {
            return this.$store.state.modal.button;
        },

        /**
         * Shortcut for modal.type
         */
        type ()
        {
            return this.$store.state.modal.type;
        },

        /**
         * size of modal
         */
        size ()
        {
            return this.$store.state.modal.size;
        },

        /**
         * The inner-width dimensions
         * 
         * possible container options
         * modal-container
         * medium-modal-container
         * large-modal-container
         * 
         */
        getOuterContainer ()
        {
            let getOuterContainer = 'modal-container';
            if (this.size === 'large') {
                getOuterContainer = 'large-modal-container';
            } else if (this.size === 'small') {
                getOuterContainer = 'small-modal-container';
            }

            if (this.type === 'ViewCommunityPost') {
                getOuterContainer = 'large-modal-container';
            } else if (this.type === 'PointsInfo' || this.type === 'Pending') {
                getOuterContainer = 'small-modal-container';
            } else if (this.type === 'CommunitySetting' || this.type === 'MembershipSetting') {
                getOuterContainer = 'large-modal-container p0';
            } else if (this.type === 'ViewCommunityEvent') {
                getOuterContainer = 'modal-container p0';
            } else if (this.type === 'ChatDetail' || this.type === 'LevelAccessAlert') {
                getOuterContainer += ' p0';
            }
            
            return getOuterContainer;
        },

        getModalMaskContainer ()
        {
            let getModalMaskContainer = '';
            if (this.type === 'TopRightNavContent') {
                getModalMaskContainer = 'right-sidebar-modal-mask';
            }
            
            return getModalMaskContainer;
        },

        /**
         * Get the title for the modal
         */
        title ()
        {
            return this.$store.state.modal.title;
        },

        /**
         * If transparent is true, hide white background + buttons
         */
        transparent ()
        {
            return this.$store.state.modal.transparent;
        },

        /**
        * If disabledConfirm is true, confirm button is disabled, default = false
        */
        disabledConfirm ()
        {
            if (this.processing) return true;

            return this.$store.state.modal.disabledConfirm;
        },

        /**
        * Message to be displayed at bottom of modal, if any
        */
        infoMessage ()
        {
            return this.$store.state.modal.infoMessage;
        },

        /**
        * True is "cancel" button should have an invisable border
        */
        isInvisible ()
        {
            return (this.$store.state.modal.action === 'STORE_INVOICE' || this.$store.state.modal.action === 'REMOVE_PAYMENT_REQUEST');
        },

        isShow ()
        {
            return this.$store.state.modal.show;
        },

        /**
         * Returns mentioned member info
         */
        mentionedMemberInfo ()
        {
            return this.$store.state.community.mentionedMemberInfo;
        },

    },
    methods: {

        /**
         * Action to dispatch when primary button is pressed
         */
        async action ()
        {
            this.processing = true;

            await this.$store.dispatch(this.$store.state.modal.action, this.$store.state.modal.actionParam);

            this.processing = false;
        },

        /**
         * Close the modal
         */
        close ()
        {
            this.$store.commit('hideModal');
        },
    }
}
</script>

<style scoped lang="scss">

    .modal-enter .modal-container,
    .modal-leave-active .modal-container {
        -webkit-transform: scale(1.1);
        transform: scale(1.1);
    }

    .modal-enter {
        opacity: 0;
    }
    .modal-leave-active {
        opacity: 0;
    }

    .modal-enter .modal-container,
    .modal-leave-active .modal-container {
        -webkit-transform: scale(1.1);
        transform: scale(1.1);
    }

    .inner-modal-container {
        padding: 2em;
        min-height: 150px;
    }

    .small-modal-container {
      background-color: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
      display: inline-block;
      font-family: Helvetica, Arial, sans-serif;
      padding: 0;
      position: relative;
      margin: 20px auto;
      transition: all .3s ease;
      width: 460px;
      min-height: 300px !important;
      overflow: hidden;
    }

    .modal-container {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
        display: inline-block;
        font-family: Helvetica, Arial, sans-serif;
        padding: 0;
        position: relative;
        margin: 20px auto;
        transition: all .3s ease;
        width: 585px;
        overflow: hidden;
    }

    .medium-modal-container {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
        display: inline-block;
        font-family: Helvetica, Arial, sans-serif;
        padding: 0;
        position: relative;
        margin: 20px auto;
        transition: all .3s ease;
        width: 800px;
      overflow: hidden;
    }

    .large-modal-container {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
        display: inline-block;
        font-family: Helvetica, Arial, sans-serif;
        padding: 0;
        position: relative;
        margin: 20px auto;
        transition: all .3s ease;
        width: 910px;
        overflow: hidden;
    }

    .p0 {
        padding: 0px;
    }

    @media only screen and (min-width:599px) {
        #community_modal_container {
            min-height: 350px;
        }
    }

    .small-modal-container {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
        display: inline-block;
        font-family: Helvetica, Arial, sans-serif;
        padding: 1em 0;
        position: relative;
        margin: 20px auto;
        transition: all .3s ease;
        width: 460px;
        min-height: 250px !important;
    }

    #community_modal_container::-webkit-scrollbar {
        background-color: #f4f5f8;
        width: 9px;
    }

    #community_modal_container::-webkit-scrollbar-thumb {
        background-color: #c0c0c0;
        border: 0.25em solid #c0c0c0;
        border-radius: 1em;
    }

    .modal-flex {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .modal-header {
        margin-bottom: 1em;
        position: relative;
        text-align: center;
    }

    .modal-mask {
        background-color: rgba(0, 0, 0, .5);
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        transition: opacity .3s ease;
        text-align: center;
        z-index: 1000;
    }

    .right-sidebar-modal-mask {
        background-color: rgba(0, 0, 0, .5);
        position: fixed;
        top: 0;
        right: 0;
        left: auto;
        width: 100%;
        height: 100%;
        z-index: 1000;
    }

    .top-right {
        position: absolute;
        top: 0;
        right: 1em;
        padding: .3em;
        cursor: pointer;
        z-index: 9999;
    }

    .info-title {
      color: #459ef5;
      cursor: pointer;
      margin-top: 1.75em;
    }

    @media only screen and (max-width: 600px) {
        .mobile-only {
            padding-bottom: 0px;
        }

        .modal-container {
            width: 85%;
            margin: 10px auto;
            padding: 0.5em 0;
        }

        .medium-modal-container {
            width: 85%;
            margin: 10px auto;
            padding: 0.5em 0;
        }

        .large-modal-container {
            width: 100%;
            height: 100%;
            margin: 0px auto;
            padding: 0.5em 0;
            border-radius: 0px;
            padding: 0px;
        }

        .small-modal-container {
            width: 85%;
            margin: 10px auto;
            padding: 0.5em 0;
            min-height: 200px !important;
        }

        .right-sidebar-modal-mask .modal-container {
            width: 300px;
            height: 100%;
            padding: 0px;
            margin: 0px;
            border-top-right-radius: 0px;
            border-bottom-right-radius: 0px;
            position: absolute;
            right: 0px;
            left: auto;
            transition: all 0.5s ease;
            overflow-y: scroll;
            overflow-x: hidden;
        }

        #community_modal_container {
            min-height: 250px;
        }

        #community_modal_container::-webkit-scrollbar {
            width: 5px;
        }

        #community_modal_container::-webkit-scrollbar-thumb {
            border-radius: 5px;
        }

        .inner-modal-container {
            padding: 0.5em 1em;
            min-height: 80px;
        }
    }
</style>
