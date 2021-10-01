<template>
<div
    :class="'component-content' + (dialog ? ' component-content-dialog' : '')"
    style="position: relative; overflow-y: hidden;"
>

    <!-- Center -->
    <div
        style="position: absolute; top: 0; width: 100%"
        :style="'padding-left:' + centerPad.left + 'px; padding-right:' + centerPad.right + 'px'"
    >
        <div
            id="split-screen-center"
            style="overflow-y: scroll"
            :style="'height: calc(100vh - ' + topOffset + 'px)'"
        >
            <slot />
        </div>
    </div>

    <!-- Right -->
    <template v-if="givenSlots.right">
        <div
            class="drawer_bg"
            style="position: absolute; top: 0; right: 0; box-shadow: 0px 0px 10px rgba(0,0,0,0.45);'"
            :style="'width:' + rightWidth + 'px'"
        >
            <div
                id="split-screen-right"
                style="overflow: hidden;"
                :style="'height: calc(100vh - ' + topOffset + 'px)'"
            >
                <slot name="right" />
            </div>
        </div>
    </template>

    <!-- Left -->
    <template v-if="givenSlots.left">
        <div style="position: absolute; top: 0; left: 0; box-shadow: 0px 0px 10px rgba(0,0,0,0.25);'">
            <vue-resizable
                :active="['r']"
                :width="left.startWidth"
                :minWidth="left.minWidth"
                :maxWidth="leftMaxWidth"
                @resize:move="resizeLeft"
            >
                <div
                    id="split-screen-left"
                    style="overflow: hidden;"
                    :style="'height: calc(100vh - ' + topOffset + 'px)'"
                >
                    <filter-drawer
                        v-if="filterDrawer"
                        header
                        @search="$emit('search')"
                        @clear="$emit('clear')"
                    >
                        <slot name="left" />
                    </filter-drawer>

                    <slot name="left" />
                </div>
            </vue-resizable>
        </div>

        <div
            :class="dragHandler.classes"
            :style="dragHandler.styles"
        />
    </template>
</div>
</template>

<script>
import vueResizable from 'vue-resizable'

export default {
    components: {
        vueResizable
    },

    data () {
        return {
            left: {
                minWidth: 40,
                startWidth: this.leftWidthDefault,
                currentWidth: this.leftWidthDefault
            }
        }
    },

    props: {
        dialog:             { type: Boolean, default: false },
        filterDrawer:       { type: Boolean, default: false },
        leftWidthDefault:   { type: Number, default: 350 },
        rightWidthDefault:  { type: Number, default: 200 },
        centerWidthMin:     { type: Number, default: 350 },
        scrollCenterToTop:  { type: Number, default: 350 }
    },

    computed: {
        givenSlots () {
            return {
                left: this.$slots.left ? true : false,
                right: this.$slots.right ? true : false
            }
        },

        topOffset () {
            return this.dialog ? 170 : 90
        },

        totalWidth () {
            return this.$vuetify.breakpoint.width - (this.dialog ? 50 : 0)
        },

        rightWidth () {
            return this.givenSlots.right ? Math.min(this.rightWidthDefault, Math.floor(this.totalWidth) / 4) : 0
        },

        centerWidth () {
            return this.totalWidth - this.centerPad.left - this.centerPad.right
        },

        leftMaxWidth () {
            const available = this.totalWidth - this.rightWidth - this.centerWidthMin
            return available > this.leftWidthDefault ? available : this.leftWidthDefault
        },

        centerPad () {
            return {
                left: this.givenSlots.left ? this.left.currentWidth : 0,
                right: this.rightWidth
            }
        },

        dragHandler () {
            const width = 7
            const height = 100

            return {
                classes: 'blue_prim',
                styles: [
                    'position: absolute',
                    'top: calc(50vh - ' + (this.topOffset - (this.dialog ? 50 : 0)) + 'px)',
                    'left:' + this.left.currentWidth + 'px',
                    'width:' + width + 'px',
                    'height:' + height + 'px',
                    'pointer-events: none',
                    'border-top-right-radius: 7px',
                    'border-bottom-right-radius: 7px'
                ].join(';')
            }
        }
    },

    watch: {
        scrollCenterToTop: function () {
            const el = document.getElementById('split-screen-center')
            el?.scrollTo({
                top: 0,
                left: 0,
                behavior: 'smooth'
            })
            this.$emit('scroll', el ? true : false)
        }
    },

    mounted () {
        this.emitSpace()
    },

    methods: {
        resizeLeft (event) {
            this.left.currentWidth = event.width
            this.emitSpace()
        },

        emitSpace () {
            this.$emit('resize', {
                total:  this.totalWidth,
                left:   this.left.currentWidth,
                center: this.centerWidth,
                right:  this.rightWidth
            })
        }
    }
}
</script>

<style scoped>
    #dragHandler {
        position: absolute;
        pointer-events: none;
    }
</style>
