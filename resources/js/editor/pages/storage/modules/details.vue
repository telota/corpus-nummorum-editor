<template>
<div
    class="drawer_bg transition-width-350 component-content"
    :class="dialog ? ' component-content-dialog' : ''"
    style="position: absolute; right: 0; box-shadow: 0px 0px 10px rgba(0,0,0,0.34);"
    :style="'width:' + width + 'px;overflow-y:' + (expanded ? 'auto' : 'hidden')"
>
    <!-- Header -->
    <adv-btn
        :icon="expanded ? 'chevron_right' : 'chevron_left'"
        :tooltip="expanded ? 'expand_less' : 'expand_more'"
        medium
        @click="$emit('expand', !expanded)"
    />

    <v-progress-linear :indeterminate="loading" height="1px" />

    <div v-if="expanded">

        <!-- File Navigator -->
        <div class="d-flex justify-space-between align-center">
            <adv-btn
                icon="arrow_left"
                tooltip="previous_file_in_selection"
                :disabled="navDisabled"
                medium
                v-on:click="$emit('shiftSelection', -1)"
            />
            <div
                class="font-weight-bold pa-2 text-truncate body-2"
                style="cursor: default"
                v-text="item.file ? item.file : 'none'"
            />
            <adv-btn
                icon="arrow_right"
                tooltip="next_file_in_selection"
                :disabled="navDisabled"
                medium
                v-on:click="$emit('shiftSelection', 1)"
            />
        </div>

        <v-divider />

        <template v-if="item.path">
            <div class="ma-3">
                <a
                    :href="'/storage/' + item.path"
                    target="_blank"

                >
                    <adv-img
                        contain
                        :src="item.path"
                        :background="$vuetify.theme.dark ? 'imgbg' : 'white'"

                        style="outline: 1px solid grey"
                    />
                </a>
            </div>

            <v-divider />

            <div class="d-flex flex-wrap">
                <a :href="$root.baseURL + '/storage/' + item.path" download>
                    <adv-btn
                        icon="file_download"
                        medium
                        :tooltip="item.file + ' herunterladen'"
                    />
                </a>

                <a :href="'https://digilib.bbaw.de/digitallibrary/digilib.html?fn=silo10/thrakien/' + item.path" target="_blank">
                    <adv-btn
                        icon="handyman"
                        medium
                        :tooltip="item.file + ' in Digilib öffnen'"
                    />
                </a>

                <adv-btn
                    v-if="select"
                    icon="touch_app"
                    :tooltip="'Select ' + item.file"
                    color-main="blue_prim"
                    color-hover="blue_sec"
                    color-text="white"
                    medium
                    :disabled="!item.path || selected === item.path"
                    v-on:click="$emit('select', item.path)"
                />

                <v-spacer />

                <adv-btn
                    icon="delete"
                    :tooltip="'Delete ' + item.file"
                    medium
                    v-on:click="dispatchAction('delete', item.path)"
                />
            </div>

            <v-divider />

            <!-- META -->
            <div v-if="item.path" class="pa-3">
                <table class="caption">
                    <tr>
                        <td>Type</td><td v-html="item.type" />
                    </tr>
                    <tr>
                        <td>Size</td><td v-html="printFileSize(item.size)" />
                    </tr>
                    <template v-if="item.relations">
                        <tr>
                            <td colspan="2" class="text-center pt-5" v-text="'related Objects (' + (item.relations.length) + ')'" />
                        </tr>
                        <template v-for="(rel, r) in item.relations">
                            <tr :key="r + '_' + 0">
                                <td colspan="2">&ensp;</td>
                            </tr>
                            <tr :key="r + '_' + 1">
                                <td>Name</td>
                                <td>
                                    <span
                                        v-if="['coins', 'types'].includes(rel.entity)"
                                        v-text="rel.name"
                                        style="cursor: pointer"
                                        @click="$store.commit('setDetailsDialog', { entity: rel.entity, id: rel.id })"
                                    />
                                    <a
                                        v-else
                                        :href="'/editor#/' + rel.entity + (['coins', 'types'].includes(rel.entity) ? '/show/' : '/') + rel.id"
                                        class="invert--text"
                                        v-text="rel.name"
                                    />
                                </td>
                            </tr>
                            <tr :key="r + '_' + 2">
                                <td>Author</td><td v-text="rel.author ? rel.author : '--'" />
                            </tr>
                            <tr :key="r + '_' + 3">
                                <td>Copyright</td><td v-text="rel.copyright ? rel.copyright : '--'" />
                            </tr>
                            <tr :key="r + '_' + 4">
                                <td>Created</td><td v-text="$handlers.format.date($root.language, rel.created, true)" />
                            </tr>
                        </template>
                    </template>
                </table>

            </div>
        </template>
    </div>

