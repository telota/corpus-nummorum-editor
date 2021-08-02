<template>
<div>

    <!-- Toolbar -->
    <component-toolbar>
        <a :href="'/editor#/' + (entity === 'coins' ? 'types' : 'coins') + '/import/' + encodeURI(sourceLink)">
            <adv-btn
                :icon="entity === 'coins' ? 'blur_circular' : 'copyright'"
                :tooltip="'Switch to ' + (entity === 'coins' ? 'types' : 'coins')"
                color-hover="header_hover"
            />
        </a>

        <input-template
            v-model="sourceLink"
            icon="public"
            class="ml-2"
            placeholder="Source URL"
            clearable
            v-on:keyup_enter="pushSource()"
        />

        <v-hover v-slot="{ hover }">
            <div
                class="d-flex align-center justify-center headline font-weight-bold text-uppercase light-blue--text text--darken-2"
                :class="hover ? 'header_hover' : 'header_bg'"
                style="width: 200px; height: 50px; cursor: pointer"
                @click="pushSource()"
            >
                <v-icon v-text="'play_arrow'" class="mr-2 light-blue--text text--darken-2" />
                <div v-text="'Get Data'" />
            </div>
        </v-hover>

        <div :class="$root.vDivider" />

        <v-hover v-slot="{ hover }">
            <div
                class="d-flex align-center justify-center headline font-weight-bold text-uppercase"
                :class="(saveDisabled ? 'grey--text' : 'light-blue--text text--darken-2') + (hover && !saveDisabled ? ' header_hover' : ' header_bg')"
                style="width: 200px; height: 50px;"
                :style="saveDisabled ? 'cursor: default:' : 'cursor: pointer'"
                @click="saveDisabled ? '' : save()"
            >
                <v-icon v-text="'save'" class="mr-2" :class="saveDisabled ? 'grey--text' : 'light-blue--text text--darken-2'" />
                <div v-text="'save'" />
            </div>
        </v-hover>
    </component-toolbar>

    <!-- Content -->
    <component-content>
        <sheet-template>
            <div class="d-flex align-center mb-3">
                <div class="headline mr-3" v-text="'New cn ' + entity.slice(0, -1)" />
                <v-divider />
            </div>

            <!-- Loading -->
            <div v-if="loading" class="text-center headline mt-5" v-text="'Loading ... '" />

            <!-- Error -->
            <div v-else-if="error || duplicates[0]">
                <div class="font-weight-bold red--text mb-2" v-text="error" />
                <template v-if="duplicates[0]">
                    <div
                        v-for="(d) in duplicates"
                        :key="d"
                        class="mb-1"
                    >
                        <a :href="'/editor#/' + entity + '/show/' + d" v-text="'cn ' + entity.slice(0, -1) + ' ' + d" />
                    </div>
                </template>
            </div>

            <!-- Select Template -->
            <v-row v-else>
                <v-col
                    cols="12"
                    sm="6"
                    md="4"
                    lg="3"
                    v-for="(i, key) in selection"
                    :key="key"
                >
                    <div class="d-flex component-wrap align-start">
                        <v-checkbox
                            v-model="selection[key]"
                            class="mt-0"
                            color="blue_prim"
                            :disabled="key === 'sourceLink'"
                        />
                        <div class="pt-1 ml-1">
                            <div class="mb-1 font-weight-bold" v-text="$root.label(key)" />
                            <div v-html="sourceData.data[key]" />
                            <div v-if="sourceData.info[key]" v-text="sourceData.info[key]" class="mr-3" />
                        </div>
                    </div>
                </v-col>
            </v-row>

            <!-- Infos -->
            <v-row style="margin-top: 150px;">
                <v-col cols="12" sm="6">
                    <v-card class="tile_bg caption mr-5" tile>
                        <v-card-text>
                            <p class="font-weight-bold red--text">Wichtiger Hinweis:</p>
                            <p>
                                Bitte beachten Sie die korrekte Formatierung des Links, eine vollständige Adresse mit Protokollkürzel (z.B. http://numismatics.org/...).<br />
                                Vermeiden Sie Leerzeichen, Zeilenumbrüche oder sonstige Steuerzeichen.
                            </p><p>
                                Um Dubletten zu vermeiden, können bereits importierte Münzen/Typen nicht erneut importiert werden.
                            </p>
                        </v-card-text>
                    </v-card>
                </v-col>

                <v-col cols="12" sm="6">
                    <v-card class="tile_bg caption" tile>
                        <v-card-text>
                            <p class="font-weight-bold red--text">Noch ein Wichtiger Hinweis:</p>
                            <p>
                                Der Importer ist noch nicht zu 100% fertig.
                                Es werden noch nicht alle Felder berücksichtigt und die Darstellung in der Übersicht ist unvollständig. Er funktioniert aber.
                                Aktuell können nur Münzen/Typen von http://numismatics.org ausgelesen werden.
                                Weitere Adressen (z.B IKMK) folgen in der Zukunft.
                            </p>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>
        </sheet-template>
    </component-content>

</div>
</template>

<script>

export default {
    data () {
        return {
            sourceLink:     this.source,
            sourceData:     {},
            error:          null,
            duplicates:     [],
            selection:      {},
            loading:        false
        }
    },

    props: {
        entity: { type: String, required: true },
        source: { type: String, required: null },
    },

    computed: {
        title () {
            return 'New cn ' + this.entity.slice(0, -1) + ' (import of ' + this.source + ')'
        },
        saveDisabled () {
            if (this.loading) return true
            if (!this.sourceData?.url) return true
            if (this.error) return true
            return false
        }
    },

    watch: {
        entity () { this.watchInput() },
        source () { this.watchInput() }
    },

    created () {
        if (this.source) this.getSourceData()
        else this.$root.setTitle('New cn ' + this.entity.slice(0, -1) + ' (Import)')
    },


    methods: {
        pushSource () {
            const encoded = encodeURI(this.sourceLink)
            console.log(this.sourceLink, encoded, this.source)
            if (this.sourceLink !== this.source) this.$router.push('/' + this.entity + '/import/' + encoded)
            else this.getSourceData()
        },

        watchInput () {
            this.sourceLink = this.source
            this.error = null
            this.duplicates = []
            this.sourceData = {}
            this.selection = {}

            if (this.sourceLink) this.getSourceData()
        },

        checkSource () {
            let error = null
            if (!this.source) error = 'No Source given'
            else if (this.source.slice(0, 7) !== 'http://' && this.source.slice(0, 8) !== 'https://') error = 'Source is no valid url (must start with http:// or https://)'
            this.error = error
            return error ? false : true
        },

        async getSourceData () {
            if (this.checkSource()) {
                this.$root.loading = this.loading = true
                this.$root.setTitle(this.title)

                this.error = null
                this.duplicates = []
                this.sourceData = {}
                this.selection = {}

                const dbi = await this.$root.DBI_SELECT_POST('import/' + this.entity, { source: this.sourceLink })

                if (!dbi?.url) this.error = typeof dbi === 'object' ? ('Source Dataobject does not seem to be a ' + this.entity.slice(0, -1)) : dbi
                else if (dbi?.duplicates?.[0]) {
                    this.error = 'This object has already been imported. Duplicate(s) detected:'
                    this.duplicates = dbi.duplicates
                }
                else {
                    this.sourceData = dbi
                    Object.keys(this.sourceData.data).forEach((key) => {
                        this.selection[key] = true
                    })
                }

                this.$root.loading = this.loading = false
            }
        },

        async save () {
            this.$root.loading = this.loading = true

            const item = this.$handlers.create_item(this.entity)

            // get selected values
            Object.keys(this.selection).forEach((key) => {
                if (this.selection[key] === true) { item[key] = this.sourceData.data[key] }
            })

            let response = await this.$root.DBI_INPUT_POST(this.entity, 'input', item)

            if (response.success) {
                this.$store.dispatch('showSnack', { color: 'success', message: response.success })
                this.$router.push('/' + this.entity + '/edit/' + response.id)
            }
            else console.error('Data Input Error: response was not ok')

            this.$root.loading = this.loading = false
        },

        showItemData (key, section) {
            return this.$handlers.show_item_data(this.$root.language, this.entity, this.sourceData, key, section)
        }
    }
}
</script>
