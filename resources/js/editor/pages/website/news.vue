<template>
    <div>
        <generic-entity-template
            :key="language"
            :entity="entity"
            :attributes="attributes"
            default-sort-by="release.DESC"
            default-layout="tiles"
            :dialog="dialog"
            :select="select"
            :selected="selected"
            v-on:select="(emit) => { $emit('select', emit) }"
            v-on:close="$emit('close')"
            v-on:setFilter="(emit) => { this.attributes[emit.key].filter = emit.value }"
        >
            <!-- Filter ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:filters>
                <div style="position: relative">
                    <v-menu
                        offset-y
                        :close-on-content-click="false"
                        transition="scale-transition"
                    >
                        <template v-slot:activator="{ on }">
                            <v-text-field dense outlined filled clearable
                                v-model="attributes.release.filter"
                                :label="attributes.release.text"
                                :prepend-icon="attributes.release.icon"
                                readonly
                                v-on="on"
                            />
                        </template>
                        <v-card tile>
                            <v-date-picker v-model="attributes.release.filter" landscape />
                        </v-card>
                    </v-menu>
                </div>

                <template v-for="(item, i) in attributes">
                    <v-text-field dense outlined filled clearable
                        v-if="item.filter !== undefined && i !== 'release'"
                        :key="i"
                        v-model="attributes[i].filter"
                        :label="item.text"
                        :prepend-icon="item.icon"
                    />
                </template>
            </template>

            <!-- Content Cards ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:search-tile-header="slot">
                <div v-html="attributes.release.content(slot.item)" class="mr-5"></div>
            </template>

            <template v-slot:search-tile-body="slot">
                <adv-img
                    :src="slot.item.image"
                    square
                    contain
                    background="white"
                />
                <div class="body-1 font-weight-bold mb-3 mt-3" v-html="attributes['header_' + language].content(slot.item)"></div>
                <div class="body-2 mb-3" v-html="attributes['teaser_' + language].content(slot.item)"></div>
                <div class="body-2 text-right" v-html="attributes.creator.content(slot.item)"></div>
            </template>

            <!-- Editor ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:editor="slot">
                <v-row>
                    <!-- Release Date -->
                    <v-col cols="12" md="6" lg="3">
                        <v-menu offset-y
                            :close-on-content-click="false"
                            transition="scale-transition"
                        >
                            <template v-slot:activator="{ on }">
                                <v-text-field dense outlined filled clearable
                                    v-model="slot.item.release"
                                    :label="attributes.release.text"
                                    :prepend-icon="attributes.release.icon"
                                    v-on="on"
                                />
                            </template>
                            <v-card tile>
                                <v-date-picker v-model="slot.item.release" landscape />
                            </v-card>
                        </v-menu>
                    </v-col>

                    <!-- Creator -->
                    <v-col cols="12" md="6" lg="3">
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item.creator"
                            :label="attributes.creator.text"
                            prepend-icon="person"
                            hint="not required"
                            counter=255
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

                <v-row>
                    <!-- Description -->
                    <v-col
                        v-for="(l) in ['de', 'en']"
                        :key="l"
                        cols="12" sm="12" md="6"
                    >
                        <subheader :label="l.toUpperCase()" class="mb-5" />
                        <!-- Date -->
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item['date_' + l]"
                            :label="attributes['date_' + l].text"
                        />
                        <!-- Header -->
                        <v-textarea dense outlined filled no-resize
                            v-model="slot.item['header_' + l]"
                            :label="attributes['header_' + l].text"
                            rows="2"
                            hint="visible on preview: yes"
                            counter=255
                        />
                        <!-- Teaser -->
                        <v-textarea dense outlined filled no-resize
                            v-model="slot.item['teaser_' + l]"
                            :label="attributes['teaser_' + l].text"
                            rows="5"
                            hint="visible on preview: yes"
                            counter=21845
                            class="mt-5"
                        />
                        <!-- Text -->
                        <wysiwyg
                            v-model="slot.item['text_' + l]"
                            :label="attributes['text_' + l].text"
                            :counter="21845"
                            filled
                            outlined
                            :unique="l"
                        />
                    </v-col>

                    <!-- Links-->
                    <v-col cols=12>
                        <v-divider class="mb-5" />
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item.links_value"
                            label="Link"
                            prepend-icon="insert_link"
                            counter=255
                            :rules="[$handlers.rules.link]"
                        />
                    </v-col>

                    <v-col v-for="(language) in ['de', 'en']" :key="language + 'l'" cols="12" md="6">
                        <!-- Links-->
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item['links_text_' + language]"
                            :label="'Link Mask (' + language.toUpperCase() + ')'"
                            prepend-icon="insert_link"
                            counter=255
                            class="mt-n3"
                        />
                    </v-col>

                </v-row>

                <!-- FileManager -->
                <file-manager
                    v-if="fileManager"
                    dialog
                    select
                    :selected="slot.item.image ? slot.item.image : directory"
                    v-on:select="(emit) => { slot.item.image = emit ? emit : null }"
                    v-on:close="fileManager = false"
                />

                <!-- Upload -->
                <upload
                    :show="upload"
                    :directory="directory"
                    v-on:close="upload = false"
                    v-on:upload="(emit) => { slot.item.image = emit.path ? emit.path : null }"
                />
            </template>

        </generic-entity-template>
    </div>
