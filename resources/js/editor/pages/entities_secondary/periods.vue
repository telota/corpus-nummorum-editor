<template>
    <div>
        <simpleDataTemplate
            :key="language"
            :entity="entity"
            :name="$root.label(component)"
            :headline="headline"
            :attributes="attributes"
            defaultSortBy="date_start"
            cards
            smallCards
            gallery="id_period"
            :select="select"
            :selected="selected"
            v-on:select="(emit) => { $emit('select', emit); $emit('close') }"
        >
            <!-- Search ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:search>
                <v-row>
                    <template v-for="(item, i) in attributes">
                        <v-col
                            v-if="item.filter !== undefined"
                            :key="i"
                            cols=12 
                            sm=4 
                            md=3
                        >
                            <v-text-field dense outlined filled clearable
                                v-model="attributes[i].filter"
                                :label="item.text"
                                :prepend-icon="item.icon"
                            ></v-text-field>
                        </v-col>
                    </template>
                </v-row>
            </template>

            <!-- Content Cards ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:content-cards-header="slot">
                {{ 'ID&nbsp;' + slot.item.id }}
            </template>

            <template v-slot:content-cards-body="slot">
                <div class="body-2 mb-3">
                    <div class="font-weight-bold" v-html="attributes['name_' + language].content(slot.item)"></div>
                    <div class="caption" v-html="attributes.nomisma.content(slot.item)"></div>
                </div>
                <div class="body-2 mb-3" v-html="attributes.date_start.content(slot.item) + ' – ' + attributes.date_end.content(slot.item)"></div>
                <div class="body-2" v-html="'(' + (language === 'de' ? 'EN' : 'DE') + ')&ensp;' + attributes['name_' + (language === 'de' ? 'en' : 'de')].content(slot.item)"></div>                
            </template>

            <!-- Editor ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:editor-header="slot">
                {{ $root.label(component) + '&nbsp;' + slot.item.id }}
            </template>

            <template v-slot:editor-body="slot">
                <v-row> 
                    <!-- Name -->
                    <v-col v-for="key in ['name_de', 'name_en']" :key="key" cols=12 md=6>
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item[key]"
                            :label="attributes[key].text"
                            :prepend-icon="attributes[key].icon"
                            hint="required" persistent-hint
                            counter=255
                        ></v-text-field>
                    </v-col>
                    <!-- Year -->
                    <v-col v-for="key in ['date_start', 'date_end']" :key="key" cols=12 xs=6 sm=3>
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item[key]"
                            :label="attributes[key].text"
                            :prepend-icon="attributes[key].icon"
                            hint="required" persistent-hint
                            :rules="[$handlers.rules.date]"
                        ></v-text-field>
                    </v-col>
                    <!-- Nomisma -->
                    <v-col cols=12 md=6>
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item.nomisma"
                            :label="attributes.nomisma.text"
                            :prepend-icon="attributes.nomisma.icon"
                            counter=255
                            @click:prepend="$root.openInNewTab((slot.item.nomisma.slice(0, 4) != 'http' ? $handlers.constant.url.nomisma : '') + slot.item.nomisma)"
                        ></v-text-field>
                    </v-col>
                </v-row>
            </template>

        </simpleDataTemplate>
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
        select:     { type: Boolean, default: false },
        selected:   { type: [Number, String], default: 0 }
    },

    computed: {
        l () { return this.$root.language },
        labels () { return this.$root.labels },
        language () { return this.$root.language === 'de' ? 'de' : 'en' },
        
        headline () {
            return this.$root.label(this.entity)
        }
    },

    watch: {
        language: function () { this.attributes = this.setAttributes() }
    },

    created () {
        this.$store.commit('setBreadcrumbs', [ // Set Breadcrumbs
            { label: this.$root.label(this.entity), to:'' }
        ]);

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
                    clone: false,
                    content: (item) => { return item?.name_de ? item.name_de : '--' }
                },
                name_en: {
                    default: null,
                    text: this.$root.label('name') + ' (EN)',
                    icon: 'label',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: false,
                    content: (item) => { return item?.name_en ? item.name_en : '--' }
                },
                date_start: {
                    default: null,
                    text: this.$root.label('year_start'),
                    icon: 'first_page',
                    header: true,
                    sortable: true,
                    clone: false,
                    content: (item) => { return item?.date_start ? this.$handlers.format.year(this.language, item.date_start) : '--' }
                },
                date_end: {
                    default: null,
                    text: this.$root.label('year_end'),
                    icon: 'last_page',
                    header: true,
                    sortable: true,
                    clone: false,
                    content: (item) => { return item?.date_end ? this.$handlers.format.year(this.language, item.date_end) : '--' }
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
                }
            }
        }
    }
}

</script>