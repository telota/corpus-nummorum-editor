<template>
    <div>
        <simpleDataTemplate
            :key="language"
            :sync="true"
            :entity="entity"
            :name="$root.label(component)"
            :headline="headline"
            :attributes="attributes"
            defaultSortBy="name"
            defaultDisplay="cards"
            cards
            smallCards
            gallery="id_die"
            :select="select"
            :selected="selected"
            v-on:select="(emit) => { $emit('select', emit); $emit('close') }"
        >
            <!-- Search ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:search>
                <v-row class="mb-4">
                    <template v-for="(item, i) in attributes">
                        <v-col
                            v-if="item.filter !== undefined && !['side', 'design_' + (language === 'de' ? 'en' : 'de')].includes(i)"
                            :key="i"
                            cols=12 
                            sm=4 
                            md=3
                            class="mb-n4"
                        >
                            <div v-if="i === 'legend'">
                                <v-text-field dense outlined filled clearable
                                    v-model="attributes.legend.filter"
                                    :label="attributes.legend.text"
                                    :prepend-icon="attributes.legend.icon"
                                    append-icon="keyboard"
                                    @click:append="keyboard = !keyboard"
                                ></v-text-field>
                                <v-expand-transition>
                                    <div v-if="keyboard" class="pl-10 mt-n5" style="position: relative;">
                                        <v-card
                                            tile
                                            class="pt-1 pl-1 grey_trip"
                                            style="display: block; position: absolute; z-index: 200"
                                        >
                                            <keyboard                                            
                                                :string="attributes.legend.filter"
                                                layout="el_uc_adv"
                                                v-on:input="(emit) => { attributes.legend.filter = emit }"
                                            ></keyboard>
                                        </v-card>
                                    </div>
                                </v-expand-transition>
                            </div>
                            <v-text-field v-else dense outlined filled clearable
                                v-model="attributes[i].filter"
                                :label="item.text"
                                :prepend-icon="item.icon"
                            ></v-text-field>
                        </v-col>
                    </template>
                    <v-col
                        cols=12 
                        sm=4 
                        md=3
                        class="mb-n4"
                    >
                        <v-select dense outlined filled clearable
                            v-model="attributes.side.filter"
                            :items="sides"
                            :label="attributes.side.text"
                            :prepend-icon="attributes.side.icon"
                        ></v-select>
                    </v-col>
                </v-row>
            </template>

            <!-- Content Cards ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:content-cards-header="slot">
                {{ 'ID&nbsp;' + slot.item.id }}
            </template>

            <template v-slot:content-cards-body="slot">
                <div class="body-2 mb-3">
                    <span class="font-weight-bold" v-html="attributes.name.content(slot.item)"></span>,
                    <span v-html="attributes.side.content(slot.item)"></span>
                </div>
                <!-- Image -->
                <Imager
                    :key="slot.item.id"
                    :item="slot.item"
                    hide_text
                    class="mb-3"
                ></Imager>
                <div
                    class="body-2 font-weight-thin mb-1"
                    v-html="attributes.legend.content(slot.item)"
                ></div>
                <div 
                    class="body-2"
                    v-html="attributes['design_' + language].content(slot.item)"
                ></div>
                <div 
                    v-if="slot.item.comment"
                    class="caption mt-2"
                    v-html="attributes.comment.content(slot.item)"
                ></div>
            </template>

            <!-- Editor ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:editor-header="slot">
                {{ $root.label(component) + '&nbsp;' + slot.item.id }}
            </template>

            <template v-slot:editor-body="slot">
                <v-row>
                    <v-col cols=12 md=6>
                        <v-row>
                            <v-col cols=12 md=7 class="pt-0">
                                <v-text-field dense outlined filled clearable
                                    v-model="slot.item.name"
                                    :label="attributes.name.text"
                                    :prepend-icon="attributes.name.icon"
                                    hint="required"
                                    counter=255
                                ></v-text-field>
                            </v-col>                                
                            <v-col cols=12 md=5 class="pt-0">
                                <v-select dense outlined filled
                                    v-model="slot.item.side"
                                    :items="sides"
                                    :label="attributes.side.text"
                                    :prepend-icon="attributes.side.icon"
                                ></v-select>
                            </v-col>
                        </v-row>
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
                        <InputForeignKey
                            v-for="(key, i) in ['design', 'legend']"
                            :key="key + slot.item.side"
                            :entity="key + 's'" 
                            :label="$root.label((slot.item.side === 1 ? 'reverse' : 'obverse') + ',' + key)" 
                            :icon="attributes['id_' + key].icon" 
                            :sk="key === 'legend' ? 'el_uc_adv' : null"
                            :selected="slot.item['id_' + key]"
                            :conditions="[{ side: slot.item.side }]"
                            :class="i === 0 ? 'mb-3' : ''"
                            style="width: 100%"
                            v-on:select="(emit) => { item['id_' + key] = emit }"
                        ></InputForeignKey>
                    </v-col>
                </v-row>
            </template>

        </simpleDataTemplate>
    </div>
</template>



<script>
import keyboard from './../../modules/keyboard.vue'

export default {
    components: {
        keyboard
    }, 
    data () {
        return { 
            component:          'die',
            entity:             'dies',
            attributes:         {},
            key_name_class:     'caption font-weight-thin text-uppercase mb-1',
            keyboard:           false
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
        sides () {
            return this.$store.state.lists.dropdowns.sides.map((item) => { return { value: item.value, text: this.$root.label(item.text) }})
        },
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
                side: {
                    default: 0,
                    text: this.$root.label('side'),
                    icon: 'tonality',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => { return this.sides.find((row) => row.value == item?.side)?.text } 
                },
                legend: {
                    default: null,
                    text: this.$root.label('legend'),
                    icon: 'translate',
                    header: true,
                    sortable: true,
                    filter: null,
                    content: (item) => { return item?.legend ? item.legend : '--' }
                },
                design_de: {
                    default: null,
                    text: this.$root.label('design'),
                    icon: 'notes',
                    header: this.language === 'de',
                    sortable: this.language === 'de',
                    filter: null,
                    content: (item) => { return item?.design_de ? item.design_de : '--' }
                },
                design_en: {
                    default: null,
                    text: this.$root.label('design'),
                    icon: 'notes',
                    header: this.language != 'de',
                    sortable: this.language != 'de',
                    filter: null,
                    content: (item) => { return item?.design_en ? item.design_en : '--' }
                },
                id_legend: {
                    default: null,
                    text: this.$root.label('legend'),
                    icon: 'translate',
                    clone: true,
                    content: (item) => { return item?.legend ? item.legend : (item?.id_legend ? item.id_legend : '--') }
                },
                id_design: {
                    default: null,
                    text: this.$root.label('design'),
                    icon: 'notes',
                    clone: true,
                    content: (item) => { return item?.['design_' + this.language] ? item['design_' + this.language] : (item?.id_design ? item.id_design : '--') }
                },
                comment: {
                    default: null,
                    text: this.$root.label('comment'),
                    icon: 'short_text',
                    filter: null,
                    clone: false,
                    content: (item) => { return item?.comment ? item.comment : '--' } 
                },
            }
        }
    }
}

</script>