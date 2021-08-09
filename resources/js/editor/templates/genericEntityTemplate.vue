<template>
<div>
    <dialog-template
        :dialog="dialog"
        :text="$root.label(entity)"
        :closing="closing"
        :select="select"
        :selected="selected"
        v-on:close="$emit('close')"
    >
        <!-- Search -->
        <search-template
            v-if="!section || section === 'search'"
            :key="refresh"
            :dialog="dialog"
            :entity="entity"
            :attributes="attributes"
            :defaultSortBy="defaultSortBy"
            :small-tiles="smallTiles"
            :default-layout="defaultLayout"
            :tiles="tiles"
            :select="select"
            :selected="selected"
            v-on:select="selectItem"
            v-on:edit="editItem"
            v-on:clone="cloneItem"
            v-on:setFilter="(emit) => { $emit('setFilter', emit) }"
            v-on:add="newItem"
            v-on:close="close"
        >
            <template v-slot:right>
                <slot name="right" />
            </template>

            <template v-slot:filters>
                <slot name="filters" />
            </template>

            <template v-slot:search-tile-header="slot">
                <slot name="search-tile-header" v-bind:item="slot.item" />
            </template>

            <template v-slot:search-tile-body="slot">
                <slot name="search-tile-body" v-bind:item="slot.item" />
            </template>
        </search-template>

        <!-- Editor -->
        <edit-template
            v-else-if="section === 'editor'"
            :key="refresh"
            :dialog="dialog"
            :entity="entity"
            :attributes="attributes"
            :id="id"
            :gallery="gallery"
            :select="select"
            :selected="selected"
            :linking="linking"
            :no-edit="noEdit"
            v-on:select="selectItem"
            v-on:close="close"
            v-on:add="newItem"
            v-on:clone="cloneItem"
            v-on:id="(emit) => { this.id = emit }"
            v-on:search="id = null"
        >
            <template v-slot:editor="slot">
                <slot name="editor" v-bind:item="slot.item" />
            </template>

            <template v-slot:gallery-link="slot">
                <slot name="gallery-link" v-bind:link="slot.link" />
            </template>
        </edit-template>

    </dialog-template>

</div>
</template>


<script>

import dialogTemplate from './dialogTemplate.vue'
import searchTemplate from './searchTemplate.vue'
import editTemplate from './editTemplate.vue'

export default {
    components: {
        dialogTemplate,
        searchTemplate,
        editTemplate
    },

    data () {
        return {
            section:    'search',
            IdSetter:   this.selected ? ('' + this.selected) : null,
            closing:    0,
            refresh:    0
        }
    },

    props: {
        dialog:         { type: Boolean, default: false },
        entity:         { type: String, required: true },
        attributes:     { type: Object, required: true },
        defaultSortBy:  { type: String, default: 'id.DESC' },
        headline:       { type: String },
        defaultLayout:  { type: String, default: 'table' },
        table:          { type: Boolean, default: true },
        tiles:          { type: Boolean, default: true },
        smallTiles:     { type: Boolean, default: false },
        gallery:        { type: String, default: null },
        linking:        { type: Boolean, default: false },
        sync:           { type: Boolean, default: false },
        noEdit:         { type: Boolean, default: false },

        itemLabel:      { type: String, default: null },

        select:         { type: Boolean, default: false },
        selected:       { type: [Number, String], default: null }
    },

    computed: {
        language () {
            return this.$root.language
        },

        id: {
            get: function () {
                if (this.dialog) return this.IdSetter
                else return this.$route.params.id
            },

            set: function (id) {
                if (this.dialog) this.IdSetter = id ? ('' + id) : null
                else this.$router.push('/' + this.entity + (id ? ('/' + id) : ''))
            }
        }
    },

    watch: {
        id () { this.checkId() }
    },

    created () {
        this.checkId()
    },

    methods: {
        checkId () {
            if (this.id) this.section = 'editor'
            else this.section = 'search'
        },

        newItem () {
            if (this.dialog) this.IdSetter = 'new'
            else if (this.$route.path !== '/' + this.entity + '/new') this.$router.push('/' + this.entity + '/new')
            else ++this.refresh
        },

        selectItem (emit) {
            this.$emit('select', emit)
            if (confirm('Item ' + emit.id + ' has been selected. Close Dialog?')) this.close()
        },

        editItem (emit) {
            if (emit.id) this.id = emit.id
        },

        cloneItem (emit) {
            if (emit.id) this.id = 'clone-' + emit.id
        },

        close () {
            ++this.closing
        }
    }
}
</script>
