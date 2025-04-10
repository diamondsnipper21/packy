<template>
	<div class="inner-modal-container" @click="outclick">
        <div class="event-media">
            <img :src="getEventMediaSrc(communityEvent)" class="event-media-img" />
            <div v-if="!accessEvent" class="cc-lock-section">
                <font-awesome-icon icon="fa fa-lock" class="access-locked-level-icon" />
                <div class="access-locked-level-desc">
                    {{ $t('community.calendar.cannot-access-event') }}
                </div>
            </div>
        </div>

        <div class="event-title">
            {{ communityEvent.title }}
        </div>

        <div class="flex align-items-center">
            <font-awesome-icon icon="fa fa-calendar" class="mr-05 font-weight-600" />
            <div class="event-date">
                {{ getEventDate(communityEvent.start_at) }} - {{ getEventEndDate(communityEvent) }}
            </div>
        </div>

        <div class="flex align-items-center mb1">
            <font-awesome-icon icon="fa fa-location" class="mr-05 font-weight-600" />
            <div class="event-timezone">
                {{ communityEvent.timezone }}
            </div>
        </div>

        <div v-if="accessEvent" class="event-link pointer" @click="clickEventLink">
            <font-awesome-icon icon="fa fa-link" class="mr-05 font-weight-600" />
            {{ communityEvent.link }}
        </div>
        <div v-else class="event-link">
            <font-awesome-icon icon="fa fa-link" class="mr-05 font-weight-600" />
            {{ $t('community.calendar.event-link-locked') }}
        </div>

        <div class="event-description">
            {{ communityEvent.description }}
        </div>

        <div class="dropdown w100 is-top-left" id="openCalendarDropdown">
            <div class="dropdown-trigger"
                 @click="showCalendarDropdown"
                 v-click-outside="hideCalendarDropdown"
            >
                <button
                    class="button is-medium community-blue-btn w100"
                    :class="btnClass"
                    :disabled="!accessEvent || loading"
                    id="openCalendarLink"
                >
                    <font-awesome-icon icon="fa fa-calendar" class="mr-05 font-weight-600" />
                    {{ $t('community.community.event.add-to-calendar') }}
                </button>
            </div>
            <div
                id="calendar_dropdown_menu"
                class="dropdown-menu"
                :class="displayCalendarDropdown ? 'show' : ''"
            >
                <div class="dropdown-content">
                    <div
                        class="font-weight-600 w100"
                        @click="$event.stopPropagation()"
                    >
                        <div
                            v-for="eventLink in eventLinks"
                            :key="eventLink"
                            @click="openEventLink(eventLink)"
                            class="more-dropdown-item"
                        >
                            {{ translateEventKey(eventLink) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
	</div>
</template>

<script>

import moment from 'moment';
import isManager from "../../mixins/util";

export default {
	name: 'ViewCommunityEvent',
    mixins: [
        isManager
    ],
    data () {
        return {
            loading: false,
            displayCalendarDropdown: false,
        }
    },
    computed: {
        /**
         * Return auth status
         */
        auth ()
        {
            return this.$store.state.auth.loggedIn;
        },

        /**
         * Returns member
         */
        member ()
        {
            return this.$store.state.member.data;
        },

        /**
         * Returns member existence
         */
        memberExist ()
        {
            return (typeof this.member.id !== 'undefined' && parseInt(this.member.id) > 0) ? true : false;
        },

        /**
         * Returns role of member
         */
        role() {
            return this.memberExist ? this.member.role : null;
        },

        /**
         * Returns leaderboard level
         */
        level() {
            let level = 0;
            if (this.memberExist) {
                level = this.member.level;
            }

            return level;
        },

		/**
         * Return community event
         */
        communityEvent ()
        {
            return this.$store.state.communitycenter.communityEvent;
        },

        /**
         * Return community event links
         */
        links ()
        {
            return this.communityEvent.links;
        },

        /**
         * Return community event links
         */
        eventLinks ()
        {
            return this.$store.state.communitycenter.eventLinks;
        },

        /**
         * Return event accessability
         */
        accessEvent() {
            let accessEvent = false;
            if (this.isManager(this.role)) {
                accessEvent = true;
            } else {
                if (this.communityEvent.access_type === 'all') {
                    accessEvent = true;
                } else if (this.communityEvent.access_type === 'only_member') {
                    let values = (this.communityEvent.access_value || '').split(",");
                    let memberGroups = (this.member.groups || []).map(item => `group_${item.id}`)
                    memberGroups = [...memberGroups, this.member.id.toString()]

                    for (const elem of values) {
                        const value = elem.toString();
                        if (memberGroups.includes(value)) {
                            accessEvent = true;
                            break;
                        }
                    }
                }

                if (accessEvent && parseInt(this.communityEvent.level) > parseInt(this.member.level)) {
                    accessEvent = false;
                }
            }

            return accessEvent;
        },

        btnClass() {
            return this.loading ? 'is-loading' : '';
        },
	},
	methods: {
        /**
         * Return community event media, if it exists
         */
        getEventMediaSrc (event)
        {
            return event.media
                ? event.media
                : '/assets/img/right-tools.png';
        },

        getEventDate (date) {
            let eventDate = '';
            if (date) {
                eventDate = moment(date).format('LLL');
            }

            return eventDate;
        },

        getEventEndDate (event) {
            let eventEndDate = '';
            let endAt = event.end_at;
            if (endAt && endAt.startsWith('on_')) {
                eventEndDate = moment(endAt.replace('on_', '')).format('ddd, MMM DD @ h:mm a');
            } else if (event.duration !== '') {
                let durationMin = parseFloat(event.duration) * 60;
                eventEndDate = moment(event.start_at).add(durationMin, 'minutes').format('ddd, MMM DD @ h:mm a');
            }

            return eventEndDate;
        },

        clickEventLink () {
            window.open(this.communityEvent.link, '_blank');
        },

        translateEventKey (event)
        {
            return this.$t('community.community.event.' + event);
        },

        openEventLink (event) {
            if (this.auth) {
              if (typeof this.links[event] !== 'undefined') {
                this.loading = true;
                setTimeout(() => {
                  if (event === 'apple') {
                    var link = document.createElement('a');
                    link.setAttribute('href', this.links[event]);
                    link.setAttribute('download', this.communityEvent.title + '.ics');
                    link.click();
                  } else {
                    window.open(this.links[event], '_blank');
                  }

                  this.loading = false;
                }, 1000);
              }
            } else {
                this.showLogin();
            }
        },

        showCalendarDropdown() {
            if (this.accessEvent) {
                this.displayCalendarDropdown = !this.displayCalendarDropdown;
            }
        },

        hideCalendarDropdown() {
            this.displayCalendarDropdown = false;
        },

        outclick () {
            if (document.getElementById("openCalendarLink") !== null && document.getElementById("openCalendarDropdown") !== null && document.getElementById("openCalendarDropdown").classList.value.includes('is-active')) {
                document.getElementById("openCalendarLink").click();
            }
        },

        /**
         * Show login modal
         */
        showLogin ()
        {
            this.$store.commit('resetUser');
            this.$store.commit('showModal', {
                type: 'Login',
                transparent: true
            });
        },
	}
}
</script>

<style scoped>
    .event-title {
        font-size: 24px;
        font-weight: bold;
        text-align: left;
        margin-top: 15px;
        margin-bottom: 15px;
    }

    .event-date {
        text-align: left;
    }

    .event-timezone {
        color: rgb(144, 144, 144);
        text-align: left;
    }

    .event-link {
        color: rgb(46, 110, 245);
        margin-bottom: 15px;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .event-link:hover {
        text-decoration: underline;
    }

    .event-description {
        text-align: left;
        margin-bottom: 30px;
    }

    .inner-modal-container {
        padding-top: 2.5em !important;
        padding-bottom: 2.5em !important;
    }

    .event-media {
        width: 100%;
        height: 270px;
        position: relative;
    }

    .event-media-img {
        width: 100%;
        height: 100%;
        border-radius: 6px 6px 0px 0px;
        object-fit: cover;
    }

    .more-dropdown-item {
        text-align: left;
        white-space: nowrap;
        width: 100%;
        line-height: 1.5;
        padding: 0.375rem 1rem;
        color: #4a4a4a;
        cursor: pointer;
    }

    .more-dropdown-item:hover {
        background-color: whitesmoke;
        color: #0a0a0a;
    }

    @media only screen and (max-width: 600px)
    {
        .event-title {
            font-size: 16px;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .event-date {
            font-size: 14px;
        }

        .event-timezone {
            font-size: 14px;
        }

        .event-link {
            font-size: 14px;
        }

        .event-description {
            margin-bottom: 15px;
            font-size: 14px;
            max-height: 150px;
            overflow: auto;
        }

        .inner-modal-container {
            padding-top: 0.5em !important;
            padding-bottom: 0.5em !important;
        }

        .event-media {
            height: 170px;
        }

        .more-dropdown-item {
            padding: 0.375rem 0.5rem;
        }
    }
</style>
