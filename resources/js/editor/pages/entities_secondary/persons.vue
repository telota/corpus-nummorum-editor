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
            gallery="id_person"
            linking
            :select="select"
            :selected="selected"
            v-on:select="(emit) => { $emit('select', emit); $emit('close') }"
        >
            <!-- Search ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:search>
                <v-row class="mb-4">
                    <template v-for="(item, key) in attributes">
                        <v-col
                            v-if="key === 'is_authority'"
                            :key="key"
                            cols=12 
                            sm=4 
                            md=3
                            class="mb-n5"
                        >
                            <v-select dense outlined filled clearable
                                v-model="attributes[key].filter"
                                :items="yesNo"
                                :label="attributes[key].text"
                                :prepend-icon="attributes[key].icon"
                            ></v-select>
                        </v-col>
                        <v-col
                            v-else-if="key === 'alias'"
                            :key="key"
                            cols=12 
                            sm=4 
                            md=3
                            class="mb-n5"
                        >
                            <v-text-field dense outlined filled clearable
                                v-model="attributes[key].filter"
                                :label="attributes[key].text"
                                :prepend-icon="attributes[key].icon"
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
                                            :string="attributes[key].filter"
                                            layout="el_uc"
                                            v-on:input="(emit) => { attributes[key].filter = emit }"
                                        ></keyboard>
                                    </v-card>
                                </div>
                            </v-expand-transition>
                        </v-col>
                        <v-col
                            v-else-if="item.filter !== undefined"
                            :key="key"
                            cols=12 
                            sm=4 
                            md=3
                            class="mb-n5"
                        >
                            <v-text-field dense outlined filled clearable
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
                    <div class="caption" v-html="attributes.nomisma_name.content(slot.item)"></div>
                </div>
                <div class="body-2 mb-3">
                    <span v-html="slot.item.is_authority ? '(&#9733;)' : ''"></span>
                    <span v-html="attributes.position.content(slot.item)"></span><br />
                    <span class="caption" v-html="attributes.nomisma_role.content(slot.item)"></span>
                </div>
                <div
                    class="body-2 mb-3" 
                    v-html="attributes.active_time.content(slot.item)"
                ></div>
                <div class="body-2">
                    <div v-if="slot.item.caesar_start" class="caption" v-html="'Caesar: ' + attributes.caesar_start.content(slot.item)"></div>
                    <div v-if="slot.item.augustus_start" class="caption" v-html="'Augustus: ' + attributes.augustus_start.content(slot.item)"></div>
                </div>
            </template>

            <!-- Editor ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:editor-header="slot">
                {{ $root.label(component) + '&nbsp;' + slot.item.id }}
            </template>

            <template v-slot:editor-body="slot">
                <div>
                    <v-row>
                        <v-col cols=12 md=6>
                            <!-- Name -->
                            <v-text-field dense outlined filled clearable
                                v-model="slot.item.name"
                                :label="attributes.name.text"
                                :prepend-icon="attributes.name.icon"
                                hint="required" persistent-hint
                                counter=255
                                class="mb-3"
                            ></v-text-field>
                            <!-- Nomisma -->
                            <v-text-field dense outlined filled clearable
                                v-model="slot.item.nomisma_name"
                                :label="attributes.nomisma_name.text"
                                :prepend-icon="attributes.nomisma_name.icon"
                                counter=255
                                class="mb-3"
                                @click:prepend="$root.openInNewTab((slot.item.nomisma_name.slice(0, 4) != 'http' ? $handlers.constant.url.nomisma : '') + slot.item.nomisma_name)"
                            ></v-text-field>
                            <!-- Alias -->
                            <v-text-field dense outlined filled clearable
                                v-model="slot.item.alias"
                                :label="attributes.alias.text"
                                :prepend-icon="attributes.alias.icon"
                                append-icon="keyboard"
                                counter=255
                                @click:append="keyboard_e = !keyboard_e"
                            ></v-text-field>
                            <v-expand-transition>
                                <div v-if="keyboard_e" class="pl-10 mb-1">
                                    <keyboard
                                        :string="slot.item.alias"
                                        layout="el_uc"
                                        v-on:input="(emit) => { slot.item.alias = emit }"
                                    ></keyboard>
                                </div>
                            </v-expand-transition>
                            <!-- Active Time -->
                            <v-row>
                                <v-col cols=6 class="pb-0">
                                    <v-text-field dense outlined filled clearable
                                        v-model="slot.item.active_time"
                                        :label="attributes.active_time.text"
                                        :prepend-icon="attributes.active_time.icon"
                                    ></v-text-field>
                                </v-col>
                                <v-col 
                                    v-for="key in ['date_start', 'date_end']"
                                    :key="key"
                                    cols=3
                                    class="pb-0"
                                >
                                    <v-text-field dense outlined filled clearable
                                        v-model="slot.item[key]"
                                        :label="attributes[key].text"
                                        :prepend-icon="attributes[key].icon"
                                        :rules="[$handlers.rules.date]"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                            <!-- Caesar and Augustus -->
                            <v-row v-for="key in ['caesar', 'augustus']" :key="key">
                                <v-col 
                                    v-for="add in ['uncertain', 'start']"
                                    :key="add"
                                    :cols="add === 'uncertain' ? 6 : 3"
                                    class="pb-0"
                                >
                                    <v-text-field dense outlined filled clearable
                                        v-model="slot.item[key + '_' + add]"
                                        :label="attributes[key + '_' + add].text"
                                        :prepend-icon="attributes[key + '_' + add].icon"
                                        :rules="add === 'start' ? [$handlers.rules.date] : []"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                        </v-col>
                        <v-col cols=12 md=6>
                            <!-- Position -->
                            <v-text-field dense outlined filled clearable
                                v-model="slot.item.position"
                                :label="attributes.position.text"
                                :prepend-icon="attributes.position.icon"
                                class="mb-3"
                                counter=255
                            ></v-text-field>
                            <!-- Nomisma -->
                            <v-text-field dense outlined filled clearable
                                v-model="slot.item.nomisma_role"
                                :label="attributes.nomisma_role.text"
                                :prepend-icon="attributes.nomisma_role.icon"
                                class="mb-3"
                                counter=255
                                @click:prepend="$root.openInNewTab((slot.item.nomisma_role.slice(0, 4) != 'http' ? $handlers.constant.url.nomisma : '') + slot.item.nomisma_role)"
                            ></v-text-field>
                            <!-- Authority -->
                            <v-select dense outlined filled
                                v-model="slot.item.is_authority"
                                :items="yesNo"
                                :label="attributes.is_authority.text"
                                :prepend-icon="attributes.is_authority.icon"
                                class="mb-3"
                                style="width: 50%;"
                            ></v-select> 
                            <!-- Definition -->
                            <v-textarea dense outlined filled clearable 
                                no-resize
                                rows=1
                                v-model="slot.item.definition"
                                :label="attributes.definition.text"
                                :prepend-icon="attributes.definition.icon"
                                class="mb-3"
                                counter=21845
                            ></v-textarea>
                            <!-- Comment -->
                            <v-textarea dense outlined filled clearable 
                                no-resize
                                rows=3
                                v-model="slot.item.comment"
                                :label="attributes.comment.text"
                                :prepend-icon="attributes.comment.icon"
                                counter=21845
                            ></v-textarea>
                        </v-col>
                    </v-row>
                </div>
            </template>

            <!-- Gallery Linking -->
            <template v-slot:gallery-link="slot">            
                <InputForeignKey
                    entity="functions" 
                    :label="$root.label('function')" 
                    icon="event_seat"                    
                    :selected="slot.link.item.function"
                    style="width: 100%"
                    v-on:select="(emit) => { slot.link.item.function = emit }"
                ></InputForeignKey>
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
            component:          'person',
            entity:             'persons',
            attributes:         {},
            key_name_class:     'caption font-weight-thin text-uppercase mb-1',
            keyboard:           false,
            keyboard_e:         false,
            
            upload:             false,
            file_browser:       false,
            file_select:        null,
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
    },

    /*watch: {
        language: function () { this.attributes = this.setAttributes() }
    },*/

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
                    icon: 'label_important',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: false,
                    content: (item) => { return item?.alias ? item.alias : '--' }
                },
                position: {
                    default: null,
                    text: this.$root.label('position'),
                    icon: 'stars',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => { return item?.position ? item.position : '--' }
                },
                is_authority: {
                    default: 0,
                    text: this.$root.label('ruler'),
                    icon: 'star',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => { return this.yesNo.find((row) => row.value == item.is_authority)?.text }
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
                active_time: {
                    default: null,
                    text: this.$root.label('active_time'),
                    icon: 'settings_ethernet',
                    clone: false,
                    content: (item) => { return item?.active_time ? item.active_time : '--' }
                },
                nomisma_name: {
                    default: null,
                    text: 'Nomisma ID',
                    icon: 'monetization_on',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: false,
                    content: (item) => { return this.$handlers.format.nomisma_link(item.nomisma_name) } 
                },
                nomisma_role: {
                    default: null,
                    text: 'Nomisma ID (Role)',
                    icon: 'monetization_on',
                    clone: false,
                    content: (item) => { return this.$handlers.format.nomisma_link(item.nomisma_role) } 
                },
                definition: {
                    default: null,
                    text: this.$root.label('definition'),
                    icon: 'info',
                    clone: false,
                    content: (item) => { return item?.definition ? item.definition : '--' }
                },
                comment: {
                    default: null,
                    text: this.$root.label('comment'),
                    icon: 'notes',
                    clone: false,
                    content: (item) => { return item?.comment ? item.comment : '--' }
                },
                caesar_start: {
                    default: null,
                    text: this.$root.label('caesar_start'),
                    icon: 'play_circle_outline',
                    clone: false,
                    content: (item) => { return item?.caesar_start ? this.$handlers.format.year(this.language, item.caesar_start) : '--' }
                },
                caesar_uncertain: {
                    default: null,
                    text: this.$root.label('caesar_string'),
                    icon: 'notes',
                    clone: false,
                    content: (item) => { return item?.caesar_uncertain ? item.caesar_uncertain : '--' }
                },
                augustus_start: {
                    default: null,
                    text: this.$root.label('augustus_start'),
                    icon: 'play_circle_outline',
                    clone: false,
                    content: (item) => { return item?.augustus_start ? this.$handlers.format.year(this.language, item.augustus_start) : '--' }
                },
                augustus_uncertain: {
                    default: null,
                    text: this.$root.label('caesar_string'),
                    icon: 'notes',
                    clone: false,
                    content: (item) => { return item?.augustus_uncertain ? item.augustus_uncertain : '--' }
                },
            }
        }
    }
}

</script>