</template>



<script>

export default {
    data () {
        return {
            component:          'News',
            entity:             'news',
            attributes:         {},
            key_name_class:     'caption font-weight-thin text-uppercase mb-1',

            upload:             false,
            fileManager:        false,
            directory:          'news',
            htmleditor:         false
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

        release () {
            const date = new Date ()
            const date_month = '0' + (date.getMonth() + 1)
            const date_day = '0' + date.getDate()
            return date.getFullYear() + '-' + date_month.slice(-2) + '-' + date_day.slice(-2)
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
                    content: (item) => item?.id ?? '--'
                },
                release: {
                    default: this.release,
                    text: this.$root.label('release'),
                    icon: 'event',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: false,
                    content: (item) => item?.release ? this.$handlers.format.date(this.language, item.release) : '--'
                },
                creator: {
                    default: this.$root.user.fullname,
                    text: this.$root.label('creator'),
                    icon: 'person',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => item?.creator ?? '--'
                },
                image: {
                    default: null,
                    text: this.$root.label('image'),
                    icon: 'label',
                    clone: false,
                    content: (item) => item?.image ?? '--'
                },
                header_de: {
                    default: null,
                    text: this.$root.label('header') + ' (DE)',
                    icon: 'short_text',
                    header: this.language === 'de' ? true : false,
                    filter: this.language === 'de' ? null : undefined,
                    clone: false,
                    content: (item) => item?.header_de ?? '--'
                },
                header_en: {
                    default: null,
                    text: this.$root.label('header') + ' (EN)',
                    icon: 'short_text',
                    header: this.language === 'en' ? true : false,
                    filter: this.language === 'en' ? null : undefined,
                    clone: false,
                    content: (item) => item?.header_en ?? '--'
                },
                teaser_de: {
                    default: null,
                    text: this.$root.label('teaser') + ' (DE)',
                    icon: 'short_text',
                    clone: false,
                    content: (item) => item?.teaser_de ?? '--'
                },
                teaser_en: {
                    default: null,
                    text: this.$root.label('teaser') + ' (EN)',
                    icon: 'short_text',
                    clone: false,
                    content: (item) => item?.teaser_en ?? '--'
                },
                text_de: {
                    default: null,
                    text: this.$root.label('text') + ' (DE)',
                    icon: 'short_text',
                    clone: false,
                    content: (item) => item?.text_de ?? '--'
                },
                text_en: {
                    default: null,
                    text: this.$root.label('text') + ' (EN)',
                    icon: 'short_text',
                    clone: false,
                    content: (item) => item?.text_en ?? '--'
                },
                date_de: {
                    default: null,
                    text: this.$root.label('date') + ' (DE)',
                    icon: 'event',
                    clone: false,
                    content: (item) => item?.date_de ?? '--'
                },
                date_en: {
                    default: null,
                    text: this.$root.label('date') + ' (EN)',
                    icon: 'event',
                    clone: false,
                    content: (item) => item?.date_en ?? '--'
                },
                links_text_de: {
                    default: null,
                    text: this.$root.label('link_text') + ' (DE)',
                    icon: 'short_text',
                    clone: false,
                    content: (item) => item?.links_text_de ?? '--'
                },
                links_text_en: {
                    default: null,
                    text: this.$root.label('link_text') + ' (EN)',
                    icon: 'short_text',
                    clone: false,
                    content: (item) => item?.links_text_en ?? '--'
                },
                links_value: {
                    default: null,
                    text: this.$root.label('Link') + ' (EN)',
                    icon: 'short_text',
                    clone: false,
                    content: (item) => item?.links_value ?? '--'
                }
            }
        }
    }
}

</script>
