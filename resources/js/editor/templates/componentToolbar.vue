<template>
<div class="component-tb">
    <div v-if="!flat" class="component-toolbar component-toolbar-shadow" />
    <div class="component-toolbar header_bg">
        <div :class="styling" style="width: 100%; height: 50px;">
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
        noFlex: { type: Boolean, default: false }
    },

    computed: {
        styling () {
            return this.noFlex ? '' : 'd-flex align-center'
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
