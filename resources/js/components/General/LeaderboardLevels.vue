<template>
  <div class="leaderboard-points-section">
    <div
        v-for="level in levels"
        :key="level.id"
        class="leaderboard-level-item"
        :class="userLevel === level.level_number ? 'level-active' : userLevel >= level.level_number ? 'level-completed' : ''"
    >
      <div class="leaderboard-level-item-icon">
        <div v-if="userLevel >= level.level_number" style="margin-top: -2px">
          <font-awesome-icon icon="fa fa-lock-open" class="locked-level-icon" />
        </div>
        <div v-else style="margin-top: -2px">
          <font-awesome-icon icon="fa fa-lock" class="locked-level-icon" :beat="true"/>
        </div>
      </div>
      <div class="leaderboard-level-item-summary">
        <div class="leaderboard-level-item-title">
          {{ level.name }}
        </div>

        <div v-if="level.classrooms.length > 1" class="leaderboard-level-item-desc">
          {{ $t('community.leaderboard.unlock-multi-classrooms').replace("#classroom_counts#", level.classrooms.length) }}
        </div>
        <div v-else-if="level.classrooms.length === 1" class="leaderboard-level-item-desc">
          {{ $t('community.leaderboard.unlock') }} <span class="unlock-classroom-link" @click="goToClassroomDetail(level.classrooms[0])">"{{ level.classrooms[0].title }}"</span>
        </div>

        <div class="leaderboard-level-item-desc">
          {{ $t('community.leaderboard.percent-of-members').replace("#percent#", level.member_percent) }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>

import { notify } from "@kyvg/vue3-notification";
import { MemberAccess } from '../../data/enums';
import isManager from "../../mixins/util";

export default {
  name: 'LeaderboardLevels',
  props: ['userLevel', 'levels'],
  mixins: [
    isManager
  ],
  data() {
    return {
      MemberAccess
    };
  },

  computed: {
    /**
    * Returns community
    */
    community() {
      return this.$store.state.community.data;
    },

    /**
    * Returns community privacy
    */
    privacy ()
    {
      return this.community.privacy;
    },

    /**
    * Returns member
    */
    member() {
      return this.$store.state.member.data;
    },

    /**
    * Returns member existence
    */
    memberExist() {
      return (typeof this.member.id !== 'undefined' && parseInt(this.member.id) > 0) ? true : false;
    },

    /**
    * Returns access of member
    */
    access() {
      return this.memberExist ? this.member.access : null;
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

    /**
    * Go to classroom detail
    */
    goToClassroomDetail(item) {
      if (this.accessClassroom(item)) {
        let tab = 'ressources';
        if (this.privacy === 'private' && this.access !== MemberAccess.ALLOWED) {
          tab = 'about';
        }

        this.$router.push('/' + this.community.url + '/ressources/' + item.id).catch(()=>{});
        this.$store.commit('setCommunityTab', tab);
      } else {
        if (this.accessLevel(item) > 0) {
          this.$store.commit('setModalSize', 'small');
          this.$store.commit('showModal', {
            type: 'LevelAccessAlert',
            extraData: item,
            transparent: true
          });
        } else {
          notify({
            text: this.$t('community.classroom.cannot-access-classroom'),
            type: 'error'
          });
        }
      }
    }
  }
}
</script>

<style scoped>
.leaderboard-level-item {
  margin-bottom: 20px;
  display: flex;
  align-items: center;
}
.leaderboard-level-item:last-child {
  margin-bottom: 0px;
}
.leaderboard-level-item-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  background-color: white;
  border: 2px solid #E4E4E4FF;
}
.leaderboard-level-item {
  opacity: 0.5;
}
.leaderboard-level-item.level-active,
.leaderboard-level-item.level-completed {
  opacity: 1;
}
.leaderboard-level-item.level-active .leaderboard-level-item-icon,
.leaderboard-level-item.level-completed .leaderboard-level-item-icon {
  background-color: #9198FF;
  color: #fff;
  border: none;
}
.leaderboard-level-item-title {
  font-size: 15px;
  font-weight: 700;
}
.leaderboard-level-item-desc {
  font-size: 12px;
  color: rgb(144, 144, 144);
}
.locked-level-icon {
  font-size: 16px;
}
.leaderboard-level-item-summary {
  margin-left: 10px;
  width: calc(100% - 50px);
}

.unlock-classroom-link {
  color: rgb(46, 110, 245);
  font-size: 13px;
  cursor: pointer;
}

.unlock-classroom-link:hover {
  text-decoration: underline;
}
</style>