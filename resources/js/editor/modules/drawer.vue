<template>
    <div
        :class="color"
        :style="styling"
        @mouseover="onDrawerHover(true)"
        @mouseleave="onDrawerHover(false)"
    >
        <slot />
    </div>
</template>

<script>
export default {
    data () {
        return {
            drawerHover: false,
            drawerWidth: this.miniWidth + 'px',
        }
    },

    props: {
        top: { type: Number, default: 90 },
        zIndex: { type: Number, default: 98 },
        maxWidth:  { type: Number, default: 700 },
        miniWidth:  { type: Number, default: 40 },
        hoverWidth:  { type: [Number, String], default: '50%' },
        delay: { type: Number, default: 350 },
        color: { type: String, default: 'header_bg' }
    },

    computed: {
        styling () {
            return [
                'position: fixed',
                'top:' + this.top + 'px',
                'height: calc(100vh - ' + this.top + 'px)',
                'overflow-y: auto',
                'overflow-x: hidden',
                'z-index:' + this.zIndex,
                'max-width:' + this.maxWidth + 'px',
                'width:' + this.drawerWidth + ';',
                '-webkit-transition: width ' + this.delay + 'ms ease-out',
                '-moz-transition: width ' + this.delay + 'ms ease-out',
                '-ms-transition: width ' + this.delay + 'ms ease-out',
                '-o-transition: width ' + this.delay + 'ms ease-out'
            ].join(';\n')
        }
    },

    methods: {
        onDrawerHover (hovered) {
            if (this.drawerHover !== hovered) {
                if (hovered) {
                    this.drawerWidth = this.hoverWidth;
                    this.activeTab = this.cachedTab
                }
                else {
                    this.drawerWidth = this.miniWidth + 'px';
                    this.cachedTab = this.activeTab
                    this.activeTab = null

                }
                this.drawerHover = hovered
                setTimeout(() => { this.$emit('hover', hovered) }, hovered ? (this.delay - 150) : 0);
            }
        }
    }
}
</script>
