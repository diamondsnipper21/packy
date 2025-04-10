import { createStore } from 'vuex'
import { communitycenter } from "./modules/communitycenter";
import { community } from "./modules/community";
import { modal } from "./modules/modal";
import { member } from "./modules/member";
import { post } from "./modules/post";
import { classroom } from "./modules/classroom";
import { auth } from "./modules/auth";
import { superadmin } from "./modules/superadmin";

const store = createStore({
    modules: {
        communitycenter,
        community,
        modal,
        member,
        post,
        classroom,
        auth,
        superadmin
    }
})

export default store;