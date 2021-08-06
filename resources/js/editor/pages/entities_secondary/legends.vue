<template>
    <div>
        <generic-entity-template
            sync
            :entity="entity"
            :name="$root.label(component)"
            :attributes="attributes"
            :default-sort-by="'legend.ASC'"
            small-tiles
            gallery="id_legend"
            :dialog="dialog"
            :select="select"
            :selected="selected"
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
                        v-model="attributes.legend.filter"
                        :label="attributes.legend.text"
                        :prepend-icon="attributes.legend.icon"
                        append-icon="keyboard"
                        @click:append="keyboard = !keyboard"
                    />
                    <v-expand-transition>
                        <div
                            v-if="keyboard"
                            class="pl-10 mb-4"
                        >
                            <keyboard
                                :string="attributes.legend.filter"
                                layout="el_uc_adv"
                                v-on:input="(emit) => { attributes.legend.filter = emit }"
                            />
                        </div>
                    </v-expand-transition>

                    <InputForeignKey
                        entity="directions"
                        :label="attributes.direction.text"
                        :icon="attributes.direction.icon"
                        :selected="parseInt(attributes.direction.filter)"
                        style="width: 100%"
                        v-on:select="(emit) => { attributes.direction.filter = emit }"
                    />

                    <v-select dense outlined filled clearable
                        v-for="(key) in ['language', 'role', 'side']"
                        :key="key"
                        v-model="attributes[key].filter"
                        :items="key === 'side' ? search_sides : (key === 'role' ? search_roles : languages)"
                        :label="attributes[key].text"
                        :prepend-icon="attributes[key].icon"
                    />

                    <v-text-field dense outlined filled clearable
                        v-model="attributes.keywords.filter"
                        :label="attributes.keywords.text"
                        :prepend-icon="attributes.keywords.icon"
                        :menu-props="{ offsetY: true }"
                        style="position: relative; z-index: 200"
                    />
            </template>

            <!-- Content Cards ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:search-tile-header="slot">
                {{ 'ID&nbsp;' + slot.item.id }}
            </template>

            <template v-slot:search-tile-body="slot">
                <div class="body-1 mb-3" v-html="attributes.legend.content(slot.item)"></div>
                <div class="d-flex align-start justify-space-between">
                    <div
                        class="body-2"
                        v-html="attributes.role.content(slot.item) + ', ' + attributes.side.content(slot.item) + ', ' + attributes.language.content(slot.item)"
                    ></div>
                    <div v-if="slot.item.image" v-html="attributes.direction.content(slot.item)"></div>
                </div>
                <div v-if="slot.item.legend != slot.item.full_text && slot.item.full_text" class="body-2 mt-3" v-html="attributes.full_text.text + ':<br />' + attributes.comment.content(slot.item)"></div>
                <div v-if="slot.item.comment" class="caption mt-2" v-html="attributes.comment.content(slot.item)"></div>
                <div v-if="slot.item.keywords" class="caption mt-2" v-html="attributes.keywords.content(slot.item)"></div>
            </template>

            <!-- Editor ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:editor="slot">
                <v-row>
                    <v-col cols=12 md=6>
                        <v-row>
                            <v-col cols=12 sm=12 md=6>
                                <!-- Direction -->
                                <InputForeignKey
                                    entity="directions"
                                    :label="attributes.direction.text"
                                    :icon="attributes.direction.icon"
                                    :selected="slot.item.direction"
                                    style="width: 100%"
                                    v-on:select="(emit) => { slot.item.direction = emit }"
                                />
                            </v-col>
                            <v-col cols=12 sm=12 md=6>
                                <!-- Language -->
                                <v-select dense outlined filled
                                    v-model="slot.item.language"
                                    :items="languages"
                                    :label="attributes.language.text"
                                    :prepend-icon="attributes.language.icon"
                                    :menu-props="{ offsetY: true }"
                                />
                            </v-col>
                        </v-row>

                        <!-- Legend -->
                        <v-expand-transition>
                            <v-text-field dense outlined filled clearable
                                v-if="slot.item.language != 'el'"
                                v-model="slot.item.legend"
                                :label="attributes.legend.text"
                                :prepend-icon="attributes.legend.icon"
                                :hint="'required' + (slot.item.language === 'el' ? ' - Legend is Greek: please use the keyboard below.' : '')" persistent-hint
                                class="mb-3"
                                :disabled="slot.item.language === 'el'"
                                counter=255
                            />
                            <div v-else >
                                <div class="mt-n2 mb-1 ml-10 pr-3 d-flex justify-space-between align-end caption marked--text">
                                    <div v-text="'Legend is Greek (keyboard disabled). Click on a position in string to move cursor.'"></div>
                                    <div style="white-space: nowrap">
                                        <span
                                            v-html="'&#9664;'"
                                            :class="cursor.legend !== 0 ? 'invert--text' : ''"
                                            :style="cursor.legend !== 0 ? 'cursor: pointer' : ''"
                                            @click="moveCursor('legend', slot.item.legend, false)"
                                        ></span>
                                        <span v-html="'&nbsp;'"></span>
                                        <span
                                            v-html="'&#9654;'"
                                            :class="cursor.legend !== null ? 'invert--text' : ''"
                                            :style="cursor.legend !== null ? 'cursor: pointer' : ''"
                                            @click="moveCursor('legend', slot.item.legend, true)"
                                        ></span>
                                    </div>
                                </div>
                                <div class="d-flex align-start">
                                    <v-icon class="mt-3 mr-2" v-text="attributes.legend.icon"></v-icon>
                                    <div class="grey_prim pa-3" :style="'width: 100%; border: 1px solid ' + ($vuetify.theme.dark ? '#626262' : '#838383') + ' !important; border-radius: 5px'">
                                        <div class="d-flex flex-wrap body-1">
                                            <template v-if="slot.item.legend">
                                                <div
                                                    v-for="(val, i) in slot.item.legend.split('')"
                                                    :key="val + i"
                                                    v-html="val === ' ' ? '&ensp;' : val"
                                                    style="cursor: pointer;"
                                                    :style="cursor.legend === i && cursor.blink ? ('border-left: 1px solid ' + ($vuetify.theme.dark ? 'white' : 'black') + ';') : 'margin-left: 1px;'"
                                                    @click="setKeyboardCursor('legend', i)"
                                                ></div>
                                            </template>
                                            <div
                                                v-html="'&emsp;'"
                                                style="cursor: pointer;"
                                                :style="cursor.legend === null && cursor.blink ? ('border-left: 1px solid ' + ($vuetify.theme.dark ? 'white' : 'black') + ';') : 'margin-left: 1px;'"
                                                @click="cursor.legend = null"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right caption mb-5 pr-3" style="width: 100%" v-text="(slot.item.legend ? slot.item.legend.length : 0) + ' / ' + 255"></div>
                                <div
                                    v-if="slot.item.language === 'el'"
                                    class="pl-10 mt-n3 mb-7"
                                >
                                    <keyboard
                                        :string="provideString('legend', slot.item.legend)"
                                        layout="el_uc_adv"
                                        v-on:input="(emit) => { slot.item.legend = handleKeyboardOutput('legend', slot.item.legend, emit) }"
                                    ></keyboard>
                                </div>
                            </div>
                        </v-expand-transition>

                        <!-- Full Text -->
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item.full_text"
                            :label="attributes.full_text.text"
                            :prepend-icon="attributes.full_text.icon"
                            counter=255
                        />
                        <v-expand-transition>
                            <div
                                v-if="slot.item.language === 'el'"
                                class="pl-10 mb-4"
                            >
                                <keyboard
                                    :string="slot.item.full_text"
                                    layout="el_uc_adv"
                                    v-on:input="(emit) => { slot.item.full_text = emit }"
                                />
                            </div>
                        </v-expand-transition>
                    </v-col>

                    <!-- Comment -->
                    <v-col cols=12 md=6>
                        <v-row>
                            <v-col cols=12 sm=12 md=6>
                                <!-- Role -->
                                <v-select dense outlined filled
                                    v-model="slot.item.role"
                                    :items="roles"
                                    :label="attributes.role.text"
                                    :prepend-icon="attributes.role.icon"
                                    :menu-props="{ offsetY: true }"
                                />
                            </v-col>
                            <v-col cols=12 sm=12 md=6>
                                <!-- Side -->
                                <v-select dense outlined filled
                                    v-model="slot.item.side"
                                    :items="sides"
                                    :label="attributes.side.text"
                                    :prepend-icon="attributes.side.icon"
                                    :menu-props="{ offsetY: true }"
                                />
                            </v-col>
                        </v-row>
                        <!-- Keywords -->
                        <v-textarea dense outlined filled clearable
                            no-resize
                            rows=2
                            v-model="slot.item.keywords"
                            :label="attributes.keywords.text"
                            :prepend-icon="attributes.keywords.icon"
                            class="mb-3"
                            counter=21845
                        />
                        <!-- Comment -->
                        <v-textarea dense outlined filled clearable
                            no-resize
                            rows=2
                            v-model="slot.item.comment"
                            :label="attributes.comment.text"
                            :prepend-icon="attributes.comment.icon"
                            counter=21845
                        />
                    </v-col>
                </v-row>
            </template>

        </generic-entity-template>
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
            component:          'legend',
            entity:             'legends',
            attributes:         {},
            key_name_class:     'caption font-weight-thin text-uppercase mb-1',
            keyboard:           false,
            cursor: {
                blink: true,
                legend: null
            }
        }
    },

    props: {
        dialog:     { type: Boolean, default: false },
        select:     { type: Boolean, default: false },
        selected:   { type: [Number, String], default: null }
    },

    computed: {
        language () {
            return this.$root.language === 'de' ? 'de' : 'en'
        },

        // Dropdowns
        languages () {
            return this.$store.state.lists.dropdowns.legends_languages.map((item) => { return {
                value: item.value,
                text: this.$root.label(item.text)
            }})
        },
        sides () {
            return this.$store.state.lists.dropdowns.sides.map((item) => { return {
                value: item.value,
                text: this.$root.label(item.text)
            }})
        },
        roles () {
            return this.$store.state.lists.dropdowns.typeCoin.map((item) => { return {
                value: item.value,
                text: this.$root.label(item.text)
            }})
        },
        search_sides () {
            return this.$store.state.lists.dropdowns.sides.map((item) => { return {
                value: this.dialog ? item.value : (item.value + ''),
                text: this.$root.label(item.text)
            }})
        },
        search_roles () {
            return this.$store.state.lists.dropdowns.typeCoin.map((item) => { return {
                value: this.dialog ? item.value : (item.value + ''),
                text: this.$root.label(item.text)
            }})
        }
    },

    created () {
        this.attributes = this.setAttributes()
        const self = this
        setInterval(function() {
            self.cursor.blink = !self.cursor.blink
        }, 500)
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
                legend_sort: {
                    default: null,
                    text: this.$root.label('legend'),
                    icon: 'translate',
                    content: (item) => item?.legend_sort ?? '--'
                },
                legend: {
                    default: null,
                    text: this.$root.label('legend'),
                    icon: 'translate',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => item?.legend ? ('<span class="font-weight-thin">' + item.legend + '</span>') : '--'
                },
                full_text: {
                    default: null,
                    text: this.$root.label('legend_full_text'),
                    icon: 'short_text',
                    clone: false,
                    content: (item) => item?.full_text ?? '--'
                },
                direction:   {
                    default: null,
                    text: this.$root.label('direction'),
                    icon: 'text_rotation_angleup',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => item?.direction ? (item.image ? this.$handlers.format.image_tile(item.image, 30) : item.direction) : '--'
                },
                language: {
                    default: 'el',
                    text: this.$root.label('language'),
                    icon: 'language',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => this.languages.find((row) => row.value == item.language.toLowerCase())?.text ?? '--'
                },
                role: {
                    default: 0,
                    text: this.$root.label('kind'),
                    icon: 'help_outline',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => this.roles.find((row) => row.value == item?.role)?.text ?? '--'
                },
                side: {
                    default: 0,
                    text: this.$root.label('side'),
                    icon: 'tonality',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => this.sides.find((row) => row.value == item?.side)?.text ?? '--'
                },
                keywords: {
                    default: null,
                    text: this.$root.label('keywords'),
                    icon: 'format_list_bulleted',
                    header: true,
                    filter: null,
                    clone: true,
                    content: (item) => item?.keywords ?? '--'
                },
                comment: {
                    default: null,
                    text: this.$root.label('comment'),
                    icon: 'notes',
                    clone: false,
                    content: (item) => item?.comment ?? '--'
                }
            }
        },

        handleKeyboardOutput (key, state, input) {
            if (this.cursor[key] === null || (!input && (this.cursor[key] === null || this.cursor[key] < 1)) || !state) {
                //console.log('state:' + state)
                //console.log('input:' + input)
                this.cursor[key] = null
                return input
            }
            else {
                const cursor = this.cursor[key]
                //console.log('state:' + state)
                //console.log('input:' + input)
                this.cursor[key] = cursor + (input === state.slice(0, (cursor - 1)) ? -1 : (input.slice(-5) === '[...]' ? 5 : 1))
                return input + state.slice(cursor)
            }
        },

        setKeyboardCursor (key, i) {
            this.cursor.legend = i
        },

        provideString (key, input) {
            if (this.cursor[key] === null || !input) return input
            return input.slice(0, this.cursor[key])
        },

        moveCursor (key, state, forward) {
            if (state) {
                const length = state.split('').length
                if (this.cursor[key] === null) {
                    this.cursor[key] = forward ? null : length -1
                }
                else if (forward) {
                    this.cursor[key] = this.cursor[key] < (length - 1) ? (this.cursor[key] + 1) : null
                }
                else if (this.cursor[key] > 0 && !forward) {
                    this.cursor[key] = this.cursor[key] - 1
                }
            }
        }
    }
}

</script>
