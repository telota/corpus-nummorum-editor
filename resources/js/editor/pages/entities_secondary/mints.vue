<template>
    <div>
        <simpleDataTemplate
            :key="language"
            :entity="entity"
            :name="$root.label(component)"
            :headline="headline"
            :attributes="attributes"
            defaultSortBy="name"
            cards
            smallCards
            gallery="id_mint"
            :select="select"
            :selected="selected"
            v-on:select="(emit) => { $emit('select', emit); $emit('close') }"
        >
            <!-- Search ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:search>
                <v-row>
                    <v-col
                        v-for="key in ['id', 'name', ('name_' + language), ('region_' + language), 'nomisma']"
                        :key="key"
                        cols=12 
                        sm=4 
                        md=3
                    >
                        <v-text-field dense outlined filled clearable
                            v-model="attributes[key].filter"
                            :label="attributes[key].text"
                            :prepend-icon="attributes[key].icon"
                        ></v-text-field>
                    </v-col>
                </v-row>
            </template>

            <!-- Content Cards ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:content-cards-header="slot">
                {{ 'ID&nbsp;' + slot.item.id }}
            </template>

            <template v-slot:content-cards-body="slot">
                <div class="body-2 mb-3">
                    <div class="font-weight-bold" v-html="attributes.name.content(slot.item)"></div>
                    <div class="caption" v-html="attributes.nomisma.content(slot.item)"></div>
                </div>
                <div class="body-2 mb-3">
                    <span v-html="attributes['region_' + language].content(slot.item)"></span>, 
                    <span v-html="attributes['name_' + language].content(slot.item)"></span>
                </div>
            </template>

            <!-- Editor ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:editor-header="slot">
                {{ $root.label(component) + '&nbsp;' + slot.item.id }}
            </template>

            <template v-slot:editor-body="slot">
                <v-row>
                    <!-- Name CN -->
                    <v-col cols=12 md=6>
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item.name"
                            :label="attributes.name.text"
                            :prepend-icon="attributes.name.icon"
                            hint="required" persistent-hint
                            counter=255
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
            component:          'mint',
            entity:             'mints',
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
                name: {
                    default: null,
                    text: this.$root.label('name') + ' (CN)',
                    icon: 'label',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: false,
                    content: (item) => { return item?.name ? item.name : '--' }
                },
                name_de: {
                    default: null,
                    text: this.$root.label('name') + ' (DE)',
                    icon: 'label_important',
                    header: this.language === 'de',
                    sortable: this.language === 'de',
                    filter: null,
                    content: (item) => { return item?.name_de ? item.name_de : '--' }
                },
                name_en: {
                    default: null,
                    text: this.$root.label('name') + ' (EN)',
                    icon: 'label_important',
                    header: this.language != 'de',
                    sortable: this.language != 'de',
                    filter: null,
                    content: (item) => { return item?.name_en ? item.name_en : '--' }
                },
                region_de: {
                    default: null,
                    text: this.$root.label('region'),
                    icon: 'public',
                    header: this.language === 'de',
                    sortable: this.language === 'de',
                    filter: null,
                    content: (item) => { return item?.region_de ? item.region_de : '--' }
                },
                region_en: {
                    default: null,
                    text: this.$root.label('region'),
                    icon: 'public',
                    header: this.language != 'de',
                    sortable: this.language != 'de',
                    filter: null,
                    content: (item) => { return item?.region_en ? item.region_en : '--' }
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
            }
        }
    }
}

</script>