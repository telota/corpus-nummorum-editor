<template>
<div :class="classing" :style="styling">
    <slot />
</div>
</template>

<script>
export default {
    props: {
        dialog:     { type: Boolean, default: false },
        margin:     { type: Array, default: () => [20, 40, 20,40] },
        padding:    { type: Number, default: 5 },
        colorBg:    { type: String, default: 'sheet_bg' }
    },

    computed: {
        dark () { return this.$vuetify.theme.dark },

        classing () {
            return [
                this.colorBg,
                'pa-' + this.padding
            ].join(' ')
        },

        marg () {
            return [
                parseInt(this.margin[0]) ?? 0,
                parseInt(this.margin[1]) ?? 0,
                parseInt(this.margin[2]) ?? 0,
                parseInt(this.margin[3]) ?? 0,
            ]
        },

        styling () {
            return [
                'min-height: calc(100vh - ' + ((this.dialog ? 170 : 90) + this.marg[0] + this.marg[2]) + 'px)',
                'margin: ' + this.marg.join('px ') + 'px;',
                'outline: 1px solid ' + (this.dark ? 'black' : 'grey'),
                'box-shadow: 0px 0px 5px rgba(0,0,0,0.1)'
            ].join(';\n')
        }
    }
}
</script>
