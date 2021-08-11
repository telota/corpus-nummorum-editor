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
                                hint="Release always on 1st of month" persistent-hint
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
                <div class="d-flex body-2">
                    <div v-html="attributes.release.content(slot.item)" />
                    <div v-html="'&ensp;|&ensp;ID&nbsp;' + attributes.id.content(slot.item) + ','" class="mr-1" />
                    <div v-html="attributes.id_cn.content(slot.item)" />
                </div>
            </template>

            <template v-slot:search-tile-body="slot">
                <adv-img
                    :src="slot.item.image"
                    square
                    contain
                    background="white"
                />
                <div class="body-1 font-weight-bold mb-3 mt-3" v-html="attributes['header_' + language].content(slot.item)" />
                <!--<div class="body-2 mb-3" v-html="attributes['teaser_' + language].content(slot.item)" />-->
                <div class="body-2 text-right" v-html="attributes.presented_by.content(slot.item)" />
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
                                    readonly
                                    hint="Release always on 1st of month" persistent-hint
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
                            v-model="slot.item.presented_by"
                            :label="attributes.presented_by.text"
                            prepend-icon="person"
                            hint="not required"
                            counter=255
                        />
                    </v-col>

                    <!-- Coin/Type? -->
                    <v-col cols="12" md="6" lg="3">
                        <div class="d-flex">
                            <coins-types-selector
                                :entity="slot.item.is_type ? 'types' : 'coins'"
                                :selected="slot.item.id_cn"
                                v-on:select="(emit) => { slot.item.id_cn = emit }"
                            />

                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-btn v-on="on" icon @click="slot.item.is_type = !slot.item.is_type" class="mr-1 ml-2">
                                        <v-icon>swap_horiz</v-icon>
                                    </v-btn>
                                </template>
                                <span>Switch to {{ slot.item.is_type ? 'Coin' : 'Type' }} ID</span>
                            </v-tooltip>

                            <v-tooltip bottom >
                                <template v-slot:activator="{ on }">
                                    <a :href="(slot.item.is_type ? 'https://www.corpus-nummorum.eu/types/' : 'http://www.corpus-nummorum.eu/CN_') + slot.item.id_cn" target="_blank">
                                        <v-btn v-on="on" icon :disabled="!slot.item.id_cn">
                                            <v-icon>link</v-icon>
                                        </v-btn>
                                    </a>
                                </template>
                                <span>open linked {{ !slot.item.is_type ? 'coin' : 'type' }} in new tab</span>
                            </v-tooltip>
                        </div>
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
import coinsTypesSelector from '../entities_primary/modules/coinsTypesSelector.vue';
export default {
  components: { coinsTypesSelector },
    data () {
        return {
            component:          'Coin of the Month',
            entity:             'coinofthemonth',
            attributes:         {},
            key_name_class:     'caption font-weight-thin text-uppercase mb-1',

            upload:             false,
            fileManager:        false,
            directory:          'CoinOfMonth',
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
            const date = new Date ();
            const date_month = date.getMonth() < 11 ? (date.getMonth() + 2) : 1;
            const date_year = date_month != 1 ? date.getFullYear() : (date.getFullYear() + 1);
            return date_year + '-' + (date_month > 9 ? date_month : ('0'+date_month)) + '-01';
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
                presented_by: {
                    default: this.$root.user.fullname,
                    text: this.$root.label('presented_by'),
                    icon: 'person',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => item?.presented_by ?? '--'
                },
                image: {
                    default: null,
                    text: this.$root.label('image'),
                    icon: 'label',
                    clone: false,
                    content: (item) => item?.image ?? '--'
                },
                is_type: {
                    default: 0,
                    text: 'Art',
                    icon: 'flaky',
                    clone: true,
                    content: (item) => item?.is_type ? 'Typ' : 'Münze'
                },
                id_cn: {
                    default: null,
                    text: 'ID',
                    icon: 'fingerprint',
                    header: true,
                    sortable: true,
                    filter: null,
                    content: (item) => { return item?.id_cn ? this.$handlers.format.cn_public_link({
                        id: item.id_cn,
                        public: 1,
                        kind: item?.is_type ? 'type' : 'coin'
                    }, 'cn ' + (item?.is_type ? 'type' : 'coin') + ' ' + item.id_cn) : '--' }
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
                }
            }
        }
    }
}

</script>
