<template>
    <div>
        <simpleDataTemplate
            :key="language"
            :entity="entity"
            :name="$root.label(component)"
            :headline="headline"
            :attributes="attributes"
            defaultSortBy="release.desc"
            defaultDisplay="cards"
            cards
            :select="select"
            :selected="selected"
            v-on:select="(emit) => { $emit('select', emit); $emit('close') }"
        >
            <!-- Search ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:search>
                <v-row>
                    <v-col cols=12 sm=4 md=3>
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
                                ></v-text-field>
                            </template>
                            <v-card tile>
                                <v-date-picker v-model="attributes.release.filter"></v-date-picker>
                            </v-card>
                        </v-menu>
                    </v-col>
                    <template v-for="(item, i) in attributes">
                        <v-col
                            v-if="item.filter !== undefined && i !== 'release'"
                            :key="i"
                            cols=12
                            sm=4
                            md=3
                        >
                            <v-text-field dense outlined filled clearable
                                v-model="attributes[i].filter"
                                :label="item.text"
                                :prepend-icon="item.icon"
                            ></v-text-field>
                        </v-col>
                    </template>
                </v-row>
            </template>

            <!-- Content Cards ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:content-cards-header="slot">
                <div v-html="attributes.release.content(slot.item)" class="mr-5"></div>
                <div v-html="attributes.id_cn.content(slot.item)"></div>
            </template>

            <template v-slot:content-cards-body="slot">
                <Imager
                    v-if="slot.item.image"
                    :item="slot.item"
                    color_background="white"
                    contain
                ></Imager>
                <div class="body-1 font-weight-bold mb-3 mt-3" v-html="attributes['header_' + language].content(slot.item)"></div>
                <div class="body-2 mb-3" v-html="attributes['teaser_' + language].content(slot.item)"></div>
                <div class="body-2 text-right" v-html="attributes.presented_by.content(slot.item)"></div>
            </template>

            <!-- Editor ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:editor-header="slot">
                <div class="d-flex">
                    <div v-html="'ID&nbsp;' + slot.item.id + ', ' + slot.item.release"></div>
                    <div v-if="slot.item.id" class="ml-5">
                        <a
                            :href="'https://www.corpus-nummorum.eu/coin-of-the-month/' + slot.item.release.substring(0, 7).replace('-', '/')"
                            target="_blank"
                            v-text="'Preview'"
                        ></a>
                    </div>
                </div>
            </template>

            <template v-slot:editor-body="slot">
                <div>
                    <v-row>
                        <!-- JK: Release Date -->
                        <v-col cols="12" md="6">
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
                                    ></v-text-field>
                                </template>
                                <v-card tile>
                                    <v-date-picker v-model="slot.item.release"></v-date-picker>
                                </v-card>
                            </v-menu>
                        </v-col>

                        <!-- JK: Creator -->
                        <v-col cols="12" md="6">
                            <v-text-field dense outlined filled clearable
                                v-model="slot.item.presented_by"
                                :label="attributes.presented_by.text"
                                prepend-icon="person"
                                hint="not required"
                                counter=255
                            ></v-text-field>
                        </v-col>

                        <!-- JK: Coin/Type? -->
                        <v-col cols="12" md="6">
                            <div class="d-flex">
                                <v-text-field dense outlined filled
                                    v-model="slot.item.id_cn"
                                    :label="slot.item.is_type ? 'Type ID' : 'Coin ID'"
                                    hint="Click on arrows to switch ID"
                                    :prepend-icon="slot.item.is_type ? 'blur_circular' : 'copyright'"
                                ></v-text-field>

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

                        <!-- JK: Image -->
                        <v-col cols="12" md="6">
                            <div class="component-wrap d-flex">
                                <v-text-field dense outlined filled clearable
                                    v-model="slot.item.image"
                                    :label="attributes.image.text"
                                    :prepend-icon="attributes.image.icon"
                                    disabled
                                ></v-text-field>

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

                        <!-- JK: Description -->
                        <v-col
                            v-for="(l) in ['de', 'en']"
                            :key="l"
                            cols="12" sm="12" md="6"
                        >
                            <subheader :label="l.toUpperCase()" class="mb-5"></subheader>
                            <!-- JK: DE : Header -->
                            <v-textarea dense outlined filled no-resize
                                v-model="slot.item['header_' + l]"
                                :label="attributes['header_' + l].text"
                                rows="2"
                                prepend-icon="short_text"
                                hint="visible on preview: yes"
                                counter=255
                            ></v-textarea>
                            <!-- JK: DE : Teaser -->
                            <v-textarea dense outlined filled no-resize
                                v-model="slot.item['teaser_' + l]"
                                :label="attributes['teaser_' + l].text"
                                rows="5"
                                prepend-icon="short_text"
                                hint="visible on preview: yes"
                                counter=21845
                                class="mt-5"
                            ></v-textarea>
                            <!-- JK: DE : Text -->
                            <v-textarea dense outlined filled no-resize
                                v-if="!htmleditor"
                                v-model="slot.item['text_' + l]"
                                :label="attributes['text_' + l].text"
                                rows="10"
                                prepend-icon="notes"
                                counter=21845
                                class="mt-5"
                            ></v-textarea>
                            <div v-else class="ml-9 mb-3">
                                <div class="grey_sec mt-5" style="position: relative; height: 700px; width: 100%">
                                    <v-progress-linear indeterminate style="position: absolute"></v-progress-linear>
                                    <div class="pt-10 text-center" style="width: 100%; position: absolute" v-text="'Editor wird geladen ...'"></div>
                                    <!--</div>
                                    <tinymce
                                        style="position: absolute"
                                        v-model="slot.item['text_' + l]"
                                        api-key="no-api-key"
                                        :init="{
                                            height:         700,
                                            menubar:        true,
                                            plugins: [
                                                'advlist autolink lists link image charmap print preview anchor',
                                                'searchreplace visualblocks code fullscreen',
                                                'insertdatetime media table paste code help wordcount'
                                                ],
                                            toolbar:
                                                'undo redo | formatselect | bold italic backcolor | \
                                                alignleft aligncenter alignright alignjustify | \
                                                bullist numlist outdent indent | removeformat | help'}"
                                    ></tinymce>-->
                                </div>
                            </div>
                            <div class="d-flex justify-end">
                                <v-hover>
                                    <template v-slot:default="{ hover }" >
                                        <div
                                            style="cursor: pointer"
                                            :class="hover ? 'blue_sec--text' : ''"
                                            v-text="'Zu ' + (htmleditor ? 'Text-Editor' : 'HTML-Editor') + ' wechseln'"
                                            @click="htmleditor = !htmleditor"
                                        ></div>
                                    </template>
                                </v-hover>
                            </div>
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
                                :prop_item="{ id: slot.item.image, parent: 'CoinOfMonth' }"
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
                        prop_target="storage/CoinOfMonth"
                        prop_key="image"
                        v-on:close="upload = false"
                        v-on:ChildEmit="(emit) => {
                            slot.item.image = emit.url
                            upload = false
                        }"
                    ></upload>
                </div>
            </template>

        </simpleDataTemplate>
    </div>
