<template>
    <div>
        <generic-entity-template
            :key="language"
            :entity="entity"
            :name="$root.label(component)"
            :headline="headline"
            :attributes="attributes"
            default-sort-by="name.ASC"
            small-tiles
            gallery="id_group"
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
                    <v-text-field dense outlined filled clearable
                        v-if="item.filter !== undefined"
                        :key="i"
                        v-model="attributes[i].filter"
                        :label="item.text"
                        :prepend-icon="item.icon"
                    />
                </template>
            </template>

            <!-- Content Cards ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:content-tile-header="slot">
                {{ 'ID&nbsp;' + slot.item.id }}
            </template>

            <template v-slot:content-tile-body="slot">
                <div class="body-2 mb-3">
                    <div class="font-weight-bold" v-html="attributes.name.content(slot.item)"></div>
                    <!--<div class="caption" v-html="attributes.nomisma.content(slot.item)"></div>-->
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
                        <!-- Comment -->
                        <v-textarea dense outlined filled clearable
                            no-resize
                            rows=3
                            v-model="slot.item.comment"
                            :label="attributes.comment.text"
                            :prepend-icon="attributes.comment.icon"
                            class="mt-2"
                            counter=21845
                        />
                    </v-col>

                    <!-- Description -->
                    <v-col
                        v-for="(lang) in ['de', 'en']"
                        :key="'description' + lang"
                        cols=12
                        md=6
                    >
                        <v-textarea dense outlined filled clearable
                            no-resize
                            rows=2
                            v-model="slot.item['description_' + lang]"
                            :label="attributes['description_' + lang].text"
                            :prepend-icon="attributes['description_' + lang].icon"
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
            component:          'objectgroup',
            entity:             'objectgroups',
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
                    content: (item) => item?.id ?? '--'
                },
                name: {
                    default: null,
                    text: this.$root.label('name'),
                    icon: 'label',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => item?.name ?? '--'
                },
                description_de: {
                    default: null,
                    text: this.$root.label('description') + ' (DE)',
                    icon: 'short_text',
                    header: this.language === 'de' ? true : false,
                    filter: this.language === 'de' ? null : undefined,
                    clone: false,
                    content: (item) => item?.description_de ?? (item?.description_en ?? '--')
                },
                description_en: {
                    default: null,
                    text: this.$root.label('description') + ' (EN)',
                    icon: 'short_text',
                    header: this.language !== 'de' ? true : false,
                    filter: this.language !== 'de' ? null : undefined,
                    clone: false,
                    content: (item) => item?.description_en ?? (item?.description_de ?? '--')
                },
                comment: {
                    default: null,
                    text: this.$root.label('comment'),
                    icon: 'notes',
                    clone: false,
                    content: (item) => item?.comment ?? '--'
                },
                is_emission: {
                    default: null,
                    text: this.$root.label('status'),
                    icon: 'category',
                    header: true,
                    sortable: true,
                    content: (item) => item?.is_emission ? 'Emission' : 'Class'
                },

                creator: { default: null },
                editor: { default: null },
                created_at: { default: null },
                updated_at: { default: null }
            }
        }
    }
}

</script>
