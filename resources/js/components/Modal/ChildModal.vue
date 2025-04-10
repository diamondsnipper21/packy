<template>
    <transition name="modal">
        <div @click="close" class="modal-mask modal-flex">
            <div
                @click.stop
                :class="getChildOuterContainer"
            >
                <!-- Main content -->
                <component :is="childModalTypes[this.childType]"/>
            </div>
        </div>
    </transition>
</template>

<script>

import { shallowRef } from 'vue'
import ViewMediaComp from './child/ViewMedia';
import AddLinkComp from './child/AddLink';
import ConfirmComp from './child/Confirm';
import DeleteRecurringEventComp from './child/DeleteRecurringEvent';
import EditRecurringEventComp from './child/EditRecurringEvent';
import AutoDmPreviewComp from './child/AutoDmPreview';
import BanConfirmComp from './child/BanConfirm';
import PointsInfoComp from './PointsInfo';
import TogglePinPostPageComp from './child/TogglePinPostPage';

export default {
    name: 'ChildModal',
    data ()
    {
        return {
            childModalTypes: {}
        };
    },
    created () {
        const ViewMedia = shallowRef(ViewMediaComp);
        const AddLink = shallowRef(AddLinkComp);
        const Confirm = shallowRef(ConfirmComp);
        const DeleteRecurringEvent = shallowRef(DeleteRecurringEventComp);
        const EditRecurringEvent = shallowRef(EditRecurringEventComp);
        const AutoDmPreview = shallowRef(AutoDmPreviewComp);
        const BanConfirm = shallowRef(BanConfirmComp);
        const PointsInfo = shallowRef(PointsInfoComp);
        const TogglePinPostPage = shallowRef(TogglePinPostPageComp);
        
        this.childModalTypes = {
            ViewMedia,
            AddLink,
            Confirm,
            DeleteRecurringEvent,
            EditRecurringEvent,
            AutoDmPreview,
            BanConfirm,
            PointsInfo,
            TogglePinPostPage
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
    },
    computed: {
        /**
         * Child type of media modal
         */
        childType ()
        {
            return this.$store.state.modal.childModalType;
        },

        /**
         * The inner-width dimensions
         */
        getChildOuterContainer ()
        {
            let getChildOuterContainer = 'child-modal-container';
            if (this.childType === 'ViewMedia') {
                getChildOuterContainer = 'media-child-modal-container';
            }
            
            return getChildOuterContainer;
        },
    },
    methods: {
        /**
         * Close the modal
         */
        close ()
        {
            this.$store.commit('hideChildModal');
        }
    }
}
</script>

<style scoped>
    .child-modal-container {
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
        display: inline-block;
        font-family: Helvetica, Arial, sans-serif;
        position: relative;
        margin: 30px auto;
        transition: all .3s ease;
        width: 600px;
        padding: 2em;
    }
    .media-child-modal-container {
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
        display: inline-block;
        font-family: Helvetica, Arial, sans-serif;
        position: relative;
        margin: 30px auto;
        transition: all .3s ease;
        width: 950px;
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
        overflow-y: scroll;
        overflow-x: hidden;
        transition: opacity .3s ease;
        text-align: center;
        z-index: 10000;
    }
    .top-right {
        position: absolute;
        top: 0;
        right: 1em;
        padding: .3em;
        cursor: pointer;
        z-index: 9999;
    }

    @media only screen and (max-width: 600px)
    {
        .child-modal-container {
            width: 80%;
            padding: 1em;
        }

        .media-child-modal-container {
            width: 80%;
        }
    }
</style>
