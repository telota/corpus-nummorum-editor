<template>
<div class="component-tb">
    <div v-if="!flat" class="component-toolbar component-toolbar-shadow" :style="styling" />
    <div class="component-toolbar header_bg" :style="styling">
        <div :class="classes" :style="'width: 100%; height:' + (this.height ? this.height: 50) + 'px;'">
            <slot name="toolbar" />
        </div>
    </div>
</div>
</template>


<script>

export default {
    /* data () {
        return {
        }
    },*/

    props: {
        flat: { type: Boolean, default: false },
        noFlex: { type: Boolean, default: false },
        top: { type: Number, default: null },
        height: { type: Number, default: null },
    },

    computed: {
        classes () {
            return this.noFlex ? '' : 'd-flex align-center'
        },
        styling () {
            return [
                this.top ? ('top:' + this.top + 'px') : null,
                this.height ? ('height:' + this.height + 'px') : null,
            ].join('\n;')
        }
    },

    watch: {
        /*$route (to, from) {
            if (this.$route.params.id) { this.getSingleItem(this.$route.params.id) }
        }*/
    },

    /*methods: {
    }*/
}

</script>
