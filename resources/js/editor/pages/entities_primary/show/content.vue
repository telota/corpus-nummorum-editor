<template>
<v-expand-transition>

    <!-- No Item Data -->
    <div v-if="!item.id" class="d-flex align-center justify-center headline" style="height: 500px">
        <div
            v-if="no_match === null"
            v-text="'No ID given'"
        />

        <div
            v-else-if="loading === true"
            v-text="'loading ... please wait'"
        />

        <div
            v-else-if="no_match === true"
            class="text-center"
            v-html="'There is no data for cn ' + entity.slice(0, -1) + ' ' + id + '<br />Please check the given ID and retry.'"
        />

        <div
            v-else
            v-text="'Oops... something unexpected happend. Please reload and retry.'"
        />
    </div>


    <!-- Item found and Content given -------------------------------------------------------------------------------------- -->
    <div v-else>

        <!-- Top Information --------------------------------------------------------------- -->
        <div class="d-flex component-wrap justify-space-between">
            <!-- Status, Creator, Editor -->
            <div
                class="mb-2 body-2"
                v-html="$handlers.show_item_data(l, entity, item, 'system')"
            />

            <!-- Source Link -->
            <div v-html="labels['source'] + ':&emsp;' + $handlers.show_item_data(l, entity, item, 'source')"></div>
        </div>

        <!-- Forgery -->
        <div v-if="item.is_forgery" class="text-center red--text headline" v-html="'!!!&emsp;' + $root.label('forgery') + '&emsp;!!!'" />

        <!-- First Row / Obverse and Reverse ----------------------------------------------- -->
        <v-row>
            <v-col cols="12" sm="12" md="6" v-for="side in ['obverse', 'reverse']" :key="side">

                <!-- Header -->
                <subheader :label="side"></subheader>

                <!-- Content -->
                <v-row v-if="item[side]">

                    <!-- Image -->
                    <v-col cols="12" sm="3">
                        <v-card tile>
                            <coin-images
                                :images="images"
                                link
                                :side="side"
                            />
                        </v-card>
                    </v-col>

                    <!-- Information -->
                    <v-col cols="12" sm="9">
                        <table class="mt-n2" :style="collapse">
                            <tr v-for="(key) in first_row" :key="key">
                                <td
                                    :class="td_header"
                                    :style="no_wrap"
                                    v-text="labels[key]"
                                ></td>
                                <td :class="td_value">
                                    <div
                                        class="d-flex flex-wrap"
                                        v-html="$handlers.show_item_data(l, entity, item, key, side)"
                                    ></div>
                                </td>
                            </tr>
                        </table>
                    </v-col>

                </v-row>
            </v-col>
        </v-row>

        <!-- Second Row: Production, Owners / Variations and Persons --------------------------------------------------------- -->
        <v-row>
            <!-- Production and Basics -->
            <v-col v-for="(data, section) in second_row" :key="section" cols="12" sm="12" md="3">
                <!-- Header -->
                <subheader :label="section" class="mb-2"></subheader>

                <!-- Content -->
                <table v-if="item.id" class="ml-n1" :style="collapse">
                    <tr
                        v-for="key in data" :key="key"
                    >
                        <td
                            :class="td_header"
                            :style="no_wrap"
                            v-text="labels[key]"
                        ></td>
                        <td
                            :class="td_value"
                            v-html="$handlers.show_item_data(l, entity, item, key)"
                        ></td>
                    </tr>
                </table>
            </v-col>

            <!-- Variations (types only) -->
            <v-col cols="12" sm="12" md="3" v-if="entity === 'types'">
                <!-- Header -->
                <subheader label="type_variations" :count="item.variations" class="mb-2"></subheader>

                <!-- Content -->
                <div v-if="!item.variations" v-text="'--'"></div>
                <div v-else class="ml-n2">
                    <ol v-if="item.variations">
                        <li
                            v-for="(data, i) in item.variations"
                            :key="i"
                            class="font-weight-bold"
                        >
                            <div class="ml-3 mb-2 caption font-weight-regular" v-html="to_string.text(data, l)"></div>
                        </li>
                    </ol>
                </div>
            </v-col>

            <!-- Persons -->
            <v-col cols="12" sm="12" md="3">
                <!-- Header -->
                <subheader label="persons" :count="item.persons" class="mb-2"></subheader>

                <!-- Content -->
                <div v-if="!item.persons" v-text="'--'"></div>
                <div v-else class="ml-n2">
                    <ol v-if="item.persons">
                        <li
                            v-for="(data, i) in item.persons"
                            :key="i"
                            class="font-weight-bold"
                        >
                            <div class="ml-3 mb-2 caption font-weight-regular" v-html="to_string.individual(data, l)"></div>
                        </li>
                    </ol>
                </div>
            </v-col>
        </v-row>

        <!-- Related Opposite Entity --------------------------------------------------------- -->
        <ItemGallery
            :entity="entity"
            :search_key="'id_' + entity.slice(0, -1)"
            :search_val="item.id"
            :key="entity + item.id"
            color_main="grey_trip"
            color_hover="sysbar"
        />

        <!-- Hoard(s) and Findspot(s), Groups, Links --------------------------------------------------------- -->
        <v-row>
            <!-- Hoard(s) and Findspot(s) -->
            <v-col v-for="(key) in (entity === 'types' ? ['hoards', 'findspots'] : ['hoard', 'findspot'])" :key="key" cols="12" sm="12" md="3">
                <!-- Header -->
                <subheader v-if="entity === 'coins'" :label="key" class="mb-2"></subheader>
                <subheader v-else :label="key" :count="item[key]" class="mb-2"></subheader>

                <!-- Content -->
                <div v-if="entity === 'types'">
                    <div v-if="!item[key]" v-text="'--'"></div>
                    <div v-else class="ml-n2">
                        <ol>
                            <li
                                v-for="(data, i) in item[key]"
                                :key="i"
                                class="font-weight-bold"
                            >
                                <div class="ml-3 mb-2 caption font-weight-regular" v-html="to_string.hoard_findspot(data, l)"></div>
                            </li>
                        </ol>
                    </div>
                </div>

                <div v-else class="caption font-weight-regular" v-html="$handlers.show_item_data(l, entity, item, key)"></div>
            </v-col>

            <!-- Groups -->
            <v-col cols="12" sm="12" md="3">
                <!-- Header -->
                <subheader label="objectgroups" :count="item.dbi.groups" class="mb-2"></subheader>

                <!-- Content -->
                <div v-if="!item.dbi.groups" v-text="'--'"></div>
                <div v-else class="ml-n2">
                    <ol>
                        <li
                            v-for="(data, i) in item.dbi.groups"
                            :key="'d' + i"
                            class="font-weight-bold"
                        >
                            <div
                                class="ml-3 mb-2 caption font-weight-regular"
                                v-html="to_string.objectgroup(data, l)"
                            ></div>
                        </li>
                    </ol>
                </div>
            </v-col>

            <!-- Link -->
            <v-col cols="12" sm="12" md="3">
                <!-- Header -->
                <subheader label="web_references" :count="item.web_references" class="mb-2"></subheader>

                <!-- Content -->
                <div v-if="!item.web_references" v-text="'--'"></div>
                <div v-else class="ml-n2">
                    <ol>
                        <li
                            v-for="(data, i) in item.web_references"
                            :key="'l' + i"
                            class="font-weight-bold"
                        >
                            <div
                                class="ml-3 mb-2 caption font-weight-regular"
                                v-html="to_string.weblink(data, l)"
                            ></div>
                        </li>
                    </ol>
                </div>
            </v-col>
        </v-row>

        <!-- Refrences (Citations, Literature) --------------------------------------------------------- -->
        <v-row>
            <v-col cols="12" sm="12" md="6" v-for="key in ['citations', 'literature']" :key="key">
                <!-- Header -->
                <subheader :label="key" :count="item[key]" class="mb-2"></subheader>

                <!-- Content -->
                <div v-if="!item[key]" v-text="'--'"></div>
                <div v-else class="ml-n2">
                    <ol>
                        <li
                            v-for="(data, i) in item[key]"
                            :key="i"
                            class="font-weight-bold mb-1"
                        >
                            <div class="ml-3 mb-2 caption font-weight-regular" v-html="to_string.reference(data, l)"></div>
                        </li>
                    </ol>
                </div>
                <template v-if="entity === 'coins' && key === 'literature'">
                    <!-- Header -->
                    <subheader label="type_literature" :count="item.type_literature" class="mb-2 mt-4"></subheader>

                    <!-- Content -->
                    <div v-if="!item.type_literature" v-text="'--'"></div>
                    <div v-else class="ml-n2">
                        <ol>
                            <li
                                v-for="(data, i) in item.type_literature"
                                :key="i"
                                class="font-weight-bold mb-1"
                            >
                                <div class="ml-3 mb-2 caption font-weight-regular" v-html="to_string.reference(data, l)"></div>
                            </li>
                        </ol>
                    </div>
                </template>
            </v-col>
        </v-row>

        <!-- Remarks and Images --------------------------------------------------------- -->
        <v-row>
            <!-- Comments -->
            <v-col cols="12" sm="12" md="6">
                <!-- Header -->
                <subheader label="remarks" class="mb-2"></subheader>

                <!-- Content -->
                <table v-if="item.id" class="ml-n1" :style="collapse">
                    <tr
                        v-for="key in entity === 'coins' ?
                        ['comment_private', 'type_comment_private', 'comment_public', 'type_comment_public', 'description_private', 'name_private', 'pecularities'] :
                        ['comment_private', 'comment_public', 'description_private', 'name_private', 'pecularities']"
                        :key="key"
                    >
                        <td
                            :class="td_header"
                            :style="no_wrap"
                            v-text="$root.label(key)"
                        ></td>
                        <td
                            :class="td_value"
                            v-html="$handlers.show_item_data(l, entity, item, key)"
                        ></td>
                    </tr>
                </table>
            </v-col>

            <!-- Imagesets -->
            <v-col cols="12" sm="6">
                <subheader label="imagesets" :count="item.dbi.images" class="mb-2"></subheader>
                <v-row>
                    <v-col cols="12" sm="2" md="4" xl="3" v-for="(img, i) in images" :key="i">
                        <v-card tile class="tile_bg">
                            <coin-images
                                :images="images"
                                :index="i"
                                link
                            />
                            <div
                                class="caption text-center text-truncate pa-2"
                                v-text="img.kind + ' (ID' + img.id + ')'"
                            />
                        </v-card>
                    </v-col>
                </v-row>
            </v-col>
        </v-row>

        <!-- Imported Data -->
        <v-row>
            <v-col cols="12" sm="12" md="12">
                <imported :data="item.dbi.submitted"></imported>
            </v-col>
        </v-row>

        <div class="d-flex justify-center">
            <v-btn text v-text="'reload'" @click="setItem(true)" />
        </div>

    </div>
