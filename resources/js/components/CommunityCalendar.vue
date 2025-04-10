<template>
    <div class="container pl-0 pr-0">

        <loading v-if="loading" :active.sync="loading" :is-full-page="true" />

        <div v-else>
            <div v-if="typeof community.name === 'undefined' || ![CommunityStatus.PUBLISHED, CommunityStatus.PENDING].includes(community.status)"
                class="empty-section">
                {{ $t('community.community.empty-community-placeholder') }}
            </div>
            <div v-else>
                <div v-if="calendarViewMode === 'list_view'" class="columns">
                    <div class="column is-two-thirds left-column-for-desktop">
    
                        <div class="calendar-list-view-header">
                            <div class="calendar-link-section">
                                <div class="calendar-link" @click="decreaseMonth">
                                    <font-awesome-icon icon="fa fa-chevron-left"/>
                                </div>
                                <div class="calendar-link" @click="increaseMonth">
                                    <font-awesome-icon icon="fa fa-chevron-right"/>
                                </div>
                                <div class="ml-05 calendar-month-info">
                                    {{ monthInfo }}
                                </div>
                            </div>
                            <div class="calendar-link calendar-date-link" @click="viewSwitcher">
                                <font-awesome-icon icon="fa fa-calendar"/>
                            </div>
                        </div>
    
                        <div class="mt1">
                            <!-- No events Exist -->
                            <div v-if="this.events.length === 0" class="empty-section box">
                                {{ $t('community.community.empty-event-placeholder') }}
                            </div>
    
                            <div v-else>
                                <div
                                    v-for="event, i in events"
                                    :key="event.id"
                                    class="box calendar-event-item"
                                    @click="clickEventLink(event)"
                                >
                                    <div class="calendar-event-item-img-container">
                                        <img :src="getEventImgSrc(event)" class="calendar-event-item-img" />
                                    </div>
    
                                    <div class="calendar-event-item-content">
    
                                        <div class="calendar-event-item-time">
                                            {{ getEventDate(event.start_at) }} - {{ getEventEndDate(event) }}
                                        </div>
                                        <div class="calendar-event-item-title">
                                            {{ event.title }}
                                        </div>
                                        <div class="calendar-event-item-link">
                                            <font-awesome-icon icon="fa fa-link"/>
                                            {{ $t('common.link') }}
                                        </div>
                                    </div>
                                </div>
                                <Pagination :total="total" :perPage="perPage" :current="currentPage"
                                    pageAction="GET_MONTHLY_EVENTS" class="mb1"/>
                            </div>
                        </div>
                    </div>
    
                    <div class="column is-one-third right-column-for-desktop">
                        <CommunitySummarySidebarTop />
                    </div>
                </div>
    
                <div v-else-if="calendarViewMode === 'date_view'" class="calendar-container">
                    <div class="calendar-link calendar-date-link calendar-date-view-action" @click="viewSwitcher">
                        <font-awesome-icon icon="fa fa-bars"/>
                    </div>
                    <full-calendar :options="calendarOptions" />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import moment from 'moment'
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/css/index.css'
import isManager from "../mixins/util";

import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import enLocale from "@fullcalendar/core/locales/en-gb";
import frLocale from "@fullcalendar/core/locales/fr";

import CommunitySummarySidebarTop from "./CommunitySummarySidebarTop.vue";
import Pagination from "../components/General/Elements/Pagination.vue";
import { CommunityStatus } from '../data/enums';
import SubscriptionOverdueBar from "./General/SubscriptionOverdueBar.vue";
import InactiveBar from "./General/InactiveBar.vue";

