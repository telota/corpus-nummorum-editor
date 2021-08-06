<template>
    <div>
        <generic-entity-template
            :key="language"
            :entity="entity"
            :name="$root.label(component)"
            :attributes="attributes"
            :default-sort-by="'name.ASC'"
            small-tiles
            gallery="id_mint"
            :dialog="dialog"
            :select="select"
            :selected="selected"
            v-on:select="(emit) => { $emit('select', emit) }"
            v-on:close="$emit('close')"
            v-on:setFilter="(emit) => { this.attributes[emit.key].filter = emit.value }"
        >
            <!-- Filter ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:filters>
                <v-text-field dense outlined filled clearable
                    v-for="key in ['id', 'name', ('name_' + language), ('region_' + language), 'nomisma']"
                    :key="key"
                    v-model="attributes[key].filter"
                    :label="attributes[key].text"
                    :prepend-icon="attributes[key].icon"
                />
            </template>

            <!-- Content Cards ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:search-tile-header="slot">
                {{ 'ID&nbsp;' + slot.item.id }}
            </template>

            <template v-slot:search-tile-body="slot">
                <div class="body-2 mb-3">
                    <div class="font-weight-bold" v-html="attributes.name.content(slot.item)" />
                    <div class="caption" v-html="attributes.nomisma.content(slot.item)" />
                </div>
                <div class="body-2 mb-3">
                    <span v-html="attributes['region_' + language].content(slot.item)" />,
                    <span v-html="attributes['name_' + language].content(slot.item)" />
                </div>
            </template>

            <!-- Editor ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:editor="slot">
                <v-row>
                    <!-- Name CN -->
                    <v-col cols=12 md=6>
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item.name"
                            :label="attributes.name.text"
                            :prepend-icon="attributes.name.icon"
                            hint="required" persistent-hint
                            counter=255
                        />
                    </v-col>

                    <!-- Nomisma -->
                    <v-col cols=12 md=6>
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item.nomisma"
                            :label="attributes.nomisma.text"
                            :prepend-icon="attributes.nomisma.icon"
                            counter=255
                            @click:prepend="$root.openInNewTab((slot.item.nomisma.slice(0, 4) != 'http' ? $handlers.constant.url.nomisma : '') + slot.item.nomisma)"
                        />
                    </v-col>
                </v-row>
            </template>

        </generic-entity-template>
    </div>
</template>



<script>

export default {
    data () {
        return {
            component:          'mint',
            entity:             'mints',
            attributes:         {},
            key_name_class:     'caption font-weight-thin text-uppercase mb-1',
        }
    },

    props: {
        dialog:     { type: Boolean, default: false },
        select:     { type: Boolean, default: false },
        selected:   { type: [Number, String], default: null }
    },

    computed: {
        language () {
            return this.$root.language === 'de' ? 'de' : 'en'
        }
    },

    watch: {
        language: function () { this.attributes = this.setAttributes() }
    },

    created () {
        this.attributes = this.setAttributes()
    },

    methods: {
        setAttributes () {
            return {
                id: {
                    default: null,
                    text: 'ID',
                    icon: 'fingerprint',
                    header: true,
                    sortable: true,
                    filter: null,
                    content: (item) => item?.id ?? '--'
                },
                name: {
                    default: null,
                    text: this.$root.label('name') + ' (CN)',
                    icon: 'label',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: false,
                    content: (item) => item?.name ?? '--'
                },
                name_de: {
                    default: null,
                    text: this.$root.label('name') + ' (DE)',
                    icon: 'label_important',
                    header: this.language === 'de',
                    sortable: this.language === 'de',
                    filter: null,
                    content: (item) => item?.name_de ?? '--'
                },
                name_en: {
                    default: null,
                    text: this.$root.label('name') + ' (EN)',
                    icon: 'label_important',
                    header: this.language != 'de',
                    sortable: this.language != 'de',
                    filter: null,
                    content: (item) => item?.name_en ?? '--'
                },
                region_de: {
                    default: null,
                    text: this.$root.label('region'),
                    icon: 'public',
                    header: this.language === 'de',
                    sortable: this.language === 'de',
                    filter: null,
                    content: (item) => item?.region_de ?? '--'
                },
                region_en: {
                    default: null,
                    text: this.$root.label('region'),
                    icon: 'public',
                    header: this.language != 'de',
                    sortable: this.language != 'de',
                    filter: null,
                    content: (item) => item?.region_en ?? '--'
                },
                nomisma: {
                    default: null,
                    text: 'Nomisma ID',
                    icon: 'monetization_on',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: false,
                    content: (item) => this.$handlers.format.nomisma_link(item.nomisma)
                },
            }
        }
    }
}

</script>
