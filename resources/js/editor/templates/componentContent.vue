<template>
<div class="component-content" :style="styling">
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
        top: { type: Number, default: null }
    },

    computed: {
        styling () {
            if (!this.top) return ''

            return [
                'top: ' + this.top + 'px',
                'height: calc(100vh - ' + this.top + 'px)'
            ].join(';\n')
        }
    },

    watch: {
        $route (to, from) {
        }
    },

    methods: {
    }
}

</script>
