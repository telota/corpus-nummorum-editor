<template>
<div>
    <generic-entity-template
        DISABLED-key="language"
        :entity="entity"
        :attributes="attributes"
        defaultSortBy="combination.ASC"
        smallTiles
        gallery="id_monogram"
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
            <template v-for="(item, i) in attributes">
                <v-text-field dense outlined filled clearable
                    v-if="item.filter !== undefined && i != 'combination'"
                    :key="i"
                    v-model="attributes[i].filter"
                    :label="item.text"
                    :prepend-icon="item.icon"
                ></v-text-field>
            </template>
            <v-text-field dense outlined filled clearable
                v-model="attributes.combination.filter"
                :label="attributes.combination.text"
                :prepend-icon="attributes.combination.icon"
                append-icon="keyboard"
                v-on:input="(string) => { attributes.combination.filter = string ? string.replace(' ', '').split('').join(' ').trim() : null }"
                @click:append="keyboard = !keyboard"
            />
            <v-expand-transition>
                <div v-if="keyboard" class="pl-10 mt-n5" style="position: relative;">
                    <v-card
                        tile
                        class="pt-1 pl-1 grey_trip"
                        style="display: block; position: absolute; z-index: 200"
                    >
                        <keyboard
                            :string="attributes.combination.filter"
                            layout="el_uc"
                            v-on:input="(emit) => { attributes.combination.filter = emit.replace(' ', '').split('').join(' ').trim() }"
                        ></keyboard>
                    </v-card>
                </div>
            </v-expand-transition>
        </template>

        <!-- Search Tiles ---------------------------------------------------------------------------------------------------- -->
        <template v-slot:search-tile-header="slot">
            {{ 'ID&nbsp;' + slot.item.id }}
        </template>

        <template v-slot:search-tile-body="slot">
            <!-- Image -->
            <Imager
                :key="slot.item.id"
                :item="slot.item"
                color_background="white"
                hide_text
            ></Imager>
            <div class="body-2 mt-3">
                <span class="font-weight-bold" v-html="attributes.name.content(slot.item)"></span>,
                <span v-html="attributes.combination.content(slot.item)"></span>
            </div>
        </template>

        <!-- Editor ---------------------------------------------------------------------------------------------------- -->
        <template v-slot:editor="slot">
            <v-row>
                <!-- Combination -->
                <v-col cols=12 md=6 lg=4>
                    <v-text-field dense outlined filled clearable
                        v-model="slot.item.combination"
                        :label="attributes.combination.text"
                        :prepend-icon="attributes.combination.icon"
                        :hint="'required'" persistent-hint
                        disabled
                        counter=255
                    />
                    <div class="pl-10 mb-5">
                        <keyboard
                            :string="slot.item.combination"
                            layout="el_uc"
                            v-on:input="(emit) => { slot.item.combination = emit }"
                        />
                    </div>
                </v-col>

                <!-- Name -->
                <v-col cols=12 md=6 lg=4>
                    <v-text-field dense outlined filled clearable
                        v-model="slot.item.name"
                        :label="attributes.name.text"
                        :prepend-icon="attributes.name.icon"
                        hint="required"
                        class="mb-3"
                        counter=255
                    />
                </v-col>

                <!-- Image -->
                <v-col cols=12 md=6 lg=4>
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
                                    @click="file_browser = true"
                                >
                                    <v-icon>folder_open</v-icon>
                                </v-btn>
                            </template>
                            <span>Open File Browser</span>
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

            <!-- Files Browser -->
            <simpleSelectDialog
                :active="file_browser"
                icon="folder_open"
                text="File Browser"
                v-on:close="file_browser = false; file_select = null;"
            >
                <template v-slot:content>
                    <files
                        :prop_item="{ id: slot.item.image, parent: 'Monograms' }"
                        class="mt-n2 mb-5"
                        v-on:ChildEmit="(emit) => { file_select =  emit.id }"
                        ></files>
                    <v-btn tile block small color="blue_prim"
                        v-text="file_select? (file_select === slot.item.image ? (file_select.split('/').pop() + ' is alreadey selected') : ('Select ' + file_select.split('/').pop())) : ('No File selected, yet')"
                        :disabled="!file_select || file_select === slot.item.image"
                        class="ml-n6"
                        style="position: absolute; bottom: 0"
                        @click="slot.item.image = file_select; file_browser = false; file_select = null;"
                    ></v-btn>
                </template>
            </simpleSelectDialog>

            <!-- Upload -->
            <upload
                :prop_active="upload"
                prop_target="storage/Monograms"
                prop_key="image"
                v-on:close="upload = false"
                v-on:ChildEmit="(emit) => {
                    slot.item.image = emit.url
                    upload = false
                }"
            ></upload>
        </template>

        <!-- Gallery Linking -->
        <template v-slot:gallery-link="slot">
            <v-select dense outlined filled
                v-model="slot.link.item.side"
                :items="sides"
                :label="$root.label('side')"
                prepend-icon="tonality"
                style="width: 100%"
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
            component:          'monogram',
            entity:             'monograms',
            attributes:         {},
            key_name_class:     'caption font-weight-thin text-uppercase mb-1',
            keyboard:           false,

            upload:             false,
            file_browser:       false,
            file_select:        null,
        }
    },

    props: {
        dialog:     { type: Boolean, default: false },
        select:     { type: Boolean, default: false },
        selected:   { type: [Number, String], default: 0 }
    },

    computed: {
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
                    content: (item) => { return item?.id ? item.id : '--' }
                },
                image: {
                    default: null,
                    text: this.$root.label('image'),
                    icon: 'image',
                    header: true,
                    clone: false,
                    content: (item) => { return item?.image ? this.$handlers.format.image_tile(item.image, 30) : '--' }
                },
                combination: {
                    default: null,
                    text: this.$root.label('combination'),
                    icon: 'functions',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => { return item?.combination ? item.combination : '--' }
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
                }
            }
        }
    }
}

</script>