export default {
    name: 'CommunityCalendar',
    mixins: [
        isManager
    ],
    components: {
        InactiveBar, SubscriptionOverdueBar,
        FullCalendar,
        CommunitySummarySidebarTop,
        Loading,
        Pagination
    },
    data () {
        return {
            CommunityStatus,
            calendarOptions: {
                plugins: [
                    dayGridPlugin,
                    timeGridPlugin,
                    interactionPlugin
                ],
                nowIndicator: true,
                headerToolbar: {
                    left: 'today',
                    center: 'prev title next',
                    right: ''
                },
                navLinks: false,
                dayMaxEvents: true, // allow "more" link when too many events
                initialView: 'dayGridMonth',
                events: [],
                editable: true,
                selectable: true,
                weekends: true,
                select: this.handleDateSelect,
                eventClick: this.handleEventClick
            },
            loading: false
        }
    },
    async created ()
    {
        this.loading = true;
        await this.$store.dispatch('GET_COMMUNITY_EVENTS');
        if (this.communityEvents.length > 0) {
            this.communityEvents.map(event => {
                this.calendarOptions.events.push({
                    id: event.id,
                    title: event.title,
                    start: event.start_at,
                    end: event.end_at
                });
            })
        }

        if (localStorage.getItem('communityCalendarViewMode') !== null) {
            this.$store.commit('setCalendarViewMode', localStorage.getItem('communityCalendarViewMode'));
        }

        await this.$store.dispatch('GET_MONTHLY_EVENTS');

        this.changeLangOfCalendar();

        this.loading = false;
    },
    mounted() {
        window.addEventListener('lang-localstorage-changed', (event) => {
            this.changeLangOfCalendar();
        });
    },
    computed: {
        /**
         * Returns role of member
         */
        role() {
            return this.memberExist ? this.currentMember.role : null;
        },

        /**
         * Returns member
         */
        member() {
            return this.$store.state.member.data;
        },

        /**
         * Returns community
         */
        community ()
        {
            return this.$store.state.community.data;
        },
        
        /**
         * Returns community events
         */
        communityEvents ()
        {
            return this.$store.state.communitycenter.communityEvents;
        },

        /**
         * Returns community events
         */
        monthlyEvents ()
        {
            return this.$store.state.communitycenter.monthlyEvents;
        },

        /**
         * Returns community posts
         */
        events ()
        {
            return (this.monthlyEvents.hasOwnProperty('data')) ? this.monthlyEvents.data : [];
        },

        /**
         * Returns calendar view mode
         */
        calendarViewMode ()
        {
            return this.$store.state.communitycenter.calendarViewMode;
        },

        /**
         * Returns eventMonth
         */
        eventMonth ()
        {
            return this.$store.state.communitycenter.eventMonth;
        },

        /**
         * Returns month info
         */
        monthInfo ()
        {
            let monthInfo = '';
            if (this.eventMonth > 0) {
                monthInfo = moment().add(this.eventMonth, 'months').locale(this.$i18n.locale).format('MMMM YYYY');
            } else if (this.eventMonth < 0) {
                monthInfo = moment().subtract(Math.abs(this.eventMonth), 'months').locale(this.$i18n.locale).format('MMMM YYYY');
            } else {
                monthInfo = moment().locale(this.$i18n.locale).format('MMMM YYYY');
            }

            return monthInfo;
        },
        
        /**
         * Returns total of community events
         */
         total() {
            return this.$store.state.communitycenter.monthlyEvents?.total || 0;
        },

        /**
         * Returns current page of community events
         */
         currentPage() {
            return this.$store.state.communitycenter.monthlyEvents?.current_page || 0;
        },

        perPage() {
            return this.$store.state.communitycenter.monthlyEvents?.per_page || 1;
        },
    },
    methods: {
        handleDateSelect(e) {

        },

        changeLangOfCalendar() {
            if (this.$i18n && this.$i18n.locale === 'fr') {
                this.calendarOptions.locale = frLocale;
            } else {
                this.calendarOptions.locale = enLocale;
            }
        },

        /**
         * Switch view way
         */
        viewSwitcher()
        {
            let view = '';
            if (this.calendarViewMode === 'date_view') {
                view = 'list_view';
            } else {
                view = 'date_view';
            }

            this.$store.commit('setCalendarViewMode', view);
            localStorage.setItem('communityCalendarViewMode', view);
        },

        async handleEventClick(e) {
            await this.$store.dispatch('GET_COMMUNITY_EVENT', e.event.id);
        },

        /**
         * Return community event media, if it exists
         */
        getEventImgSrc (event)
        {
            return event.media
                ? event.media
                : '/assets/img/right-tools.png';
        },

        getEventDate (date) {
            let eventDate = '';
            if (date) {
                eventDate = moment(date).locale(this.$i18n.locale).format('ddd, MMM DD @ h:mm a');
            }

            return eventDate;
        },

        getEventEndDate (event) {
            let eventEndDate = '';
            let endAt = event.end_at;
            if (endAt && endAt.startsWith('on_')) {
                eventEndDate = moment(endAt.replace('on_', '')).locale(this.$i18n.locale).format('ddd, MMM DD @ h:mm a');
            } else if (event.duration !== '') {
                let durationMin = parseFloat(event.duration) * 60;
                eventEndDate = moment(event.start_at).add(durationMin, 'minutes').locale(this.$i18n.locale).format('ddd, MMM DD @ h:mm a');
            }

            return eventEndDate;
        },

        async clickEventLink (event) {
            await this.$store.dispatch('GET_COMMUNITY_EVENT', event.id);
        },

        /**
         * Decrease month
         */
        async decreaseMonth()
        {
            this.$store.commit('decreaseMonth');
            await this.$store.dispatch('GET_MONTHLY_EVENTS');
        },

        /**
         * Increase month
         */
        async increaseMonth()
        {
            this.$store.commit('increaseMonth');
            await this.$store.dispatch('GET_MONTHLY_EVENTS');
        }
    }
}
</script>

