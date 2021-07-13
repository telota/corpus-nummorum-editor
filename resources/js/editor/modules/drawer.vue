<template>
<div>
    <div
        :class="color"
        :style="styling"
        @mouseover="onDrawerHover(true)"
        dis-mouseleave="onDrawerHover(false)"
    >
        <div
            v-if="header"
            :style="'height:' + header + 'px'"
        >
            <slot name="header">
                <v-hover v-slot="{ hover }">
                    <div
                        class="d-flex align-center white--text light-blue"
                        :class="hover ? 'darken-3' : 'darken-4'"
                        style="cursor: pointer; position: relative"
                        :style="'height:' + header + 'px'"
                        @click="$emit('search')"
                    >
                        <div style="position: absolute">
                            <adv-btn medium icon="search" color-hover="transparent" color-text="white" />
                        </div>
                        <div class="font-weight-bold text-center" v-text="'SEARCH'" :style="'width: 100%; padding: 0 ' + (header + 10) + 'px 0 ' + (header + 10) + 'px;'" />
                    </div>
                </v-hover>
            </slot>
        </div>

        <div :style="stylingContent">
            <slot name="content" />
        </div>
    </div>

        <v-card
            :class="toggle.class"
            :style="toggle.style"
            :dark="!$vuetify.theme.dark"
            :disabled="!drawerHover"
            @click="onDrawerHover(false)"
        >
            <v-icon v-if="drawerHover" v-text="'arrow_back_ios'" />
        </v-card>
</div>
</template>

<script>
export default {
    data () {
        return {
            drawerHover: false,
            drawerWidth: '40px'
        }
    },

    props: {
        top: { type: Number, default: 90 },
        header: { type: Number, default: 0 },
        zIndex: { type: Number, default: 98 },
        maxWidth:  { type: [Number, String], default: 600 },
        miniWidth:  { type: [Number, String], default: 40 },
        hoverWidth:  { type: [Number, String], default: '50%' },
        delay: { type: Number, default: 350 },
        color: { type: String, default: 'header_bg' },
        collapse:  { type: [Number, String], default: null },
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
                'padding-top:' + this.top + 'px',
                'height: 100vh',
                //'overflow-y: auto',
                'overflow-y: hidden',
                'overflow-x: hidden',
                'z-index:' + this.zIndex,
                'max-width:' + this.max_width,
                'width:' + this.drawerWidth + ';',
                '-webkit-transition: width ' + this.delay + 'ms ease-out',
                '-moz-transition: width ' + this.delay + 'ms ease-out',
                '-ms-transition: width ' + this.delay + 'ms ease-out',
                '-o-transition: width ' + this.delay + 'ms ease-out',
                'box-shadow: 0px 0px 10px rgba(0,0,0,0.25)'
            ].join(';\n')
        },

        stylingContent () {
            return [
                'height: calc(100vh - ' + (this.top + this.header) + 'px)',
                'width: 100%',
                'overflow-y: auto',
                'overflow-x: hidden',
            ].join(';\n')
        },

        toggle () {
            return {
                class: 'd-flex align-center justify-center',
                style: [
                    'position: fixed',
                    'bottom: 0',
                    'z-index: 100',
                    'height:' + this.mini_width,
                    'width:' + this.mini_width,
                ].join(';\n')
            }
        }
    },

    watch: {
        collapse () {
            if (this.collapse) this.onDrawerHover(false)
        }
    },

    methods: {
        onDrawerHover (hovered) {
            if (this.drawerHover !== hovered) {
                if (hovered) {
                    this.drawerWidth = this.hover_width;
                    this.activeTab = this.cachedTab
                }
                else {
                    this.drawerWidth = this.mini_width;
                    this.cachedTab = this.activeTab
                    this.activeTab = null

                }
                this.drawerHover = hovered
                const delay = hovered ? (this.delay - (this.delay > 150 ? 150 : 0)) : 0

                setTimeout(() => {
                    this.$emit('hover', hovered)
                }, hovered ? delay : 0);
            }
        }
    }
}
</script>
