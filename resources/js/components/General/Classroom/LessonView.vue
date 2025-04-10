<template>
    <div class="box" :class="lesson?.id ? '' : 'empty-lesson-box'" style="position: relative;">
        <loading v-if="loading" :active.sync="loading" :is-full-page="false" />

        <div v-if="accessLevel(classroom) > 0" class="cc-lock-section border-radius-6px">
            <font-awesome-icon icon="fa fa-lock" class="access-locked-level-icon" />
            <div class="access-locked-level-desc">
                {{ $t('community.classroom.unlock-at-level').replace("#level#",
                accessLevel(classroom)) }}
            </div>
        </div>
        <div v-else-if="!(lesson?.id)">
            {{ $t('community.community.classroom.empty-lesson-placeholder') }}
        </div>
        <div v-else>
            <div class="lesson-header">
                <div class="lesson-title">
                    {{ lesson.title }}
                </div>

                <div v-if="auth" class="lesson-mark" @click="toggleLessonCompleted">
                    <a href="#"
                        :title="lesson.completed ? $t('community.community.classroom.lesson-mark-incomplete-tooltip') : $t('community.community.classroom.lesson-mark-complete-tooltip')"
                        class="tooltip">
                        <span class="flex align-items-center jc flex-column h100-percent" title="">
                            <font-awesome-icon icon="fa fa-circle-check" class="tooltip completed-status-icon"
                                :class="lesson.completed ? 'completed' : ''" />
                        </span>
                    </a>
                </div>
            </div>

            <div class="lesson-body">
                <div v-if="audioType" class="w100">
                    <audio controls class="w100" :key="lessonMedia">
                        <source v-if="lessonMediaExt === 'ogg'" :src="lessonMedia" type="audio/ogg">
                        <source v-else :src="lessonMedia" type="audio/mpeg">
                    </audio>
                </div>
                <div v-else-if="lessonMedia !== ''" class="lesson-media-container"
                    :class="lessonMedia.includes('youtube') || lessonMedia.includes('vimeo') ? 'iframe-container' : ''">
                    <iframe v-if="lessonMedia.includes('youtube')"
                        :src="'https://www.youtube.com/embed/' + lessonMedia.replace('youtube-', '')" width="100%"
                        height="100%" frameborder="0"></iframe>

                    <iframe v-else-if="lessonMedia.includes('vimeo')"
                        :src="'https://player.vimeo.com/video/' + lessonMedia.replace('vimeo-', '') + '?autoplay=0&loop=false&controls=true'"
                        width="100%" height="100%" frameborder="0" allow="autoplay; fullscreen; picture-in-picture"
                        webkitallowfullscreen mozallowfullscreen allowfullscreen
                        referrerpolicy="strict-origin"></iframe>

                    <video v-else :key="lessonMedia" controls>
                        <source :src="lessonMedia" type="video/mp4" />
                    </video>
                </div>

                <div class="lesson-content mt2" v-html="lesson.content"></div>

                <div v-if="(resources || []).length > 0" class="flex mt2">
                    <div class="flex-1 w100">
                        <p class="text-left input-label font-weight-600 mb1">
                            {{ $t('community.classroom.resources') }}
                        </p>

                        <div v-for="item in resources" :key="item.id" class="flex align-items-center pb-05 w100">
                            <div v-if="item.type === 'video'" class="resource-icon-section mr-05">
                                <font-awesome-icon icon="fa fa-video" class="resource-icon" />
                            </div>

                            <div v-else-if="item.type === 'audio'" class="resource-icon-section mr-05">
                                <font-awesome-icon icon="fa fa-microphone" class="resource-icon" />
                            </div>

                            <div v-else-if="item.type === 'image'" class="resource-icon-section mr-05">
                                <font-awesome-icon icon="fa fa-image" class="resource-icon" />
                            </div>

                            <div v-else-if="item.type === 'pdf'" class="resource-icon-section mr-05">
                                <font-awesome-icon icon="fa fa-file" class="resource-icon" />
                            </div>

                            <div v-else-if="item.type === 'link'" class="resource-icon-section mr-05">
                                <font-awesome-icon icon="fa fa-link" class="resource-icon" />
                            </div>

                            <a :href="item.url"
                                class="community-external-link tab-content-sub-title font-weight-500 resource-label"
                                target="_blank">
                                {{ item.label }}
                            </a>
                        </div>
                    </div>
                </div>

                <div v-if="transcript !== ''" class="flex mt2 mb1">
                    <div class="flex-1">
                        <div class="flex align-items-center jcb">
                            <p class="text-left input-label font-weight-600">
                                {{ $t('community.classroom.transcript') }}
                            </p>
                            <a v-if="transcriptExpanded" class="community-external-link" @click="collapseTranscript">
                                {{ $t('common.collapse') }}
                            </a>
                            <a v-else class="community-external-link" @click="expandTranscript">
                                {{ $t('common.expand') }}
                            </a>
                        </div>

                        <div class="mt1" :class="transcriptExpanded ? 'full-content' : 'ellipsis-content'">
                            {{ transcript }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="lesson-footer" v-if="posts.length > 0">
                <PostElement v-for="post in posts" :key="post.id" :post="post" :postType="PostType.REGULAR" :parentId="lesson.id" />
            </div>
        </div>
    </div>
</template>

<script>

import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/css/index.css'
import { MemberAccess, PostType } from '../../../data/enums';
import isManager from "../../../mixins/util";
import PostElement from '../Posts/PostElement';

export default {
    name: 'LessonView',
    mixins: [
        isManager
    ],
    components: {
        Loading,
        PostElement
    },
    data() {
        return {
            MemberAccess,
            PostType,
            transcriptExpanded: false,
        }
    },
    computed: {
        auth() {
            return this.$store.state.auth.loggedIn;
        },

        member() {
            return this.$store.state.member.data;
        },

        memberExist() {
            return this.member?.id ? true : false;
        },

        access() {
            return this.member?.access || null;
        },

        role() {
            return this.member?.role || null;
        },

        classroom() {
            return this.$store.state.classroom.data || {};
        },

        level() {
            return this.member?.level || 0;
        },

        community() {
            return this.$store.state.community.data;
        },

        lesson() {
            return this.$store.state.classroom.selectedLesson || {};
        },

        posts() {
            let posts = [];
            if (typeof this.lesson.posts !== 'undefined' && this.lesson.posts.length > 0) {
                posts = this.lesson.posts;
            }

            return posts;
        },

        loading() {
            return this.$store.state.classroom.lessonLoading;
        },

        lessonMedia() {
            return this.lesson.media;
        },

        lessonMediaExt() {
            let ext = '';
            let mediaUrl = this.lessonMedia;
            if (mediaUrl !== '') {
                ext = mediaUrl.split(".").pop();
            }

            if (ext !== '') {
                ext = ext.toLowerCase();
            }

            return ext;
        },

        audioType() {
            const audioTypes = ['mp3', 'wav', 'ogg'];

            return audioTypes.includes(this.lessonMediaExt) ? true : false;
        },

        resources() {
            return this.lesson.resources || [];
        },

        transcript() {
            return this.lesson.transcript;
        },

        groups() {
            return this.community.groups;
        },

    },
    methods: {
        /**
         * Return classroom accessability
         */
        accessClassroom(item) {
            let accessClassroom = false;
            if (this.isManager(this.role)) {
                accessClassroom = true;
            } else {
                if (item.access_type === 'all') {
                    accessClassroom = true;
                } else if (item.access_type === 'only_member') {
                    let values = (item.access_value || '').split(",");

                    let memberGroups = (this.member.groups || []).map(item => `group_${item.id}`)
                    memberGroups = [...memberGroups, this.member.id.toString()]
                    for (const elem of values) {
                        const value = elem.toString();
                        if (memberGroups.includes(value)) {
                            accessClassroom = true;
                            continue;
                        }
                    }
                }

                if (accessClassroom && parseInt(item.level) > parseInt(this.member.level)) {
                    accessClassroom = false;
                }
            }

            return accessClassroom;
        },

        /**
         * Return access level
         */
        accessLevel(item) {
            let accessLevel = 0;
            if (!this.accessClassroom(item)) {
                if (parseInt(this.level) < parseInt(item.level)) {
                    accessLevel = item.level;
                }
            }

            return accessLevel;
        },

        collapseTranscript() {
            this.transcriptExpanded = false;
        },

        expandTranscript() {
            this.transcriptExpanded = true;
        },

        async toggleLessonCompleted() {
            if (this.auth) {
              if (this.access === MemberAccess.ALLOWED) {
                if (this.lesson.id > 0) {
                  await this.$store.dispatch('COMPLETE_CLASSROOM_LESSON', {
                    set_id: this.lesson.set_id || 0,
                    id: this.lesson.id
                  });
                }
              } else if (this.access === MemberAccess.PENDING) {
                this.$store.commit('showModal', {
                  type: 'Pending',
                  transparent: true
                });
              } else {
                this.$store.commit('showModal', {
                  type: 'Join',
                  transparent: true
                });
              }
            } else {
                this.showLogin();
            }
        },

    }
}
</script>
<style lang="scss" scoped>
$color_1: rgb(144, 144, 144);
$color_2: rgb(0, 158, 93);
$color_3: #fff;
$background-color_1: rgb(228, 228, 228);

.lesson-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px;
}

