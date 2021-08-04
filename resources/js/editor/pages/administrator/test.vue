<template>
<div>
    <div style="position:absolute; top: 50px; left: 50px;">
        <div v-text="'Currently selected: ' + selected" class="mb-5" @click="dialog = true" />

    </div>

    <component
        v-if="dialog"
        :is="'file-manager'"
        dialog
        select
        :selected="selected"
        v-on:select="(emit) => { this.selected = emit }"
        v-on:close="dialog = false"
    />
</div>
</template>

<script>
export default {
    data () {
        return {
            mat: 2,
            leg: 40,
            selected: 'Monograms',
            l_selected: [40],
            m_selected: [2],
            entity: 'coins',
            dialog: false
        }
    },
    methods: {
        select (emit) {
            console.log('receive', emit)
            this.l_selected = emit
        }
    }
}
</script>
