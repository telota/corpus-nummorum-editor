<template>
<div>
    <!-- Toggle -->
    <div
        :class="toggle.class"
        :style="toggle.style"
        @click="onDrawerExpand(false)"
    >
        <v-icon dark v-text="'arrow_back_ios'" />
    </div>

    <!-- Drawer -->
    <div
        :class="color"
        :style="styling"
    >
        <div v-if="header" style="height: 80px">
            <v-hover v-slot="{ hover }">
                <div
                    class="d-flex align-center white--text light-blue"
                    :class="hover ? 'darken-3' : 'darken-4'"
                    style="cursor: pointer; position: relative"
                    :style="'height: 40px'"
                    @click="$emit('search')"
                >
                    <div style="position: absolute">
                        <adv-btn medium icon="search" color-hover="transparent" color-text="white" />
                    </div>
                    <div class="font-weight-bold text-center" v-text="'SEARCH'" :style="'width: 100%; padding: 0 50px 0 50px;'" />
                </div>
            </v-hover>

            <v-hover v-slot="{ hover }">
                <div
                    class="d-flex align-center white--text caption grey"
                    :class="hover ? 'darken-1' : 'darken-2'"
                    style="cursor: pointer; position: relative; white-space: nowrap"
                    :style="'height: 40px'"
                    @click="onDrawerExpand(true); $emit('clear')"
                >
                    <div style="position: absolute">
                        <adv-btn medium icon="clear" color-hover="transparent" color-text="white" />
                    </div>
                    <div class="text-center" v-text="'Clear Query Parameters'" :style="'width: 100%; padding: 0 50px 0 50px;'" />
                </div>
            </v-hover>
        </div>

        <div v-else-if="costumHeader" :style="'height:' + costumHeader + 'px'">
            <slot name="header" />
        </div>

        <div :style="stylingContent" @click="noAutoExpansion ? '' : onDrawerExpand(true)">
            <slot name="content" />
        </div>
    </div>
</div>
</template>

<script>
export default {
    data () {
        return {
            expanded: false,
            drawerWidth: '40px'
        }
    },

    props: {
        top: { type: Number, default: 90 },
        header: { type: Boolean, default: false },
        dialog: { type: Boolean, default: false },
        costumHeader: { type: Number, default: 0 },
        zIndex: { type: Number, default: 98 },
        maxWidth:  { type: [Number, String], default: 700 },
        miniWidth:  { type: [Number, String], default: 40 },
        hoverWidth:  { type: [Number, String], default: '75%' },
        delay: { type: Number, default: 350 },
        color: { type: String, default: 'drawer_bg' },
        collapse:  { type: [Number, String], default: null },
        expanding: { type: [Number, String], default: null },
        noAutoExpansion: { type: Boolean, default: false }
    },

    computed: {
        max_width () {
            return typeof this.maxWidth === 'number' ? (this.maxWidth + 'px') : this.maxWidth
        },
        mini_width () {
            return typeof this.miniWidth === 'number' ? (this.miniWidth + 'px') : this.miniWidth
        },
        hover_width () {
            return typeof this.hoverWidth === 'number' ? (this.hoverWidth + 'px') : this.hoverWidth
        },

        styling () {
            return [
                'position: fixed',
                'padding-top:' + (this.dialog ? 80 : this.top) + 'px',
                'height: calc(100vh - ' + (this.dialog ? 90 : 0) + 'px)',
                'overflow-y: hidden',
                'overflow-x: hidden',
                'z-index:' + this.zIndex,
                'max-width:' + this.max_width,
                'width:' + this.drawerWidth,
                'transition: width ' + this.delay + 'ms ease-out',
                '-webkit-transition: width ' + this.delay + 'ms ease-out',
                '-moz-transition: width ' + this.delay + 'ms ease-out',
                '-ms-transition: width ' + this.delay + 'ms ease-out',
                '-o-transition: width ' + this.delay + 'ms ease-out',
                'box-shadow: 0px 0px 10px rgba(0,0,0,0.25)'
            ].join(';\n')
        },

        stylingContent () {
            return [
                'height: calc(100vh - ' + ((this.dialog ? 170 : this.top) + (this.header ? 80 : this.costumHeader)) + 'px)',
                'width: 100%',
                'overflow-y: auto',
                'overflow-x: hidden'
            ].join(';\n')
        },

        toggle () {
            return {
                class: 'd-flex align-center justify-end light-blue darken-4',
                style: [
                    'position: fixed',
                    'z-index:' + (this.zIndex - 10),
                    'top: calc(50vh - 40px)',
                    'left:' + (this.dialog ? 50 : 0) + 'px;',
                    'max-width: calc(' + this.max_width + ' + ' + (this.dialog ? 8 : 33) + 'px)',
                    'width:' + (this.expanded ? ('calc(' + this.drawerWidth + ' + ' + (this.dialog ? 8 : 33) + 'px)') : '0'),
                    'height: 80px',
                    'cursor: pointer',
                    'transition: width ' + this.delay + 'ms ease-out',
                    '-webkit-transition: width ' + this.delay + 'ms ease-out',
                    '-moz-transition: width ' + this.delay + 'ms ease-out',
                    '-ms-transition: width ' + this.delay + 'ms ease-out',
                    '-o-transition: width ' + this.delay + 'ms ease-out',
                    'box-shadow: 0px 0px 10px rgba(0,0,0,0.15)'
                ].join(';\n')
            }
        }
    },

    watch: {
        collapse () {
            if (this.collapse) this.onDrawerExpand(false)
        },
        expanding () {
            if (this.expanding) this.onDrawerExpand(true)
        }
    },

    created () {
        this.drawerWidth = this.mini_width
    },

    methods: {
        onDrawerExpand (expand) {
            if (this.expanded !== expand) {
                if (expand) {
                    this.drawerWidth = this.hover_width;
                    this.activeTab = this.cachedTab
                }
                else {
                    this.drawerWidth = this.mini_width;
                    this.cachedTab = this.activeTab
                    this.activeTab = null

                }
                this.expanded = expand
                this.$emit('expand', expand)
            }
        }
    }
}
</script>
