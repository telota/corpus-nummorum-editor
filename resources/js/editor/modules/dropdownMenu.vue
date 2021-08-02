<template>
        <div
            style="position: relative"
            @mouseenter="on = true"
        >
            <div>
                <div v-if="text" v-text="text" />
                <v-icon v-else v-text="icon" />
            </div>

            <div v-if="on" style="position: absolute">
                <div v-for="(item, i) in items" :key="i">
                    <slot name="item" v-bind:item="item">
                        <div v-text="item.text" />
                    </slot>
                </div>
            </div>
        </div>
</template>

<script>
export default {
    data () {
        return {
            on: false
        }
    },

    props: {
        text: { type: String, default: null },
        icon: { type: String, default: null },
        items: { type: Array, default: () => [] },
        colorHover: { type: String, default: 'header_hover' },
    }
}
</script>