</template>



<script>

//import tinymce from '@tinymce/tinymce-vue'

export default {
    /*components: {
        tinymce
    },*/
    data () {
        return {
            component:          'Coin of the Month',
            entity:             'coinofthemonth',
            attributes:         {},
            key_name_class:     'caption font-weight-thin text-uppercase mb-1',

            upload:             false,
            file_browser:       false,
            file_select:        null,
            htmleditor:         false
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
            return this.$root.label('coin_of_the_month')
        },

        release () {
            const date = new Date ();
            const date_month = date.getMonth() < 11 ? (date.getMonth() + 2) : 1;
            const date_year = date_month != 1 ? date.getFullYear() : (date.getFullYear() + 1);
            return date_year+'-'+(date_month > 9 ? date_month : ('0'+date_month))+'-01';
        }
    },

    watch: {
        language: function () { this.attributes = this.setAttributes() }
    },

    created () {
        this.$store.commit('setBreadcrumbs', [ // JK: Set Breadcrumbs
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
                    content: (item) => { return item?.id ? item.id : '--' }
                },
                release: {
                    default: this.release,
                    text: this.$root.label('release'),
                    icon: 'event',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: false,
                    content: (item) => { return item?.release ? this.$handlers.format.date(this.language, item.release) : '--' }
                },
                presented_by: {
                    default: this.$root.user.fullname,
                    text: this.$root.label('presented_by'),
                    icon: 'person',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => { return item?.presented_by ? item.presented_by : '--' }
                },
                image: {
                    default: null,
                    text: this.$root.label('image'),
                    icon: 'label',
                    clone: false,
                    content: (item) => { return item?.image ? item.image : null }
                },
                is_type: {
                    default: 0,
                    text: 'Art',
                    icon: 'flaky',
                    clone: true,
                    content: (item) => { return item?.is_type ? 'Typ' : 'Münze' }
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
                    content: (item) => { return item?.header_de ? item.header_de : '--' }
                },
                header_en: {
                    default: null,
                    text: this.$root.label('header') + ' (EN)',
                    icon: 'short_text',
                    header: this.language === 'en' ? true : false,
                    filter: this.language === 'en' ? null : undefined,
                    clone: false,
                    content: (item) => { return item?.header_en ? item.header_en : '--' }
                },
                teaser_de: {
                    default: null,
                    text: this.$root.label('teaser') + ' (DE)',
                    icon: 'short_text',
                    clone: false,
                    content: (item) => { return item?.teaser_de ? item.teaser_de : '--' }
                },
                teaser_en: {
                    default: null,
                    text: this.$root.label('teaser') + ' (EN)',
                    icon: 'short_text',
                    clone: false,
                    content: (item) => { return item?.teaser_en ? item.teaser_en : '--' }
                },
                text_de: {
                    default: null,
                    text: this.$root.label('text') + ' (DE)',
                    icon: 'short_text',
                    clone: false,
                    content: (item) => { return item?.text_de ? item.text_de : '--' }
                },
                text_en: {
                    default: null,
                    text: this.$root.label('text') + ' (EN)',
                    icon: 'short_text',
                    clone: false,
                    content: (item) => { return item?.text_en ? item.text_en : '--' }
                }
            }
        }
    }
}

</script>
