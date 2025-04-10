require('./bootstrap');

import { createApp } from 'vue'
import { createI18n } from "vue-i18n";
import mitt from 'mitt';
import CommunityDashboard from './components/CommunityDashboard.vue'
import SuperAdminDashboard from './components/SuperAdmin/SuperAdminDashboard.vue'
import router from "./router.js"
import { langs } from './langs'
// import vClickOutside from 'v-click-outside'
import vClickOutside from "click-outside-vue3"
import Notifications from '@kyvg/vue3-notification'
import store from './store';

/* import the fontawesome core */
import { library } from '@fortawesome/fontawesome-svg-core'

/* import font awesome icon component */
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

/* Vuetify */
/*
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
const vuetify = createVuetify({
  components,
  directives
})
*/

/* import specific icons */
import {
  faVideo,
  faImage,
  faLink,
  faThumbsUp,
  faCheck,
  faComment,
  faCircle,
  faCircleNotch,
  faCalendar,
  faFilter,
  faThumbtack,
  faUsers,
  faTag,
  faChevronUp,
  faChevronDown,
  faChevronRight,
  faChevronLeft,
  faPlus,
  faTimes,
  faBars,
  faLocation,
  faLock,
  faLockOpen,
  faCircleCheck,
  faQuestionCircle,
  faTrash,
  faClock,
  faUpRightAndDownLeftFromCenter,
  faDownLeftAndUpRightToCenter,
  faEllipsis,
  faPencil,
  faAngleDown,
  faAngleUp,
  faCircleExclamation,
  faGlobe,
  faBell,
  faMessage,
  faMagnifyingGlass,
  faArrowsRotate,
  faRepeat,
  faCamera,
  faRightToBracket,
  faCaretRight,
  faCaretUp,
  faMicrophone,
  faFile,
  faFilePdf,
  faPaperclip,
  faSquarePollVertical,
  faGear,
  faCalendarDays,
  faArrowLeft,
  faCopy
} from '@fortawesome/free-solid-svg-icons'
import {
  faCircle as farCircle,
  faGem as farGem
} from '@fortawesome/free-regular-svg-icons'

/* add icons to the library */
library.add(faVideo,
  faImage,
  faLink,
  faThumbsUp,
  faCheck,
  faComment,
  faCircle,
  faCircleNotch,
  faCalendar,
  faFilter,
  faThumbtack,
  faUsers,
  faTag,
  faChevronUp,
  faChevronDown,
  faChevronRight,
  faChevronLeft,
  faPlus,
  faTimes,
  faBars,
  faLocation,
  faLock,
  faLockOpen,
  faCircleCheck,
  faQuestionCircle,
  faTrash,
  faClock,
  faUpRightAndDownLeftFromCenter,
  faDownLeftAndUpRightToCenter,
  faEllipsis,
  faPencil,
  faAngleDown,
  faAngleUp,
  faCircleExclamation,
  faGlobe,
  faBell,
  faMessage,
  faMagnifyingGlass,
  faArrowsRotate,
  faRepeat,
  faCamera,
  faRightToBracket,
  faCaretRight,
  faCaretUp,
  faMicrophone,
  faFile,
  faFilePdf,
  faPaperclip,
  faSquarePollVertical,
  faGear,
  faCalendarDays,
  faArrowLeft,
  farCircle,
  faCopy,
  farGem
)

const i18n = createI18n({
  locale: 'fr',
  fallbackLocale: 'fr',
  messages: langs,
  silentTranslationWarn: true
})

const emitter = mitt();

const app = createApp(CommunityDashboard);
app.config.globalProperties.emitter = emitter;
app.use(store)
app.use(router);
app.use(i18n);
app.use(vClickOutside);
app.use(Notifications)
// app.use(vuetify);
app.component('font-awesome-icon', FontAwesomeIcon)

const appSuperAdmin = createApp(SuperAdminDashboard);
appSuperAdmin.config.globalProperties.emitter = emitter;
appSuperAdmin.use(store)
appSuperAdmin.use(router);
appSuperAdmin.use(i18n);
appSuperAdmin.use(vClickOutside);
appSuperAdmin.use(Notifications)
// appSuperAdmin.use(vuetify);
appSuperAdmin.component('font-awesome-icon', FontAwesomeIcon)

router.isReady().then(() => {
  app.mount("#app");

  if (document.getElementById("app-super-admin")) {
    appSuperAdmin.mount("#app-super-admin");
  }
})