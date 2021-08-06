<template>
    <div>
        <generic-entity-template
            :entity="entity"
            :attributes="attributes"
            :default-sort-by="'name_' + language + '.ASC'"
            small-tiles
            gallery="id_symbol"
            linking
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
                <div
                    v-for="key in ['name_de', 'name_en']"
                    :key="key"
                >
                    <v-text-field dense outlined filled clearable
                        v-model="attributes[key].filter"
                        :label="attributes[key].text"
                        :prepend-icon="attributes[key].icon"
                        append-icon="keyboard"
                        @click:append="keyboard = !keyboard"
                    />
                    <v-expand-transition>
                        <div v-if="keyboard" class="pl-10 mt-n5">
                            <keyboard
                                :string="attributes[key].filter"
                                layout="el_uc"
                                v-on:input="(emit) => { attributes[key].filter = emit }"
                            />
                        </div>
                    </v-expand-transition>
                </div>
            </template>

            <!-- Content Cards ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:search-tile-header="slot">
            {{ 'ID&nbsp;' + slot.item.id }}
        </template>

        <template v-slot:search-tile-body="slot">
                <!-- Image -->
                <adv-img
                    :src="slot.item.image"
                    square
                    contain
                    background="white"
                />

                <div class="body-2 mt-3" v-html="'(DE)&ensp;' + attributes.name_de.content(slot.item)" />
                <div class="body-2 mt-3" v-html="'(EN)&ensp;' + attributes.name_en.content(slot.item)" />
                <div v-if="slot.item.comment" class="caption mt-3" v-html="attributes.comment.content(slot.item)" />
            </template>

            <!-- Editor ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:editor="slot">
                <v-row>
                    <!-- Name -->
                    <v-col
                        v-for="key in ['name_de', 'name_en']"
                        :key="key"
                        cols=12
                        md=6
                    >
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item[key]"
                            :label="attributes[key].text"
                            :prepend-icon="attributes[key].icon"
                            :hint="'required'" persistent-hint
                            counter=255
                        />
                        <div class="pl-10">
                            <keyboard
                                :string="slot.item[key]"
                                layout="el_uc"
                                v-on:input="(emit) => { slot.item[key] = emit }"
                            />
                        </div>
                    </v-col>

                    <!-- Comment -->
                    <v-col cols=12 md=6>
                        <v-textarea dense outlined filled clearable
                            no-resize
                            rows=2
                            v-model="slot.item.comment"
                            :label="attributes.comment.text"
                            :prepend-icon="attributes.comment.icon"
                            counter=21845
                        />
                    </v-col>

                    <!-- Image -->
                    <v-col cols=12 md=6>
                        <div class="d-flex">
                            <v-text-field dense outlined filled clearable
                                v-model="slot.item.image"
                                :label="attributes.image.text"
                                :prepend-icon="attributes.image.icon"
                                disabled
                                counter=255
                            />

                            <!-- Files -->
                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-btn
                                        v-on="on"
                                        icon
                                        class="ml-1 mr-1"
                                        @click="fileManager = true"
                                    >
                                        <v-icon>folder_open</v-icon>
                                    </v-btn>
                                </template>
                                <span>Open File Manager</span>
                            </v-tooltip>
                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-btn
                                        v-on="on"
                                        icon
                                        @click="upload = true"
                                    >
                                        <v-icon>cloud_upload</v-icon>
                                    </v-btn>
                                </template>
                                <span>Direct Upload</span>
                            </v-tooltip>
                        </diV>
                    </v-col>
                </v-row>

                <!-- FileManager -->
                <file-manager
                    v-if="fileManager"
                    dialog
                    select
                    :selected="slot.item.image ? slot.item.image : 'Symbols'"
                    v-on:select="(emit) => { slot.item.image = emit ? emit : null }"
                    v-on:close="fileManager = false"
                />

                <!-- Upload -->
                <upload
                    :show="upload"
                    directory="Symbols"
                    v-on:close="upload = false"
                    v-on:upload="(emit) => { slot.item.image = emit.path ? emit.path : null }"
                />
            </template>

            <!-- Gallery Linking -->
            <template v-slot:gallery-link="slot">
                <v-select dense outlined filled
                    v-model="slot.link.item.side"
                    :items="sides"
                    :label="$root.label('side')"
                    prepend-icon="tonality"
                    style="width: 100%"
                    :menu-props="{ offsetY: true }"
                />
                <InputForeignKey
                    entity="positions"
                    label="Position"
                    icon="motion_photos_on"
                    :selected="slot.link.item.position"
                    style="width: 100%"
                    v-on:select="(emit) => { slot.link.item.position = emit }"
                />
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
            component:          'symbol',
            entity:             'symbols',
            attributes:         {},
            key_name_class:     'caption font-weight-thin text-uppercase mb-1',
            keyboard:           false,

            upload:             false,
            fileManager:        false,
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
        },

        sides () {
            const self = this
            const sides = []
            this.$store.state.lists.dropdowns.sides.forEach((item) => {
                if(item.value !== 2) {
                    sides.push({ value: item.value, text: self.$root.label(item.text) })
                }
            })
            return sides
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
                name_de: {
                    default: null,
                    text: this.$root.label('name') + ' (DE)',
                    icon: 'label',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: false,
                    content: (item) => item?.name_de ?? '--'
                },
                name_en: {
                    default: null,
                    text: this.$root.label('name') + ' (EN)',
                    icon: 'label',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: false,
                    content: (item) => item?.name_en ?? '--'
                },
                image: {
                    default: null,
                    text: this.$root.label('image'),
                    icon: 'camera_alt',
                    header: true,
                    clone: false,
                    content: (item) => item?.image ? this.$handlers.format.image_tile(item.image, 30) : '--'
                },
                comment: {
                    default: null,
                    text: this.$root.label('comment'),
                    icon: 'notes',
                    clone: false,
                    content: (item) => item?.comment ?? '--'
                }
            }
        }
    }
}

</script>
