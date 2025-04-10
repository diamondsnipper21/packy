<template>
    <div class="inner-modal-container">
        <h1 class="title is-4 font-weight-600 mt-05">{{ $t('common.add-media') }}</h1>
        <p class="mt1">
            {{ $t('common.upload-image-video') }}
        </p>

        <div 
            v-if="tempMediaType === 'video'"
            class="community-video-container mt1"
            :class="tempMediaPath.includes('youtube') || tempMediaPath.includes('vimeo') ? 'iframe-container' : ''"
        >
            <iframe
                v-if="tempMediaPath.includes('youtube')"
                :src="'https://www.youtube.com/embed/' + tempMediaPath.replace('youtube-', '')"
                width="100%"
                height="100%"
                frameborder="0"
            ></iframe>

            <iframe
                v-else-if="tempMediaPath.includes('vimeo')"
                :src="'https://player.vimeo.com/video/' + tempMediaPath.replace('vimeo-', '') + '?autoplay=0&loop=false&controls=true'"
                width="100%"
                height="100%"
                frameborder="0"
                allow="autoplay; fullscreen; picture-in-picture"
                allowFullScreen
            ></iframe>

            <video v-else :key="tempMediaPath" controls>
                <source :src="tempMediaPath" type="video/mp4" />
            </video>
        </div>
        <img v-else-if="tempMediaType === 'image'" :src="tempMediaPath" class="about-photo-img mt1" />
        <Dropzone v-else class="mt1" filetype="community_media" :selfobj="self" />

        <div class="mt2 text-right">
            <button class="button is-medium" @click="cancel">
                {{ $t('common.cancel') }}
            </button>
            <button
                class="button is-medium community-blue-btn ml-05"
                :class="button"
                :disabled="disabledConfirm"
                @click="addMediaConfirm"
            >{{ $t('common.add') }}</button>
        </div>

    </div>
</template>

<script>

import Dropzone from "../../components/General/Dropzone";

export default {
	name: 'AddMediaConfirm',
    components: {
        Dropzone
    },
    data () {
        return {
            processing: false
        };
    },
    created ()
    {
        this.self = this;
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
         * Returns temp media of community
         */
        tempMedia ()
        {
            return this.$store.state.community.tempMedia;
        },

        /**
         * Returns temp media type of community
         */
        tempMediaType ()
        {
            return typeof this.tempMedia.type !== 'undefined' ? this.tempMedia.type : null;
        },

        /**
         * Returns temp media path of community
         */
        tempMediaPath ()
        {
            return typeof this.tempMedia.path !== 'undefined' ? this.tempMedia.path : null;
        },

        /**
         * Returns add button disabled status
         */
        disabledConfirm ()
        {
            return this.tempMediaPath ? false : true;
        },

        /**
         * Returns button status class
         */
        button ()
        {
            return this.processing ? ' is-loading' : '';
        },
    },
    methods: {
        /**
         * Cancel confirm
         */
        cancel ()
        {
            this.$store.commit('setCommunityTempMedia', {});
            this.$store.commit('hideModal');
        },

        /**
         * Confirm for adding new media of community
         */
        async addMediaConfirm ()
        {
            this.processing = true;

            await this.$store.dispatch('ADD_MEDIA_FOR_COMMUNITY', {
                communityId: this.community.id,
                body: {
                    type: this.tempMediaType,
                    path: this.tempMediaPath
                }
            });

            this.processing = false;

            this.cancel();
        }
    }
}
</script>

<style scoped>

</style>