</v-expand-transition>
</template>



<script>
import imported from './../modules/importContent.vue'
import linkedInherited from './../modules/linkedInherited.vue'

export default {
    components: {
        imported,
        linkedInherited
    },

    data () {
        return {
            loading:    false,
            no_match:   null,
            item:       {},

            details_dialog:     {
                entity: null,
                id: null
            },

            tiles:      24,
            offset:     0,

            // CSS
            td_header:  'pa-1 caption d-flex align-start font-weight-thin text-uppercase pr-3',
            td_value:   'pa-1 caption',
            no_wrap:    'white-space: nowrap;',
            collapse:   'border-collapse: collapse; border-spacing: 0;'
        }
    },

    props: {
        dialog: { type: Boolean, default: false },
        entity: { type: String, required: true },
        id:     { type: [Number, String], required: true }
    },

    computed: {
        // Localization
        l () { return this.$root.language },
        labels () { return this.$root.labels },

        images () {
            if (this.entity === 'types' && this.item?.images?.[0]?.id) return this.item.images
            if (this.entity === 'coins' && this.item?.dbi?.images?.[0]?.id) return this.item.dbi.images
            return []
        },

        to_string () { return this.$handlers.format.stringify_data },

        first_row () {
            return ['legend', 'design', 'monograms', 'symbols'].concat(this.entity === 'coins' ? ['countermark', 'undertype', 'controlmarks'] : [])
        },

        second_row () {
            const cols = {
                production: ['mint', 'authority', 'authority_person', 'authority_group', 'date', 'period'],
                basics: ['material', 'denomination', 'standard', 'weight', 'diameter', 'axis', 'centerhole']
            }
            if (this.entity === 'coins') {
                cols['owners'] = ['owner_original', 'collection_id', 'owner_reproduction', 'plastercast_id', 'provenience']
            }
            return cols
        }
    },

    watch: {
        entity () { this.setItem() },
        id () { this.setItem() }
    },

    created () {
        this.setItem()
    },

    methods: {
        async setItem (noCaching) {
            if (this.entity && this.id) {
                this.loading = this.$root.loading = true
                this.item = {}
                this.no_match = false

                const dbi = await this.$root.DBI_SELECT_GET(this.entity, this.id)

                if (dbi.contents?.[0]?.id) {
                    this.item = dbi.contents[0]
                    this.$emit('public', this.item.dbi?.public)
                    if (!noCaching) this.$emit('cache', { entity: this.entity, id: this.id })
                }
                else this.no_match = true

                this.loading = this.$root.loading = false
            }
            else this.no_match = null
        },
    }
}

</script>
