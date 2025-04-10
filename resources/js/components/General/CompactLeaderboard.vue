<template>
  <div>
    <div class="leaderboard-title">{{ title }}</div>
    <template v-if="ranking">
      <div class="border-top" style="display: flex; align-items: end; flex-wrap: wrap; padding: 10px 0px 20px 0px;">
        <div class="leaderboard-column" v-if="ranking[1]">
          <div class="avatar avatar-2" :style="'margin: 0 auto; background-image: url(\'' + getAvatar(ranking[1].user) + '\'); background-position: center; background-size: cover;'"></div>
          <div class="rank-number">
            <div><img src="/assets/icons/silver-medal.png" style="width: 45px;"/></div>
          </div>
          <div class="user-name">
            {{ ranking[1].user.name }}<br/>
            <span class="level-label">Level {{ ranking[1].level }}</span>
          </div>
          <div style="text-align: center; margin-top: 5px;"><div class="score-label">{{ getUserPoints(ranking[1]) }}</div></div>
        </div>
        <div class="leaderboard-column" v-if="ranking[0]">
          <div class="avatar avatar-1" :style="'margin: 0 auto; background-image: url(\'' + getAvatar(ranking[0].user) + '\'); background-position: center; background-size: cover;'"></div>
          <div class="rank-number">
            <div><img src="/assets/icons/gold-medal.png" style="width: 45px;"/></div>
          </div>
          <div class="user-name">
            {{ ranking[0].user.name }}<br/>
            <span class="level-label">Level {{ ranking[0].level }}</span>
          </div>
          <div style="text-align: center; margin-top: 5px;"><div class="score-label">{{ getUserPoints(ranking[0]) }}</div></div>
        </div>
        <div class="leaderboard-column" v-if="ranking[2]">
          <div class="avatar avatar-3" :style="'margin: 0 auto; background-image: url(\'' + getAvatar(ranking[2].user) + '\'); background-position: center; background-size: cover;'"></div>
          <div class="rank-number">
            <div><img src="/assets/icons/bronze-medal.png" style="width: 45px;"/></div>
          </div>
          <div class="user-name">
            {{ ranking[2].user.name }}<br/>
            <span class="level-label">Level {{ ranking[2].level }}</span>
          </div>
          <div style="text-align: center; margin-top: 5px;"><div class="score-label">{{ getUserPoints(ranking[2]) }}</div></div>
        </div>
      </div>
      <template v-for="(member, index) in ranking">
        <div v-if="index > 2 && index <= 10" class="border-top" style="display: flex; align-items: center; padding: 10px 0px;">
          <div style="width: 25px; font-size: 14px; font-weight: 700;">{{ index + 1 }}</div>
          <div class="avatar avatar-4" :style="'background-image: url(\'' + getAvatar(member.user) + '\'); background-position: center; background-size: cover;'"></div>
          <div style="padding: 0px 10px; font-size: 14px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; max-width: 200px;">
            <strong>{{ member.user.name }}</strong><br/>
            <span class="level-label">Level {{ member.level }}</span>
          </div>
          <div style="margin-left: auto; width: 50px; text-align: center;">
            <span class="score-label">{{ getUserPoints(member) }}</span>
          </div>
        </div>
      </template>
    </template>

    <template v-else>
      <div>No data</div>
    </template>
  </div>
</template>

<script>
import md5 from 'md5'

export default {
  name: 'CompactLeaderboard',
  props: ['title', 'ranking', 'index'],
  data() {
    return {
      data: []
    };
  },
  methods: {
    getUserPoints(user)
    {
      console.log('getUserPoints user', user)

        if (this.index === 'points') {
        return user.points;
      }
      if (this.index === 'weeklyPoints') {
        return (user.weeklyPoints > 0 ? '+' : '') + user.weeklyPoints;
      }
      if (this.index === 'monthlyPoints') {
        return (user.monthlyPoints > 0 ? '+' : '') + user.monthlyPoints;
      }

      return 0;
    },

    getAvatar(user)
    {
      let avatar = 'https://www.gravatar.com/avatar/?s=48&d=identicon';
      if (user.photo !== null) {
        avatar = user.photo;
      } else if (user.customer && user.customer.email !== null) {
        avatar = 'https://www.gravatar.com/avatar/' + md5(user.customer.email) + '?s=48&d=identicon';
      }

      return avatar;
    }
  }
}
</script>

<style scoped>
.leaderboard-column {
  flex-grow: 1;
  width: 33%;
}
.avatar {
  width: 40px;
  height: 40px;
  border-radius: 100%;
}
.avatar.avatar-1 {
  width: 80px;
  height: 80px;
  outline: 3px solid #ffd700;
  border: 2px solid white;
}
.avatar.avatar-2 {
  width: 60px;
  height: 60px;
  outline: 3px solid #C0C0C0;
  border: 2px solid white;
}
.avatar.avatar-3 {
  width: 60px;
  height: 60px;
  outline: 3px solid #CD7F32;
  border: 2px solid white;
}
.avatar.avatar-4 {
  width: 30px;
  height: 30px;
  min-width: 30px;
}
.user-name {
  margin-top: 5px;
  font-size: 12px;
  text-align: center;
  font-weight: 700;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
}
.rank-number {
  margin-top: 5px;
  font-size: 16px;
  text-align: center;
  font-weight: 700;
  margin-bottom: 5px;
}
.border-top {
  border-top: 1px solid #f6f5f6;
}
.level-label {
  font-size: 10px;
  color: #ccc;
  line-height: 10px;
  display: block;
}
.score-label {
  padding: 2px 10px 2px 10px;
  background: #f6f5f6;
  border-radius: 10px;
  font-size: 13px;
  display: inline-block;
}
.leaderboard-title {
  text-align: center;
  font-size: 17px;
  font-weight: 700;
  padding-bottom: 5px;
}
</style>