<style scoped>
    .calendar-container {
        padding: 15px;
        background-color: #fff;
        padding-top: 25px;
        border: 1px solid rgb(228, 228, 228);
        border-radius: 10px;
        position: relative;
    }

    .calendar-list-view-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .calendar-date-view-action {
        position: absolute;
        top: 25px;
        right: 15px;
    }

    .calendar-date-link {
        border: 1px solid rgb(228, 228, 228);
        background-color: #fff;
    }

    .calendar-link {
        height: 40px;
        width: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border-radius: 50%;
    }

    .calendar-link i {
        opacity: 0.65;
    }

    .calendar-link:hover {
        background-color: rgb(228, 228, 228) !important;
        border: 1px solid rgb(228, 228, 228) !important;
    }

    .calendar-link:hover i {
        opacity: 1;
    }

    .calendar-link-section {
        display: flex;
        align-items: center;
    }

    .calendar-event-item {
        display: flex;
        align-items: center;
        cursor: pointer;
    }

    .calendar-event-item:hover {
        box-shadow: rgba(60, 64, 67, 0.32) 0px 1px 2px, rgba(60, 64, 67, 0.15) 0px 2px 6px, rgba(0, 0, 0, 0.1) 0px 1px 8px;
    }

    .calendar-event-item-img-container {
        position: relative;
        width: 160px;
        padding-top: 160px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        border: 1px solid rgb(228, 228, 228);
    }

    .calendar-event-item-img {
        border-radius: 10px;
        object-fit: cover;
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        pointer-events: none;
    }

    .calendar-event-item-content {
        padding: 0px 15px;
        width: calc(100% - 160px);
    }

    .calendar-event-item-title {
        font-weight: bold;
        font-style: normal;
        font-size: 20px;
        line-height: 1.5;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding: 10px 0px;
    }

    .calendar-event-item-link {
        color: rgb(144, 144, 144);
        cursor: pointer;
    }

    .calendar-event-item-link i {
        font-size: 14px;
    }

    .calendar-month-info {
        font-size: 18px;
        font-weight: 500;
    }

    .calendar-month-info:first-letter {
        text-transform: uppercase;
    }

    @media only screen and (max-width: 600px)
    {
        .calendar-container {
            padding: 5px;
            padding-top: 15px;
        }

        .calendar-event-item-img-container {
            width: 90px;
            padding-top: 90px;
            border-radius: 5px;
        }

        .calendar-event-item-content {
            padding: 0px 10px;
            width: calc(100% - 90px);
        }

        .calendar-event-item-title {
            font-size: 16px;
            padding: 5px 0px;
        }

        .calendar-event-item-link {
            font-size: 13px;
        }

        .calendar-event-item-link i {
            font-size: 13px;
        }

        .calendar-month-info {
            font-size: 16px;
        }

        .calendar-date-view-action {
            top: 15px;
        }
    }
</style>
