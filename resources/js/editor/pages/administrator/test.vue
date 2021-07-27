<template>
<div>
    <div style="position:absolute; top: 50px; left: 50px;">
        <div v-text="'Current Entity: ' + entity" />
        <div v-text="'Currently selected: ' + selected" class="mb-5"/>

        <v-btn tile v-text="'switch entity'" @click="entity = entity === 'coins' ? 'materials' : 'coins'" />
        <v-btn tile v-text="'open Dialog'" @click="dialog = true" />
        <v-btn tile v-text="'open Details for CN coin 41'" @click="$store.commit('setDetailsDialog', { entity: 'coins', id: 41 })" />



        <InputForeignKey
            v-if="entity === 'materials'"
            :entity="entity"
            :label="entity"
            :icon="entity"
            :selected="selected"
            v-on:select="(emit) => { selected = emit }"
        />

    </div>

    <component
        v-if="entity === 'coins' && dialog"
        :is="entity"
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
            selected: 41,
            entity: 'coins',
            dialog: false
        }
    }
}
</script>
