<template>
    <div>
        <generic-entity-template
            :key="language"
            :entity="entity"
            :attributes="attributes"
            default-sort-by="name.ASC"
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
                <v-text-field dense outlined filled clearable
                    v-model="attributes.nomisma_concated.filter"
                    :label="attributes.nomisma_concated.text"
                    :prepend-icon="attributes.nomisma_concated.icon"
                />
            </template>

            <!-- Editor ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:editor="slot">
                <!-- Nomisma -->
                <subheader
                    label="Nomisma IDs"
                    :count="slot.item.nomisma"
                    add
                    class="mb-5"
                    @add="slot.item.nomisma.push({
                        entity: 'mint',
                        id: null
                    })"
                />

                <template v-if="slot.item.nomisma[0]">
                    <div v-for="(iterator, i) in slot.item.nomisma" :key="i" class="mt-n3">
                        <v-row>
                            <v-col cols="6">
                                <v-text-field dense filled outlined
                                    v-model="slot.item.nomisma[i].id"
                                    label="Nomisma ID"
                                />
                            </v-col>

                            <v-col cols="6">
                                <div class="d-flex">
                                    <v-select dense filled outlined
                                        v-model="slot.item.nomisma[i].entity"
                                        :items="nomismaEntities"
                                        label="Entity"
                                        :menu-props="{ offsetY: true }"
                                    />
                                    <v-btn icon class="ml-3" @click="slot.item.nomisma.splice(i, 1)">
                                        <v-icon>delete</v-icon>
                                    </v-btn>
                                </div>
                            </v-col>
                        </v-row>
                    </div>
                </template>
                <div
                    v-else
                    class="font-weight-bold text-center body-2 mb-5 mt-n3 orange--text"
                    v-text="'Please add at least one Nomisma ID!'"
                />

                <!-- Text -->
                <subheader label="Text" />

                <div class="body-2 mb-8 text-center">
                    Fußnoten mit geschweiften Klammern <code v-text="'{ }'" /> und Links mit eckigen Klammern <code v-text="'[ ]'" /> auszeichnen.<br/>
                    Mit <code v-text="'<b> </b>'" /> kann Text fett <b>formatiert</b> werden, mit <code v-text="'<i> </i>'" /> <i>kursiv</i>.
                </div>

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
                                    :rows="section === 'label' ? 2 : 7"
                                    v-model="slot.item[l + '_' + section]"
                                    :hint="section === 'label' ? 'required' : null" 
                                    :persistent-hint="section === 'label' ? true : false"
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
                'label',
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

            nomismaEntities: [
                'mint',
                'ruler',
                'tribe'
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
                nomisma_concated: {
                    default: null,
                    text: 'Nomisma ID',
                    icon: 'monetization_on',
                    filter: null,
                    content: (item) => {
                        if (!item?.nomisma_concated) return '--'
                        const links = item.nomisma_concated.split(', ').split.maph((id) => this.$handlers.format.nomisma_link(id))
                        return links.join(', ')
                    }
                },
                name: {
                    default: null,
                    text: 'Text',
                    icon: 'label',
                    header: true,
                    sortable: true,
                    content: (item) => {
                        let header = ''
                        let text = '--'

                        header += '<h3 class="body-2 mb-1">' + (item?.[this.language + '_label'] ? item[this.language + '_label'] : ('ID ' + item.id)) + '</h3>'                        
                        header += item?.nomisma_concated ? ('<div class="font-weight-bold mb-1">' + item.nomisma_concated.split(', ').map((id) => this.$handlers.format.nomisma_link(id)).join(', ') + '</div>') : ''

                        this.sections.forEach((section) => {
                            const key = this.language + '_' + section
                            if (section !== 'label' && text === '--' && item[key]) {
                                const split = item[key].slice(0, 500).split(/\s+/)
                                split[split.length - 1] =  '...'
                                text = '<div class="text-justify">' + split.join(' ') + '</div>'
                            }
                        })
                        return header + text
                    }
                },
                nomisma: {
                    default: [{ entity: 'mint', id: null }],
                    text: 'Nomisma',
                    icon: 'museum',
                    content: (item) => item?.nomisma ?? []
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
                        content: (item) => item?.[key] ?? '--'
                    }
                })
            })

            return attributes
        }
    }
}

</script>
