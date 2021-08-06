<template>
    <div>
        <generic-entity-template
            :key="language"
            :entity="entity"
            :name="$root.label(component)"
            :attributes="attributes"
            default-sort-by="date_start.ASC"
            small-tiles
            gallery="id_period"
            :dialog="dialog"
            :select="select"
            :selected="selected"
            v-on:select="(emit) => { $emit('select', emit) }"
            v-on:close="$emit('close')"
            v-on:setFilter="(emit) => { this.attributes[emit.key].filter = emit.value }"
        >
            <!-- Filter ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:filters>
                <template v-for="(item, i) in attributes">
                    <div
                        v-if="item.filter !== undefined"
                        :key="'filter' + i"
                    >
                            <v-text-field dense outlined filled clearable
                                v-model="attributes[i].filter"
                                :label="item.text"
                                :prepend-icon="item.icon"
                            />
                    </div>
                </template>
            </template>

            <!-- Content Cards ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:content-tile-header="slot">
                {{ 'ID&nbsp;' + slot.item.id }}
            </template>

            <template v-slot:content-tile-body="slot">
                <div class="body-2 mb-3">
                    <div class="font-weight-bold" v-html="attributes['name_' + language].content(slot.item)"></div>
                    <div class="caption" v-html="attributes.nomisma.content(slot.item)"></div>
                </div>
                <div class="body-2 mb-3" v-html="attributes.date_start.content(slot.item) + ' – ' + attributes.date_end.content(slot.item)"></div>
                <div class="body-2" v-html="'(' + (language === 'de' ? 'EN' : 'DE') + ')&ensp;' + attributes['name_' + (language === 'de' ? 'en' : 'de')].content(slot.item)"></div>
            </template>

            <!-- Editor ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:editor="slot">
                <v-row>
                    <!-- Name -->
                    <v-col v-for="key in ['name_de', 'name_en']" :key="key" cols=12 md=6>
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item[key]"
                            :label="attributes[key].text"
                            :prepend-icon="attributes[key].icon"
                            hint="required" persistent-hint
                            counter=255
                        />
                    </v-col>

                    <!-- Year -->
                    <v-col v-for="key in ['date_start', 'date_end']" :key="key" cols=6 md=3>
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item[key]"
                            :label="attributes[key].text"
                            :prepend-icon="attributes[key].icon"
                            hint="required" persistent-hint
                            :rules="[$handlers.rules.date]"
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
            component:          'period',
            entity:             'periods',
            attributes:         {},
            key_name_class:     'caption font-weight-thin text-uppercase mb-1',
        }
    },

    props: {
        dialog:     { type: Boolean, default: false },
        select:     { type: Boolean, default: false },
        selected:   { type: [Number, String], default: null },
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
                name_de: {
                    default: null,
                    text: this.$root.label('name') + ' (DE)',
                    icon: 'label',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: false,
                    content: (item) => item?.name_de ?? '--'
                },
                name_en: {
                    default: null,
                    text: this.$root.label('name') + ' (EN)',
                    icon: 'label',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: false,
                    content: (item) => item?.name_en ?? '--'
                },
                date_start: {
                    default: null,
                    text: this.$root.label('year_start'),
                    icon: 'first_page',
                    header: true,
                    sortable: true,
                    clone: false,
                    content: (item) => item?.date_start ? this.$handlers.format.year(this.language, item.date_start) : '--'
                },
                date_end: {
                    default: null,
                    text: this.$root.label('year_end'),
                    icon: 'last_page',
                    header: true,
                    sortable: true,
                    clone: false,
                    content: (item) => item?.date_end ? this.$handlers.format.year(this.language, item.date_end) : '--'
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
                }
            }
        }
    }
}

</script>