.lesson-title {
    font-size: 24px;
    font-weight: bold;
}

.lesson-mark {
    border-radius: 50%;
    width: 35px;
    height: 36px;
    padding: 6px;

    &:hover {
        background-color: $background-color_1;
    }
}

.completed-status-icon {
    font-size: 24px;
    cursor: pointer;
    color: $color_1;
}

.completed-status-icon.completed {
    color: $color_2;
}

.lesson-item-section {
    &:hover {
        .completed-status-icon.completed {
            color: $color_3;
        }
    }
}

.lesson-item-section.active {
    .completed-status-icon.completed {
        color: $color_3;
    }
}

.lesson-media-container {
    margin-top: 10px;
    border: 1px solid rgb(228, 228, 228);
    display: block;
    cursor: pointer;
    border-radius: 15px;
    min-height: 280px;

    iframe {
        width: 100%;
        height: 100%;
        border-radius: 15px;
        display: block;
    }

    video {
        width: 100%;
        height: 100%;
        border-radius: 15px;
        display: block;
    }
}

.empty-lesson-box {
    height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.lesson-body {
    padding: 0px 10px;
}

.lesson-content {
    white-space: pre-wrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.community-external-link {
    &:hover {
        text-decoration: underline;
    }
}

.ellipsis-content {
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: pre-wrap;
}

.full-content {
    white-space: pre-wrap;
}

.resource-label {
    max-width: calc(100% - 40px);
}

.classroom-edit-content {
    top: 27px;
}

.set-edit-content {
    top: 27px;
}

.lesson-edit-content {
    top: 27px;
}

.resource-action-content {
    top: 27px;
}

.border-radius-6px {
    border-radius: 6px;
}

.lesson-footer {
    padding: 10px;
}

@media only screen and (max-width: 600px) {
    .lesson-media-container {
        margin-top: 5px;
        min-height: 140px;
    }

    .lesson-title {
        font-size: 18px;
    }
}
</style>
