<template>
    <div class="inner-modal-container">
        <p class="points-modal-title">
            {{ $t('community.leaderboard.points-modal.title') }}
        </p>
        <div class="sub-area">
            <p class="points-modal-sub-title">
                {{ $t('community.leaderboard.points-modal.points') }}
            </p>
            <p class="points-modal-sub-desc">
                {{ $t('community.leaderboard.points-modal.points-desc') }}
            </p>
        </div>
        <div class="sub-area">
            <p class="points-modal-sub-title">
                {{ $t('community.leaderboard.points-modal.levels') }}
            </p>
            <p class="points-modal-sub-desc">
                {{ $t('community.leaderboard.points-modal.levels-desc') }}
            </p>
        </div>
        <div class="sub-area flex">
            <div class="w100">
                <div
                    v-for="leftPoint in leftPoints"
                    :key="leftPoint.key"
                    class="leaderboard-level-item"
                >
                    {{ $t('community.leaderboard.level') }} {{ leftPoint.key }} - {{ leftPoint.val }} {{ $t('community.leaderboard.points-modal.points') }}
                </div>
            </div>
            <div class="w100">
                <div
                    v-for="rightPoint in rightPoints"
                    :key="rightPoint.key"
                    class="leaderboard-level-item"
                >
                    {{ $t('community.leaderboard.level') }} {{ rightPoint.key }} - {{ rightPoint.val }} {{ $t('community.leaderboard.points-modal.points') }}
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'PointsInfo',
    data () {
        return {
            leftPoints: [],
            rightPoints: []
        };
    },
    mounted() {
        this.leftPoints = [];
        this.rightPoints = [];

        let divideCnt = Math.round(this.neededPointsCnt / 2);

        for (let step = 1; step <= divideCnt; step ++) {
            if (typeof this.neededPoints['level_' + step] !== 'undefined') {
                this.leftPoints.push({
                    key: step,
                    val: this.neededPoints['level_' + step]
                })
            }
        }

        for (let step = divideCnt + 1; step <= this.neededPointsCnt; step ++) {
            if (typeof this.neededPoints['level_' + step] !== 'undefined') {
                this.rightPoints.push({
                    key: step,
                    val: this.neededPoints['level_' + step]
                })
            }
        }
    },
    computed: {
        /**
         * Returns leaderboard
         */
        leaderboard ()
        {
            return this.$store.state.communitycenter.leaderboard;
        },

        /**
         * Returns needed points for level up
         */
        neededPoints ()
        {
            return this.leaderboard.neededPoints;
        },

        /**
         * Returns left points
         */
        neededPointsCnt ()
        {
            var count = 0;
            for(var prop in this.neededPoints) {
                if(this.neededPoints.hasOwnProperty(prop))
                    ++ count;
            }

            return count;
        },
    },
}
</script>

<style scoped>
    .points-modal-title {
        font-size: 23px;
        font-weight: bold;
        padding-bottom: 10px;
        text-align: left;
    }
    .sub-area {
        padding: 10px 0px;
        text-align: left;
    }
    .points-modal-sub-title {
        font-size: 16px;
        font-weight: bold;
        text-align: left;
    }
    .leaderboard-level-item {
        text-align: left;
    }
</style>