<template>
	<div class="community-media-view-container" >
        <template v-if="type === 'image'">
            <img :src="path" />
        </template>
        <template v-else-if="type === 'audio'">
            <audio controls class="w100" :key="path">
                <source v-if="ext === 'ogg'" :src="path" type="audio/ogg" />
                <source v-else :src="path" type="audio/mpeg" />
            </audio>
        </template>
        <template v-else-if="type === 'video'">
            <iframe
                v-if="path.includes('youtube')"
                :src="'https://www.youtube.com/embed/' + path.replace('youtube-', '')"
                width="100%"
                height="100%"
                frameborder="0"
            ></iframe>

            <iframe
                v-else-if="path.includes('vimeo')"
                :src="'https://player.vimeo.com/video/' + path.replace('vimeo-', '') + '?autoplay=0&loop=false&controls=true'"
                width="100%"
                height="100%"
                frameborder="0"
                allow="autoplay; fullscreen; picture-in-picture"
                allowFullScreen
            ></iframe>

            <video v-else :key="path" controls>
                <source :src="path" type="video/mp4" />
            </video>
        </template>
        <template v-else-if="filename !== ''">
            <a
                :href="path"
                class="community-external-link tab-content-sub-title font-weight-500 resource-label"
                target="_blank"
            >
                <font-awesome-icon icon="fa fa-file" class="resource-icon" />&nbsp;
                {{ filename }}
            </a>
        </template>

        <div class="close-view-media" @click="cancel">
        	<font-awesome-icon icon="fa fa-times" />
        </div>
    </div>
</template>

<script>
export default {
	name: "ViewMedia",
	computed: {
		/**
         * extra data of child modal
         */
        extraData ()
        {
            return this.$store.state.modal.childModalExtraData;
        },

        /**
         * type of media
         */
        type ()
        {
            return this.extraData?.type || this.extraData?.media?.type || '';
        },

        /**
         * path of media
         */
        path ()
        {
            return this.extraData?.path || this.extraData?.media?.path || '';
        },

        /**
         * ext of media
         */
        ext ()
        {
            return this.extraData?.ext || this.extraData?.media?.ext || '';
        },

        /**
         * filename of media
         */
        filename ()
        {
            return this.extraData?.filename || this.extraData?.media?.filename || '';
        },
	},
    methods: {
    	cancel () {
			this.$store.commit('hideChildModal');
		},
    }
}
</script>

<style scoped>
    .community-media-view-container {
        width: 100%;
        padding-top: 650px;
        height: 0px;
        position: relative;
        border: 1px solid rgb(228, 228, 228);
        border-radius: 10px;
    }

    .community-media-view-container img,
    .community-media-view-container iframe,
    .community-media-view-container video {
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        border-radius: 10px;
        object-fit: contain;
    }

    .community-media-view-container img,
    .community-media-view-container iframe,
    .community-media-view-container video,
    .community-media-view-container audio,
    .community-media-view-container a {
        position: absolute;
    }

    .community-media-view-container img,
    .community-media-view-container iframe,
    .community-media-view-container video {
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        border-radius: 10px;
        object-fit: contain;
    }

    .community-media-view-container audio,
    .community-media-view-container a {
        transform: translate(-50%, -50%);
        left: 50%;
        top: 50%;
    }

    .close-view-media {
        position: absolute;
        top: -45px;
        right: -45px;
        background-color: #fff;
        border-radius: 50px;
        height: 50px;
        width: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .close-view-media i {
        font-size: 25px;
    }

    .tab-content-sub-title {
        max-width: 100%;
    }

    @media only screen and (max-width: 600px)
    {
        .community-media-view-container {
            padding-top: 300px;
        }

        .community-media-view-container img,
        .community-media-view-container iframe,
        .community-media-view-container video {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            border-radius: 10px;
            object-fit: contain;
        }

        .close-view-media {
            top: -30px;
            right: -30px;
            border-radius: 30px;
            height: 30px;
            width: 30px;
        }

        .close-view-media i {
            font-size: 20px;
        }
    }
</style>
