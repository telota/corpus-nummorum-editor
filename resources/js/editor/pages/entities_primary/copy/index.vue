<template>
<div>
    <!-- Toolbar -->
    <component-toolbar>
        <a :href="'/editor#/' + (entity === 'coins' ? 'types' : 'coins') + '/copy/' + sourceEntity + '/' + sourceId">
            <adv-btn
                :icon="entity === 'coins' ? 'blur_circular' : 'copyright'"
                :tooltip="'Switch to ' + (entity === 'coins' ? 'types' : 'coins')"
                color-hover="header_hover"
            />
        </a>

        <div class="headline ml-2" v-text="title" />

        <v-spacer />

        <!-- Actions -->

        <a :href="'/editor#/' + entity + '/search'">
            <adv-btn
                icon="search"
                tooltip="Go to search"
                color-hover="header_hover"
            />
        </a>
        <a :href="'/editor#/' + entity + '/edit'">
            <adv-btn
                icon="add"
                tooltip="Create new Object"
                color-hover="header_hover"
            />
        </a>

        <div :class="divider" />

        <adv-btn
            icon="preview"
            tooltip="Details page Preview"
            color-hover="header_hover"
            @click="$store.commit('setDetailsDialog', { entity: sourceEntity, id: sourceId })"
        />

        <div>
            <a v-if="status === 1" :href="'https://www.corpus-nummorum.eu/' + sourceEntity + '/' + sourceId" target="_blank">
                <adv-btn
                    icon="public"
                    :tooltip="'Show source item on public website'"
                    color-hover="header_hover"
                />
            </a>

            <adv-btn
                v-else
                icon="public"
                disabled
            />
        </div>

        <div :class="divider" />

        <!-- Save -->
        <v-hover v-slot="{ hover }">
            <div
                class="d-flex align-center justify-center headline font-weight-bold text-uppercase light-blue--text text--darken-2"
                :class="hover ? ' header_hover' : ' header_bg'"
                style="width: 200px; height: 50px; cursor: pointer"
                @click="save()"
            >
                <v-icon v-text="'save'" class="mr-2 light-blue--text text--darken-2" />
                <div v-text="'save'" />
            </div>
        </v-hover>

    </component-toolbar>

    <!-- Images -->
    <edit-images :entity="sourceEntity" :item="sourceData" />

    <!-- Content -->
    <div class="component-content" style="width: calc(100vw - 200px);">
        <sheet-template>
            <div v-if="loading || !sourceData.id" class="headline text-center mt-8" v-text="loading ? 'Loading ...' : ('Error: cn ' + sourceEntity.slice(0, -1) + ' ' + sourceId + ' does not exist')" />

            <template v-else>
                <div v-for="(keys, section) in copyKeys" :key="section" class="mb-5">

                    <div class="d-flex align-center">
                        <div class="title text-uppercase" v-text="$root.label(section)" />
                        <v-divider class="ml-2" />
                    </div>

                    <v-row class="ma-0 pa-0">
                        <v-col
                            v-for="(value, key) in keys"
                            :key="key"
                            cols="12"
                            sm="6"
                            md="4"
                            lg="3"
                        >
                            <div class="d-flex align-start caption mt-n1">
                                <v-checkbox
                                    v-model="copyKeys[section][key]"
                                    class="mt-0"
                                    color="blue_prim"
                                />
                                <div class="pt-1 ml-1">
                                    <div class="mb-1 font-weight-bold" v-text="$root.label(key)" />
                                    <div v-html="showItemData(key, section)" />
                                </div>
                            </div>
                        </v-col>
                    </v-row>
                </div>
            </template>
        </sheet-template>
    </div>
</div>
</template>


<script>
import editImages       from './../edit/editImages.vue'

