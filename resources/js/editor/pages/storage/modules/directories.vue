<template>
<div id="browser-menu">

    <v-card
        tile
        :style="[
            'position: fixed',
            'top:' + position.top + 'px',
            'left:' + position.left + 'px',
            'height:' + (position.height - 15)+ 'px',
            'width: 200px'
        ].join(';\n')"
    >

        <!-- Header -->
        <div :style="'height:' + (showSearch ? 90 : 50) + 'px;'">
            <div class="d-flex align-center justify-space-between">
                <!-- Collapse -->
                <advbtn
                    icon="arrow_back_ios"
                    tooltip="hide_menu"
                    v-on:click="toggleMenu()"
                />
                <v-spacer />

                    <!-- Filter -->
                <advbtn
                    :icon="showSearch ? 'search_off' : 'search'"
                    :tooltip="(showSearch ? 'Hide' : 'Show') + ' Search'"
                    v-on:click="toggleSearch"
                />

                    <!-- Refresh -->
                <advbtn
                    icon="sync"
                    tooltip="Refresh List"
                    :loading="loading"
                    :disabled="loading"
                    v-on:click="$store.dispatch('fetchDirectories')"
                />

                <!-- Expand all -->
                <advbtn
                    :icon="expandAll ? 'unfold_less' : 'unfold_more'"
                    :tooltip="expandAll ? 'expand_less' : 'expand_more'"
                    v-on:click="$store.commit('setDirectoryExpandedAll', !expandAll)"
                />
            </div>

            <v-expand-transition>
                <div v-if="showSearch" class="d-flex align-center" style="height: 40px;">
                    <v-icon
                        small
                        class="ml-3 mr-2"
                        v-text="'search'"
                    />
                    <input
                        v-model="search"
                        style="border: 0; outline: none; height: 40px; width: 100%;"
                        placeholder="Verzeichnisnamen filtern"
                    />
                    <v-fade-transition>
                        <v-icon
                            v-if="search"
                            small
                            class="ml-2 mr-4"
                            v-text="'clear'"
                            @click="search = null"
                        />
                    </v-fade-transition>
                </div>
            </v-expand-transition>

            <v-progress-linear :indeterminate="loading" height="1px" />
        </div>

        <!-- Directory Items -->
        <div id="browser-directories" :style="'height:' + (position.height - 65)+ 'px'">

            <!-- Nodes -->
            <v-expand-transition>
                <div v-if="Object.keys(items)[0]" class="mt-2">

                    <!-- List -->
                    <div v-if="search">
                        <div v-if="!Object.keys(filteredItems)[0]" v-text="'keine Treffer'" />
                        <template v-else>
                            <template v-for="(item, path) in filteredItems">
                                <v-expand-transition :key="path">
                                    <v-hover v-slot="{ hover }">
                                        <div
                                            :ref="'node:' + path"
                                            class="d-flex align-center pa-1 pl-3 pr-4"
                                            :class="(hover ? 'grey lighten-3 ' : '') + (path === currentDir ? 'font-weight-medium' : '')"
                                            :style="'cursor:' + (path === currentDir ? 'default' : 'pointer')"
                                            @click="setPath(path)"
                                        >
                                            <div v-if="item.parent" class="text-truncate" v-text="item.parent" />
                                            <div v-text="item.name" style="white-space:nowrap;" />
                                        </div>
                                    </v-hover>
                                </v-expand-transition>
                            </template>
                        </template>
                    </div>

                    <!-- Tree -->
                    <div v-else>
                        <v-expand-transition>
                            <div>
                        <template v-for="(item, path) in items">
                            <!--<v-expand-transition :key="path">-->
                                <v-hover
                                    v-if="item.depth === 0 || item.show || expandAll"
                                    v-slot="{ hover }"
                                    :key="path"
                                ><!-- || ($root.contextMenu.show && $root.contextMenu.item.path === path)-->
                                    <div
                                        :ref="'node:' + path"
                                        class="d-flex align-center pa-1"
                                        :class="hover  ? 'grey lighten-3' : ''"
                                        style="cursor: pointer"
                                        @contextmenu="(element) => showContextMenu(element, item)"
                                    >
                                        <!-- Indentation -->
                                        <div :style="'padding-left:' + (item.depth * indentation) + 'em'" />

                                        <!-- Icon -->
                                        <v-btn
                                            icon
                                            small
                                            :disabled="!item.expandable || expandAll"
                                            :style="!item.expandable && !item.current ? 'opacity: 0' : ''"
                                            @click="toggleExpansion(item.path)"
                                        >
                                            <v-icon v-text="expandIcon(item)" />
                                        </v-btn>

                                        <!-- Label -->
                                        <div
                                            v-text="item.label.replaceAll('_', ' ')"
                                            class="pl-1 body-1 pr-2 text-truncate"
                                            :class="[item.depth === 0 ? 'text-uppercase' : '', item.current ? 'font-weight-bold' : ''].join(' ')"
                                            style="width: 100%"
                                            @click="setPath(path)"
                                        />

                                        <!-- Options
                                        <v-spacer />
                                        <v-btn
                                            icon
                                            small
                                            @click="(element) => showContextMenu(element, item)"
                                        >
                                            <v-icon small v-text="'more_vert'" />
                                        </v-btn>-->
                                    </div>
                                </v-hover>
                            <!--</v-expand-transition>-->
                        </template>
                        </div>
                        </v-expand-transition>
                    </div>
                </div>
            </v-expand-transition>

        </div>

    </v-card>
