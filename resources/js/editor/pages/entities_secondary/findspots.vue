<template>
    <div>
        <simpleDataTemplate
            DISABLED-:key="language"
            :entity="entity"
            :name="$root.label(component)"
            :headline="headline"
            :attributes="attributes"
            defaultSortBy="name"
            cards
            smallCards
            gallery="id_findspot"
            :select="select"
            :selected="selected"
            v-on:select="(emit) => { $emit('select', emit); $emit('close') }"
        >
            <!-- Search ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:search>
                <v-row>
                    <template v-for="(item, key) in attributes">
                        <v-col
                            v-if="item.filter !== undefined"
                            :key="key"
                            cols=12 
                            sm=4 
                            md=3
                        >
                            <InputForeignKey
                                v-if="key === 'country'"
                                entity="countries" 
                                :label="attributes[key].text"
                                :icon="attributes[key].icon"
                                :selected="attributes[key].filter"
                                v-on:select="(emit) => { attributes[key].filter = emit }"
                            ></InputForeignKey>
                            <v-text-field dense outlined filled clearable
                                v-else
                                v-model="attributes[key].filter"
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
            <template v-slot:editor-header="slot">
                {{ $root.label(component) + '&nbsp;' + slot.item.id }}
            </template>

            <template v-slot:editor-body="slot">
                <v-row>                    
                    <v-col cols=12 md=6>
                        <!-- Name -->
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item.name"
                            :label="attributes.name.text"
                            :prepend-icon="attributes.name.icon"
                            hint="required"
                            class="mb-3"
                            counter=255
                        ></v-text-field>
                        <!-- geonames -->
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item.geonames"
                            :label="attributes.geonames.text"
                            :prepend-icon="attributes.geonames.icon"
                            :rules="[$handlers.rules.link]"
                            counter=255
                            class="mb-3"
                            @click:prepend="$root.openInNewTab($handlers.constant.url.geonames + slot.item.geonames)"
                        ></v-text-field>
                        <!-- Country -->
                        <InputForeignKey
                            entity="countries" 
                            :label="attributes.country.text"
                            :icon="attributes.country.icon"
                            :selected="slot.item.country ? slot.item.country.toLowerCase() : null"
                            v-on:select="(emit) => { slot.item.country = emit }"
                        ></InputForeignKey>
                    </v-col>
                    
                    <v-col cols=12 md=6>
                        <!-- Alias -->
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item.alias"
                            :label="attributes.alias.text"
                            :prepend-icon="attributes.alias.icon"
                            class="mb-3"
                            counter=255
                        ></v-text-field>
                        <!-- Comment -->
                        <v-textarea dense outlined filled clearable 
                            no-resize
                            rows=2
                            v-model="slot.item.comment"
                            :label="attributes.comment.text"
                            :prepend-icon="attributes.comment.icon"
                            counter=21845
                        ></v-textarea>
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
            component:          'findspot',
            entity:             'findspots',
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
                    text: this.$root.label('name'),
                    icon: 'label',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: false,
                    content: (item) => { return item?.name ? item.name : '--' }
                },
                alias: {
                    default: null,
                    text: this.$root.label('alias'),
                    icon: 'short_text',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: false,
                    content: (item) => { return item?.alias ? item.alias : '--' }
                },
                country: {
                    default: null,
                    text: this.$root.label('country'),
                    icon: 'public',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => { return item?.country ? item.country.toUpperCase() : '--' }
                },
                geonames: {
                    default: null,
                    text: 'Geonames ID',
                    icon: 'link',
                    header: true,
                    clone: false,
                    content: (item) => { return this.$handlers.format.geonames_link(item.geonames) } 
                },
                comment: {
                    default: null,
                    text: this.$root.label('comment'),
                    icon: 'notes',
                    clone: false,
                    content: (item) => { return item?.comment ? item.comment : '--' }
                },
                latitude: {
                    default: null,
                    text: this.$root.label('latitude'),
                    icon: 'my_location',
                    clone: false,
                    content: (item) => { return item?.latitude ? item.latitude : '--' }
                },
                longitude: {
                    default: null,
                    text: this.$root.label('longitude'),
                    icon: 'my_location',
                    clone: false,
                    content: (item) => { return item?.longitude ? item.longitude : '--' }
                }
            }
        }
    }
}

</script>