<template>
<div class="component-content">
    <slot
        name="content"
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
