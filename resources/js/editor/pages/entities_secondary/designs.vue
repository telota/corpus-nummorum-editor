<template>
    <div>
        <simpleDataTemplate
            :key="language"
            :sync="true"
            :entity="entity"
            :name="$root.label(component)"
            :headline="headline"
            :attributes="attributes"
            :defaultSortBy="'name_' + language"
            defaultDisplay="cards"
            cards
            smallCards
            gallery="id_design"
            :select="select"
            :selected="selected"
            v-on:select="(emit) => { $emit('select', emit); $emit('close') }"
        >
            <!-- Search ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:search>
                <v-row class="mb-4">
                    <v-col
                        v-for="(key) in ['id', 'name_de', 'name_en']"
                        :key="key"
                        cols=12 
                        sm=4 
                        md=3
                        class="mb-n4"
                    >
                        <v-text-field dense outlined filled clearable
                            v-model="attributes[key].filter"
                            :label="attributes[key].text"
                            :prepend-icon="attributes[key].icon"
                        ></v-text-field>
                    </v-col>
                    <v-col
                        v-for="(key) in ['role', 'side', 'border_dots']"
                        :key="key"
                        cols=12 
                        sm=4 
                        md=3
                        class="mb-n4"
                    >
                        <v-select dense outlined filled clearable
                            v-model="attributes[key].filter"
                            :items="key === 'side' ? sides : (key === 'role' ? roles : yesNo)"
                            :label="attributes[key].text"
                            :prepend-icon="attributes[key].icon"
                        ></v-select>
                    </v-col>
                </v-row>
            </template>

            <!-- Content Cards ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:content-cards-header="slot">
                {{ 'ID&nbsp;' + slot.item.id }}
            </template>

            <template v-slot:content-cards-body="slot">
                <div 
                    class="body-2 mb-3"
                    v-html="attributes.role.content(slot.item) + ', ' + attributes.side.content(slot.item)"
                ></div>
                <!-- Image -->
                <Imager
                    :key="slot.item.id"
                    :item="slot.item"
                    hide_text
                    class="mb-3"
                ></Imager>
                <div 
                    v-for="(key) in [(language === 'de' ? 'de' : 'en'), (language === 'de' ? 'en' : 'de')]"
                    :key="key"
                    class="body-2 mb-3"
                    v-html="'(' + key.toUpperCase() + ')&ensp;' + attributes['name_' + key].content(slot.item)"
                ></div>
            </template>

            <!-- Editor ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:editor-header="slot">
                {{ $root.label(component) + '&nbsp;' + slot.item.id }}
            </template>

            <template v-slot:editor-body="slot">
                <v-row>
                    <v-col 
                        v-for="key in ['name_de', 'name_en']"
                        :key="key"
                        cols=12 
                        md=6
                    >
                        <v-textarea dense outlined filled clearable
                            no-resize
                            rows=3
                            v-model="slot.item[key]"
                            :label="attributes[key].text"
                            :prepend-icon="attributes[key].icon"
                            class="mb-3"
                            counter=21845
                        ></v-textarea>
                    </v-col>
                    <v-col 
                        v-for="key in ['border_dots', 'role', 'side']"
                        :key="key"
                        cols=12 
                        md=3
                        class="mt-n5"
                    >
                        <v-select dense outlined filled
                            v-model="slot.item[key]"
                            :items="key === 'role' ? roles : (key === 'side' ? sides : yesNo)"
                            :label="attributes[key].text"
                            :prepend-icon="attributes[key].icon"
                        ></v-select>     
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
            component:          'design',
            entity:             'designs',
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
        },

        // Dropdowns
        yesNo () {
            return this.$store.state.lists.dropdowns.yesNo.map((item) => { return { value: item.value, text: this.$root.label(item.text) }})
        },
        sides () {
            return this.$store.state.lists.dropdowns.sides.map((item) => { return { value: item.value, text: this.$root.label(item.text) }})
        },
        roles () {
            return this.$store.state.lists.dropdowns.typeCoin.map((item) => { return { value: item.value, text: this.$root.label(item.text) }})
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
                    text: this.$root.label('design') + ' (DE)',
                    icon: 'label',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => { return item?.name_de ? item.name_de : '--' }
                },
                name_en: {
                    default: null,
                    text: this.$root.label('design') + ' (EN)',
                    icon: 'label',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => { return item?.name_en ? item.name_en : '--' }
                },
                border_dots: {
                    default: 0,
                    text: this.$root.label('border_dots'),
                    icon: 'filter_tilt_shift',
                    filter: null,
                    clone: true,
                    content: (item) => { return this.yesNo.find((row) => row.value == item.border_dots)?.text } 
                },
                role: {
                    default: 2,
                    text: this.$root.label('kind'),
                    icon: 'help_outline',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => { return this.roles.find((row) => row.value == item?.role)?.text } 
                },
                side: {
                    default: 2,
                    text: this.$root.label('side'),
                    icon: 'tonality',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => { return this.sides.find((row) => row.value == item?.side)?.text } 
                }
            }
        }
    }
}

</script>