</div>
</template>

<script>

export default {
    data () {
        return {
            search: null,
            showSearch: false
        }
    },

    props: {
        currentDir:     { type: [Boolean, String], default: null },
        indentation:    { type: Number, default: 1 }
    },

    computed: {
        position () { return this.$root.position },

        loading () { return Object.keys(this.items).length < 1 || this.$store.state.storage.directory.loading },
        directories () { return this.$store.state.storage.directory.items },
        currentNodes () { return this.getNodes(this.currentDir) },

        expand () { return this.$store.state.storage.directory.expanded },
        expandAll () { return this.$store.state.storage.directory.expandedAll },

        items () {
            const items = {}

            if (this.directories?.[0]) {
                this.directories.forEach((path) => {
                    const split = path.split('/')
                    const depth = split.length - 1
                    const label = split.pop()
                    const nodes = this.getNodes(path)
                    const current = this.currentNodes.includes(path)
                    const expandable = this.directories.find((item) => item.startsWith(path + '/')) ? true : false

                    items[path] = {
                        path,
                        lazy: false,
                        label,
                        depth,
                        show: this.checkNodeVisibiliy(nodes, current),
                        expandable: current ? false : expandable,
                        expand: expandable ? this.expand.includes(path) : false,
                        current,
                    }
                })
            }

            return items
        },

        filteredItems () {
            if (!this.search || !this.directories?.[0]) return {}

            const items = {}

            this.directories
                .filter((path) => {
                    path = path.replaceAll(' ', '_')
                    if (this.search.startsWith('/')) {
                        return this.search.length < 2 ? true : path.startsWith(this.search.slice(1))
                    }
                    else if (path.includes(this.search)) return true
                    else return false
                })
                .forEach((path) => {
                    const split = path.split('/')
                    let name = '/' + path
                    let parent = null

                    if (split[1]) {
                        name = '/' + split.pop()
                        parent = '/' + split.join('/')
                    }

                    items[path] = {
                        name,
                        parent,
                    }
                })

            return items
        }
    },

    created() {
    },

    methods: {
        toggleMenu () {
            this.$store.dispatch('toggleSidebar', { key: 'directory' })
        },

        resize (e) {
            this.$store.commit('setWidth', { key: 'directory', value: e.width - 20 })
        },

        setPath (dir) {
            this.$emit('setPath', dir)
        },

        getNodes (path, join) {
            if (path) {
                const nodes = []

                path.split('/').reduce((parent, current, i) => {
                    const node = i > 0 ? ([parent, current].join('/')) : current
                    nodes.push(node)
                    return node
                }, [])

                return nodes
            }
            return []
        },

        checkNodeVisibiliy (nodes, current) {
            let check = true

            if (!current) {
                nodes.pop() // remove last node to get parent-Level

                nodes.forEach((node) => {
                    const visible = this.expand.includes(node) || this.currentNodes.includes(node)
                    if (!visible) check = false
                })
            }

            return check
        },

        toggleExpansion (path) {
            this.$store.dispatch('toggleDirectoryExpansion', { path })
            /*const index = this.expand.indexOf(path)

            if (index < 0) this.expand.push(path)
            else if (this.expand.length > 1) this.expand.splice(index, 1)
            else this.expand = []*/
        },

        expandIcon (item) {
            if (item.current) return 'chevron_right'
            if (!item.expandable) return 'remove'
            if (item.expand || this.expandAll) return 'expand_less'
            return 'expand_more'
        },

        showContextMenu (element, item) {
            this.$root.showContextMenu('directory', element, item)
        },

        toggleSearch () {
            if (this.showSearch) this.search = null
            this.showSearch = !this.showSearch
        }
    }
}

</script>

<style scoped>
    #browser-directories {
        width: 100%;
        overflow: auto;
        padding-bottom: 10px;
    }
</style>
