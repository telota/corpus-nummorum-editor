<template>
<div
    :class="color"
    :style="styling"
>
    <div v-if="header" style="height: 80px">
        <v-hover v-slot="{ hover }">
            <div
                class="d-flex align-center white--text light-blue"
                :class="hover ? 'darken-3' : 'darken-4'"
                style="cursor: pointer; position: relative; white-space: nowrap;"
                :style="'height: 40px'"
                @click="$emit('search')"
            >
                <div style="position: absolute">
                    <adv-btn
                        medium
                        icon="search"
                        color-hover="transparent"
                        color-text="white"
                    />
                </div>
                <div
                    class="font-weight-bold text-uppercase"
                    :style="'padding-left: 50px;'"
                    v-text="'search'"
                />
            </div>
        </v-hover>

        <v-hover v-slot="{ hover }">
            <div
                class="d-flex align-center white--text caption grey"
                :class="hover ? 'darken-1' : 'darken-2'"
                style="cursor: pointer; position: relative; white-space: nowrap;"
                :style="'height: 40px'"
                @click="$emit('clear')"
            >
                <div style="position: absolute">
                    <adv-btn
                        medium icon="clear"
                        color-hover="transparent"
                        color-text="white"
                    />
                </div>
                <div
                    v-text="'Clear Query Parameters'"
                    :style="'padding-left: 50px;'"
                />
            </div>
        </v-hover>
    </div>

    <div
        v-else-if="costumHeader"
        :style="'height:' + costumHeader + 'px'"
    >
        <slot name="header" />
    </div>

    <div :style="stylingContent">
        <slot />
    </div>
</div>
</template>

<script>
export default {
    data () {
        return {
        }
    },

    props: {
        top: { type: Number, default: 90 },
        header: { type: Boolean, default: false },
        dialog: { type: Boolean, default: false },
        costumHeader: { type: Number, default: 0 },
        zIndex: { type: Number, default: 98 },
        color: { type: String, default: 'drawer_bg' },
        collapse:  { type: [Number, String], default: null },
        expanding: { type: [Number, String], default: null },
        noAutoExpansion: { type: Boolean, default: false }
    },

    computed: {
        styling () {
            return [
                'height: calc(100vh - ' + (this.dialog ? 140 : 90) + 'px)',
                'overflow-y: hidden',
                'overflow-x: hidden',
                'z-index:' + this.zIndex,
                'width: 100%',
            ].join(';\n')
        },

        stylingContent () {
            return [
                'height: calc(100vh - ' + ((this.dialog ? 170 : this.top) + (this.header ? 80 : this.costumHeader)) + 'px)',
                'width: 100%',
                'overflow-y: auto',
                'overflow-x: hidden'
            ].join(';\n')
        }
    },

    watch: {
    },

    created () {
    },

    methods: {
    }
}
</script>
