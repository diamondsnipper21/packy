<template>
    <div class="b-skeleton" v-if="active" :class="`${animated && 'is-animated'} ${size || ''} ${position || ''}`">
        <div v-for="item in items" class="b-skeleton-item"
            :class="`${rounded && 'is-rounded'} ${size || ''} ${position || ''}`" :key="item" :style="{
                height: height === undefined ? null
                    : (isNaN(height) ? height : height + 'px'),
                width: width === undefined ? null
                    : (isNaN(width) ? width : width + 'px'),
                borderRadius: circle ? '50%' : null
            }">
        </div>
    </div>
</template>

<script>
export default {
    name: 'Skeleton',
    props: {
        active: {
            type: Boolean,
            default: true
        },
        animated: {
            type: Boolean,
            default: true
        },
        width: [Number, String],
        height: [Number, String],
        circle: Boolean,
        rounded: {
            type: Boolean,
            default: true
        },
        count: {
            type: Number,
            default: 1
        },
        position: {
            type: String,
            default: '',
            validator(value) {
                return [
                    '',
                    'is-centered',
                    'is-right'
                ].indexOf(value) > -1
            }
        },
        size: String
    },
    computed: {
        items() {
            return Array(this.count)
        }
    }
}
</script>
<style lang="scss">
$grey-lighter: #dbdbdb;
$radius: 4px;

$size-small: 1em;
$size-normal: 2em;
$size-medium: 3em;
$size-large: 4em;

$skeleton-color: $grey-lighter !default;
$skeleton-background: linear-gradient(90deg, $skeleton-color 25%, rgba($skeleton-color, 0.5) 50%, $skeleton-color 75%) !default;
$skeleton-border-radius: $radius !default;
$skeleton-duration: 1.5s !default;
$skeleton-margin-top: .5rem !default;

@mixin steps-size($size) {
    >.b-skeleton-item {
        line-height: $size;
    }
}

.b-skeleton {
    display: inline-flex;
    flex-direction: column;
    vertical-align: middle;
    width: 100%;

    >.b-skeleton-item {
        background: $skeleton-background;
        background-size: 400% 100%;
        width: 100%;

        &.is-rounded {
            border-radius: $skeleton-border-radius;
        }

        &::after {
            content: "\00a0";
        }

        +.b-skeleton-item {
            margin-top: $skeleton-margin-top;
        }
    }

    &.is-animated {
        >.b-skeleton-item {
            animation: skeleton-loading $skeleton-duration infinite;
        }
    }

    &.is-centered {
        align-items: center;
    }

    &.is-right {
        align-items: flex-end;
    }

    +.b-skeleton {
        margin-top: $skeleton-margin-top;
    }

    @include steps-size($size-normal);

    &.is-small {
        @include steps-size($size-small);
    }

    &.is-medium {
        @include steps-size($size-medium);
    }

    &.is-large {
        @include steps-size($size-large);
    }
}

@keyframes skeleton-loading {
    0% {
        background-position: 100% 50%;
    }

    100% {
        background-position: 0 50%;
    }
}
</style>