</div>
</template>

<script>

export default {
    data () {
        return {
            left: 0,
            vIndex: 0,
            meta: {
                active: false,
                data: {}
            }
        }
    },

    props: {
        dialog:         { type: Boolean, default: false },
        expand:         { type: Boolean, default: null },
        currentDir:     { type: [String, Boolean], default: null },
        currentFile:    { type: [String, Boolean], default: null },
        search:         { type: String, default: null },
        marked:         { type: Array, default: () => [] },
        select:         { type: Boolean, default: false },
        selected:       { type: [String, Number], default: null },
    },

    computed: {
        expanded () {
            if (this.expand === false) return false
            if (this.expand === true) return true
            if (this.currentFile) return true
            return false
        },
        width () {
            return this.expanded ? 300 : 40
        },
        loading () {
            return this.$store.state.storage.details.loading
        },
        directories () {
            return this.$store.state.storage.directory.items
        },
        currentNodes () {
            return this.getNodes(this.currentDir)
        },
        navDisabled () {
            return this.marked?.[1] ? false : true
        },
        item: {
            get: function () { return this.$store.state.storage.details.item ?? {} },
            set: function (value) { this.$store.commit('setDetailsItem', value ?? {}) }
        },
    },

    watch: {
        currentFile () {
            if (this.currentFile) {
                this.vIndex = 0
                this.fetchData(this.currentFile)
            }
            else this.item = null
        },
        expanded () { this.$emit('expand', this.expanded) }
    },

    created () {
        if (!this.currentFile) this.item = null
    },

    methods: {
        setPath (dir) {
            this.$emit('setPath', dir)
        },

        printFileSize (size) {
            if (size > 1000000) return (Math.ceil(size / (1024 * 1024) * 10) / 10) + '&nbsp;MB'
            if (size > 1000) return (Math.ceil(size / 1024 * 10) / 10) + '&nbsp;KB'
            return size + '&nbsp;B'
        },

        versionsList () {
            return this.item.data.map((item, i) => {
                return { value: i, text: (item.modifiedAt ? item.modifiedAt : '--') + (i === 0 ? ' (current)' : '')}
            })
        },

        async fetchData (path) {
            this.$store.dispatch('fetchDetails', { path })
        },

        async dispatchAction (action, path, value) {
            const url = 'storage-api/action/' + action

            let confirm = false
            if (action === 'delete') {
                if (this.item.relations[0]) alert('To avoid broken links, referenced Images cannot be deleted. Please delete the relations first.')
                else confirm = window.confirm('Are you sure you want to delete "' + path + '"?')
            }

            if (confirm) {
                if (action === 'delete') {
                    await axios.post(url, { path })
                        .then(async (response) => {
                            if (response?.data.success) {
                                this.item = null
                                this.$emit('setPath', this.currentDir)
                                this.$store.dispatch('fetchFiles', { directory: this.currentDir })
                                this.$store.dispatch('showSnack', { color: 'success', message: path + ' was deleted' })
                            }
                        })
                        .catch((error) => {
                            console.error(error.message)
                        })
                }
            }
        },

        async appendMeta (data) {
            this.meta = { active: false, data: {} }
            //console.log(data)
            await this.$store.dispatch('appendMeta', {
                path: this.currentFile,
                data
            })
            this.vIndex = 0
        }
    }
}

</script>

<style scoped>
    table {
        border-collapse: collapse;
        width: 100%;
    }
    table td {
        padding-bottom: 2px;
        vertical-align: top;
    }
    table tr td:first-child {
        font-weight: bold;
        padding-right: 15px;
        white-space: nowrap;
    }
</style>
