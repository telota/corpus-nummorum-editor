<template>
<div class="component-toolbar header_bg">
    <div v-if="!flat" class="component-toolbar component-toolbar-shadow" />
    <slot
        name="toolbar"
        dis-v-bind:item="item"
    />
</div>
</template>


<script>

export default {
    data () {
        return {
            item: {}
        }
    },

    props: {
        flat: { type: Boolean, default: false }
    },

    computed: {
    },

    watch: {
        $route (to, from) {
            if (this.$route.params.id) { this.getSingleItem(this.$route.params.id) }
        }
    },

    methods: {
    }
}

</script>