export default {
    components: {
        editImages
    },
    data () {
        return {
            sourceData:    {},

            loading:        false,
            divider:        'header_hover fill-height width-1px',

            copyKeys: {},
            copyKeysAvailable: {
                production: [
                    { label: 'mint',                cc: 1, ct: 1, tt: 1, tc: 1 },
                    //{ label: 'issuer',              cc: 1, ct: 1, tt: 1, tc: 1 },
                    { label: 'authority',           cc: 1, ct: 1, tt: 1, tc: 1 },
                    { label: 'authority_person',    cc: 1, ct: 1, tt: 1, tc: 1 },
                    { label: 'authority_group',     cc: 1, ct: 1, tt: 1, tc: 1 },
                    { label: 'date',                cc: 1, ct: 1, tt: 1, tc: 1 },
                    { label: 'period',              cc: 1, ct: 1, tt: 1, tc: 1 }
                ],
                basics: [
                    { label: 'material',            cc: 1, ct: 1, tt: 1, tc: 1 },
                    { label: 'denomination',        cc: 1, ct: 1, tt: 1, tc: 1 },
                    { label: 'standard',            cc: 1, ct: 1, tt: 1, tc: 1 }
                ],
                depiction: [
                    { label: 'design',              cc: 1, ct: 1, tt: 1, tc: 1 },
                    { label: 'legend',              cc: 1, ct: 1, tt: 1, tc: 1 },
                    { label: 'monograms',           cc: 1, ct: 1, tt: 1, tc: 1 },
                    { label: 'symbols',             cc: 1, ct: 1, tt: 1, tc: 1 },
                    { label: 'controlmarks',        cc: 1, ct: 0, tt: 0, tc: 0 }
                ],
                references: [
                    { label: 'citations',           cc: 1, ct: 0, tt: 1, tc: 0 },
                    { label: 'literature',          cc: 1, ct: 0, tt: 1, tc: 0 },
                    { label: 'links',               cc: 1, ct: 0, tt: 1, tc: 0 }
                ],
                individuals: [
                    { label: 'persons',             cc: 1, ct: 1, tt: 1, tc: 1 }
                ],
                miscellaneous: [
                    { label: 'comment_private',     cc: 1, ct: 0, tt: 1, tc: 0 },
                    { label: 'comment_public',      cc: 1, ct: 0, tt: 1, tc: 0 },
                    { label: 'pecularities',        cc: 1, ct: 0, tt: 1, tc: 0 },
                    { label: 'groups',              cc: 1, ct: 0, tt: 1, tc: 0 }
                ]
            }
        }
    },

    props: {
        entity:         { type: String, required: true },
        sourceEntity:   { type: String, required: true },
        sourceId:       { type: Number, required: true },
    },

    computed: {
        mode () {
            return this.entity.slice(0, 1) + this.sourceEntity.slice(0, 1)
        },

        status () {
            return this.sourceData?.dbi?.public ?? 0
        },

        title () {
            return 'New cn ' + this.entity.slice(0, -1) + ' (copy of cn ' + this.sourceEntity.slice(0, -1) + ' ' + this.sourceId + ')'
        }
    },

    watch: {
        title () { this.getSourceData() }
    },

    created () {
        this.getSourceData()
    },


    methods: {
        async getSourceData () {

            this.$root.loading = this.loading = true

            this.$root.setTitle(this.title)
            this.sourceData = {}
            const sections = []

            // Create Sections from template
            Object.keys(this.copyKeysAvailable).forEach((index) => {
                if(index === 'depiction') {
                    sections.push(
                        { index: index, section: 'obverse' },
                        { index: index, section: 'reverse' }
                    )
                }
                else sections.push({ index: index, section: index })
            })

            // Create Copy Keys
            sections.forEach((v) => {
                this.copyKeys[v.section] = {}
                this.copyKeysAvailable[v.index].forEach((key) => {
                    if(key[this.mode]) { this.copyKeys[v.section][key.label] = true }
                })
            })

            const dbi = await this.$root.DBI_SELECT_GET(this.sourceEntity, this.sourceId)

            if (dbi?.contents?.[0]) this.sourceData = dbi.contents[0]
            else console.error('COPY', this.sourceEntity, this.sourceId, 'Error: No Data received')

            this.$root.loading = this.loading = false
        },

        createItem () {
            let item = this.$handlers.create_item(this.entity)
            const sourceItem = this.$handlers.create_item(this.sourceEntity, this.sourceData)

            // Set inheriting Type
            if (this.mode === 'ct') item.inherited.id_type = sourceItem.id

            // Iterate over Copy Keys
            Object.keys(this.copyKeys).forEach((section) => {
                Object.keys(this.copyKeys[section]).forEach((key) => {
                    if(this.copyKeys[section][key]) {
                        if(['obverse', 'reverse'].includes(section)) key = section.slice(0, 1) + '_' + key // add section if obverse/reverse
                        item = this.$handlers.copy_item_data(item, sourceItem, key)
                    }
                })
            })

            return item
        },

        async save () {
            this.$root.loading = this.loading = true

            const response = await this.$root.DBI_INPUT_POST(this.entity, 'input', this.createItem());

            if (response?.success) {
                this.$store.dispatch('showSnack', { color: 'success', message: response.success })
                this.$router.push('/' + this.entity + '/edit/' + response.id)
            }
            else console.error('Data Input Error: response was not ok')

            this.$root.loading = this.loading = false
        },

        showItemData (key, section) {
            return this.$handlers.show_item_data(this.$root.language, this.sourceEntity, this.sourceData, key, section)
        }
    }

}

</script>
