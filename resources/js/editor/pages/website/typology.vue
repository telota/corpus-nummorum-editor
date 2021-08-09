<template>
    <div>
        <generic-entity-template
            :key="language"
            :entity="entity"
            :attributes="attributes"
            default-sort-by="mint.ASC"
            :dialog="dialog"
            :select="select"
            :selected="selected"
            :tiles="false"
            v-on:select="(emit) => { $emit('select', emit) }"
            v-on:close="$emit('close')"
            v-on:setFilter="(emit) => { this.attributes[emit.key].filter = emit.value }"
        >
            <!-- Filter ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:filters>
                <v-text-field dense outlined filled clearable
                    v-model="attributes.id.filter"
                    :label="attributes.id.text"
                    :prepend-icon="attributes.id.icon"
                />
                <InputForeignKey
                    entity="mints"
                    :label="attributes.id_mint.text"
                    :icon="attributes.id_mint.icon"
                    :selected="attributes.id_mint.filter"
                    v-on:select="(emit) => { attributes.id_mint.filter = emit }"
                />
            </template>

            <!-- Editor ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:editor="slot">
                <v-row>
                    <v-col cols=12 md=6>
                        <InputForeignKey
                            entity="mints"
                            :label="attributes.id_mint.text"
                            :icon="attributes.id_mint.icon"
                            :selected="slot.item.id_mint"
                            v-on:select="(emit) => { slot.item.id_mint = emit }"
                        />
                    </v-col>

                    <v-col cols=12 md=6>
                        <!-- Author -->
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item.author"
                            :label="attributes.author.text"
                            :prepend-icon="attributes.author.icon"
                            counter=255
                        />
                    </v-col>
                </v-row>

                <v-divider class="mt-2 mb-4" />

                <div class="body-2">
                    Fußnoten mit geschweiften Klammern " {} " und Links mit eckigen Klammern " [] " auszeichnen.<br/>
                    Mit <code>< b >< /b ></code> kann Text fett <b>formatiert</b> werden, mit <code>< i >< /i ></code> <i>kursiv</i>. (Bitte keine Leerzeichen zwischen Tag und spitzen Klammern setzen)
                </div>

                <v-divider class="mt-4 mb-7" />

                <v-row>
                    <template v-for="(section) in sections">
                        <template v-for="(l) in languages">
                            <v-col :key="l + '_' + section" cols=12 md=6>
                                <div class="d-flex pb-2">
                                    <div class="font-weight-bold mr-2 ml-2" v-text="'Section ' + attributes[l + '_' + section].text" />
                                    <v-icon v-text="'clear'" @click="slot.item[l + '_' + section] = null" />
                                </diV>

                                <v-textarea dense outlined filled
                                    no-resize
                                    rows=7
                                    v-model="slot.item[l + '_' + section]"
                                    counter=21845
                                />
                            </v-col>
                        </template>
                    </template>
                </v-row>

                <v-divider class="mt-2 mb-7" />

                <v-row>
                    <v-col v-for="section in ['bibliography', 'links']" :key="section" cols=12 md=6>
                        <div class="d-flex pb-2">
                            <div class="font-weight-bold mr-2 ml-2" v-text="attributes[section].text" />
                            <v-icon v-text="'clear'" @click="slot.item[section] = null" />
                        </diV>

                        <v-textarea dense outlined filled clearable
                            no-resize
                            rows=7
                            v-model="slot.item[section]"
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
            component:          'Typology',
            entity:             'typology',
            attributes:         {},
            key_name_class:     'caption font-weight-thin text-uppercase mb-1',

            upload:             false,
            fileManager:        false,
            directory:          'typology',
            htmleditor:         false,

            sections: [
                'topography',
                'research',
                'typology',
                'metrology',
                'chronology',
                'special',
                'classic',
                'hellenistic',
                'imperial'
            ],

            languages: ['de', 'en']
        }
    },

    props: {
        dialog:     { type: Boolean, default: false },
        select:     { type: Boolean, default: false },
        selected:   { type: [Number, String], default: 0 }
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
            const attributes = {
                id: {
                    default: null,
                    text: 'ID',
                    icon: 'fingerprint',
                    header: true,
                    sortable: true,
                    filter: null,
                    content: (item) => item?.id ?? '--'
                },
                author: {
                    default: null,
                    text: this.$root.label('author'),
                    icon: 'person',
                    content: (item) => item?.author ?? '--'
                },
                id_mint: {
                    default: null,
                    text: 'ID Mint',
                    icon: 'museum',
                    filter: null,
                    content: (item) => item?.id_mint ? (item.mint ?? item.id_mint) : '--'
                },
                mint: {
                    default: null,
                    text: this.$root.label('mint'),
                    icon: 'museum',
                    header: true,
                    sortable: true,
                    content: (item) => item?.mint ?? '--'
                },
                bibliography: {
                    default: null,
                    text: this.$root.label('bibliography'),
                    icon: 'menu_book',
                    content: (item) => item?.bibliography ?? '--'
                },
                links: {
                    default: null,
                    text: this.$root.label('links'),
                    icon: 'link',
                    content: (item) => item?.links ?? '--'
                },
            }

            this.languages.forEach((language) => {
                this.sections.forEach((section) => {
                    const key = language + '_' + section
                    attributes[key] = {
                        default: null,
                        text: this.$root.label(section) + ' (' + language.toUpperCase() + ')',
                        icon: 'notes',
                        header: language === this.language && section === 'topography' ? true : false,
                        content: (item) => language === this.language && section === 'topography' && item?.[key] ? (item[key].slice(0, 250) + ' ...') : (item?.[key] ?? '--')
                    }
                })
            })

            return attributes
        }
    }
}

</script>
