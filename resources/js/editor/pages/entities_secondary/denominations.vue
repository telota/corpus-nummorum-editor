<template>
    <div>
        <generic-entity-template
            :key="language"
            :entity="entity"
            :name="$root.label(component)"
            :headline="headline"
            :attributes="attributes"
            :defaultSortBy="'name_' + language + '.ASC'"
            smallTiles
            gallery="id_denomination"
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
            <template v-slot:search-tile-header="slot">
                {{ 'ID&nbsp;' + slot.item.id }}, {{ attributes['name_' + language].content(slot.item) }}
            </template>

            <template v-slot:search-tile-body="slot">
                <div class="body-2 mb-3">
                    <div class="font-weight-bold" v-html="attributes['name_' + language].content(slot.item)"></div>
                    <div class="caption" v-html="attributes.nomisma.content(slot.item)"></div>
                </div>
                <div class="body-2" v-html="'(' + (language === 'de' ? 'EN' : 'DE') + ')&ensp;' + attributes['name_' + (language === 'de' ? 'en' : 'de')].content(slot.item)"></div>
                <div v-if="slot.item.comment" class="caption mt-3" v-html="attributes.comment.content(slot.item)"></div>
            </template>

            <!-- Editor ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:editor="slot">
                <v-row>
                    <v-col cols=12 md=6>
                        <!-- Name DE -->
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item.name_de"
                            :label="attributes.name_de.text"
                            :prepend-icon="attributes.name_de.icon"
                            hint="required"
                            class="mb-3"
                            counter=255
                        ></v-text-field>

                        <!-- Name EN -->
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item.name_en"
                            :label="attributes.name_en.text"
                            :prepend-icon="attributes.name_en.icon"
                            hint="required"
                            class="mb-3"
                            counter=255
                        ></v-text-field>

                        <!-- Nomisma -->
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item.nomisma"
                            :label="attributes.nomisma.text"
                            :prepend-icon="attributes.nomisma.icon"
                            counter=255
                            @click:prepend="$root.openInNewTab((slot.item.nomisma.slice(0, 4) != 'http' ? $handlers.constant.url.nomisma : '') + slot.item.nomisma)"
                        ></v-text-field>
                    </v-col>

                    <!-- Comment -->
                    <v-col cols=12 md=6>
                        <v-textarea dense outlined filled clearable
                            no-resize
                            rows=3
                            v-model="slot.item.comment"
                            :label="attributes.comment.text"
                            :prepend-icon="attributes.comment.icon"
                            counter=21845
                        ></v-textarea>
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
            component:          'denomination',
            entity:             'denominations',
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
        },
        headline () {
            return this.$root.label(this.entity)
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
                    content: (item) => { return item?.id ? item.id : '--' }
                },
                name_de: {
                    default: null,
                    text: this.$root.label('name') + ' (DE)',
                    icon: 'label',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => { return item?.name_de ? item.name_de : '--' }
                },
                name_en: {
                    default: null,
                    text: this.$root.label('name') + ' (EN)',
                    icon: 'label',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => { return item?.name_en ? item.name_en : '--' }
                },
                nomisma: {
                    default: null,
                    text: 'Nomisma ID',
                    icon: 'monetization_on',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: false,
                    content: (item) => { return this.$handlers.format.nomisma_link(item.nomisma) }
                },
                comment: {
                    default: null,
                    text: this.$root.label('comment'),
                    icon: 'notes',
                    clone: false,
                    content: (item) => { return item?.comment ? item.comment : '--' }
                }
            }
        }
    }
}

</script>
