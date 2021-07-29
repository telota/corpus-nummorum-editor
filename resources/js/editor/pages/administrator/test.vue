<template>
<div>
    <div style="position:absolute; top: 50px; left: 50px;">
        <div v-text="'Current Entity: ' + entity" />
        <div v-text="'Currently selected: ' + selected" class="mb-5"/>

        <v-btn tile v-text="'switch entity'" @click="entity = entity === 'coins' ? 'materials' : 'coins'" />
        <v-btn tile v-text="'open Dialog'" @click="dialog = true" />
        <v-btn tile v-text="'open Details for CN coin 41'" @click="$store.commit('setDetailsDialog', { entity: 'coins', id: 41 })" />



        <InputForeignKey
            entity="materials"
            label="materials"
            class="mt-5"
            :selected="mat"
            v-on:select="(emit) => { mat = emit }"
        />

        <InputForeignKey
            entity="legends"
            label="legends"
            class="mt-5"
            :selected="leg"
            v-on:select="(emit) => { leg = emit }"
        />

        <SearchForeignKey
            entity="materials"
            label="materials"
            class="mt-5"
            :selected="m_selected"
            selected_key="id"
            v-on:select="select"
        />

        <SearchForeignKey
            entity="legends"
            label="legends"
            class="mt-5"
            :selected="l_selected"
            selected_key="id"
            v-on:select="select"
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
            mat: 2,
            leg: 40,
            selected: 41,
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
