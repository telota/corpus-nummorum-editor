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
            gallery="id_hoard"
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
                                v-if="['id_findspot', 'country'].includes(key)"
                                :entity="key === 'country' ? 'countries' : 'findspots'" 
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
                    <div v-if="slot.item.link" class="caption" v-html="attributes.link.content(slot.item)"></div>
                </div>
                <div class="body-2">
                    <div v-if="slot.item.year" v-html="attributes.year.content(slot.item)"></div>
                    <div v-html="attributes.country.content(slot.item) + ', ' + attributes.findspot.content(slot.item)"></div>
                </div>
                <div class="caption">
                    <div v-if="slot.item.description" class="mt-3" v-html="attributes.description.content(slot.item)"></div>
                    <div v-if="slot.item.reference" class="mt-3" v-html="attributes.reference.content(slot.item)"></div>
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
                        <!-- Link -->
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item.link"
                            :label="attributes.link.text"
                            :prepend-icon="attributes.link.icon"
                            :rules="[$handlers.rules.link]"
                            counter=255
                            class="mb-3"
                            @click:prepend="$root.openInNewTab(slot.item.link)"
                        ></v-text-field>
                        <!-- Year -->
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item.year"
                            :label="attributes.year.text"
                            :prepend-icon="attributes.year.icon"
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
                    
                    <v-col cols=12 md=6>                    
                        <!-- Findspot -->
                        <InputForeignKey 
                            entity="findspots" 
                            :label="attributes.id_findspot.text"
                            :icon="attributes.id_findspot.icon"
                            :selected="slot.item.id_findspot"
                            class="mb-3"
                            v-on:select="(emit) => { slot.item.id_findspot = emit }"
                        ></InputForeignKey>
                        <!-- Description & Reference -->
                        <v-textarea dense outlined filled clearable
                            v-for="(key, i) in ['description', 'reference']"
                            :key="key"
                            no-resize
                            rows=4
                            v-model="slot.item[key]"
                            :label="attributes[key].text"
                            :prepend-icon="attributes[key].icon"
                            :class="i === 0 ? 'mb-3' : ''"
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
            component:          'hoard',
            entity:             'hoards',
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
                country: {
                    default: null,
                    text: this.$root.label('country'),
                    icon: 'public',
                    header: true,
                    sortable: true,
                    filter: null,
                    content: (item) => { return item?.country ? item.country.toUpperCase() : '--' }
                },
                findspot: {
                    default: null,
                    text: this.$root.label('findspot'),
                    icon: 'pin_drop',
                    header: true,
                    sortable: true,
                    content: (item) => { return item?.findspot ? item.findspot : '--' }
                },
                id_findspot: {
                    default: null,
                    text: this.$root.label('findspot'),
                    icon: 'place',
                    filter: null,
                    clone: true,
                    content: (item) => { return item?.id_findspot ? item.id_findspot : '--' }
                },
                year: {
                    default: null,
                    text: this.$root.label('date'),
                    icon: 'watch_later',
                    header: true,
                    sortable: true,
                    clone: false,
                    content: (item) => { return item?.year ? item?.year : '--' }
                },
                link: {
                    default: null,
                    text: 'Link',
                    icon: 'link',
                    header: true,
                    clone: false,
                    content: (item) => { return item.link ? (item.link.split('//').pop().split('ww.').pop().split('/')[0] + this.$handlers.format.resource_link(item.link)) : '--' } 
                },
                description: {
                    default: null,
                    text: this.$root.label('description'),
                    icon: 'notes',
                    clone: false,
                    content: (item) => { return item?.description ? item.description : '--' }
                },
                reference: {
                    default: null,
                    text: this.$root.label('reference'),
                    icon: 'notes',
                    sortable: true,
                    clone: true,
                    content: (item) => { return item?.reference ? item.reference : '--' }
                },
                comment: {
                    default: null,
                    text: this.$root.label('comment'),
                    icon: 'short_text',
                    clone: false,
                    content: (item) => { return item?.comment ? item.comment : '--' }
                }
            }
        }
    }
}

</script>