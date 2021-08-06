<template>
    <div>
        <generic-entity-template
            :entity="entity"
            :name="$root.label(component)"
            :attributes="attributes"
            :default-sort-by="'name.ASC'"
            small-tiles
            gallery="id_findspot"
            :dialog="dialog"
            :select="select"
            :selected="selected"
            v-on:select="(emit) => { $emit('select', emit) }"
            v-on:close="$emit('close')"
            v-on:setFilter="(emit) => { this.attributes[emit.key].filter = emit.value }"
        >
            <!-- Filter ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:filters>
                <template v-for="(item, key) in attributes">
                    <div
                        v-if="item.filter !== undefined"
                        :key="key"
                    >
                        <InputForeignKey
                            v-if="key === 'country'"
                            entity="countries"
                            :label="attributes[key].text"
                            :icon="attributes[key].icon"
                            :selected="attributes[key].filter"
                            v-on:select="(emit) => { attributes[key].filter = emit }"
                        />
                        <v-text-field dense outlined filled clearable
                            v-else
                            v-model="attributes[key].filter"
                            :label="item.text"
                            :prepend-icon="item.icon"
                        />
                    </div>
                </template>
            </template>

            <!-- Content Cards ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:search-tile-header="slot">
                {{ 'ID&nbsp;' + slot.item.id }}
            </template>

            <template v-slot:search-tile-body="slot">
                <div class="body-2 mb-3">
                    <div class="font-weight-bold" v-html="attributes.name.content(slot.item)"></div>
                    <div v-if="slot.item.link" class="caption" v-html="attributes.geonames.content(slot.item)"></div>
                </div>
                <div>
                    <div class="body-2" v-html="attributes.country.content(slot.item) + ', ' + attributes.alias.content(slot.item)"></div>
                    <div class="caption" v-html="attributes.longitude.content(slot.item) + ', ' + attributes.latitude.content(slot.item)"></div>
                </div>
                <div class="caption">
                    <div v-if="slot.item.comment" class="mt-3" v-html="attributes.comment.content(slot.item)"></div>
                </div>
            </template>

            <!-- Editor ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:editor="slot">
                <v-row>
                    <v-col cols=12 md=6>
                        <!-- Name -->
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item.name"
                            :label="attributes.name.text"
                            :prepend-icon="attributes.name.icon"
                            hint="required"
                            counter=255
                        />
                    </v-col>

                    <v-col cols=12 md=6>
                        <!-- Alias -->
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item.alias"
                            :label="attributes.alias.text"
                            :prepend-icon="attributes.alias.icon"
                            class="mb-3"
                            counter=255
                        />
                    </v-col>

                    <v-col cols=12 md=6>
                        <!-- Country -->
                        <InputForeignKey
                            entity="countries"
                            :label="attributes.country.text"
                            :icon="attributes.country.icon"
                            :selected="slot.item.country ? slot.item.country.toLowerCase() : null"
                            v-on:select="(emit) => { slot.item.country = emit }"
                        />
                    </v-col>

                    <v-col cols=12 md=6>
                        <!-- geonames -->
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item.geonames"
                            :label="attributes.geonames.text"
                            :prepend-icon="attributes.geonames.icon"
                            :rules="[$handlers.rules.link]"
                            counter=255
                            @click:prepend="$root.openInNewTab($handlers.constant.url.geonames + slot.item.geonames)"
                        />
                    </v-col>

                    <v-col cols=12 md=6>
                        <!-- Comment -->
                        <v-textarea dense outlined filled clearable
                            no-resize
                            rows=2
                            v-model="slot.item.comment"
                            :label="attributes.comment.text"
                            :prepend-icon="attributes.comment.icon"
                            counter=21845
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
            component:          'findspot',
            entity:             'findspots',
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
                    text: this.$root.label('name'),
                    icon: 'label',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: false,
                    content: (item) => item?.name ?? '--'
                },
                alias: {
                    default: null,
                    text: this.$root.label('alias'),
                    icon: 'short_text',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: false,
                    content: (item) => item?.alias ?? '--'
                },
                country: {
                    default: null,
                    text: this.$root.label('country'),
                    icon: 'public',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => item?.country ? item.country.toUpperCase() : '--'
                },
                geonames: {
                    default: null,
                    text: 'Geonames ID',
                    icon: 'link',
                    header: true,
                    clone: false,
                    content: (item) => this.$handlers.format.geonames_link(item.geonames)
                },
                comment: {
                    default: null,
                    text: this.$root.label('comment'),
                    icon: 'notes',
                    clone: false,
                    content: (item) => item?.comment ?? '--'
                },
                latitude: {
                    default: null,
                    text: this.$root.label('latitude'),
                    icon: 'my_location',
                    clone: false,
                    content: (item) => item?.latitude ?? '--'
                },
                longitude: {
                    default: null,
                    text: this.$root.label('longitude'),
                    icon: 'my_location',
                    clone: false,
                    content: (item) => item?.longitude ?? '--'
                }
            }
        }
    }
}

</script>
