<template>
<div
    :class="'transition-width-350 component-content' + (dialog ? ' component-content-dialog' : '')"
    style="overflow-y: hidden; margin-left: 200px;"
    :style="'width:' + this.width + 'px'"
>
    <v-fade-transition>
        <!-- Loading -->
        <div
            v-if="loading || !items[0]"
            class="d-flex align-center justify-center headline"
            style="position: absolute; left: 0; top: 0; right: 0; bottom: 0; overflow-y: scroll; cursor: default;"
        >
            <v-progress-circular v-if="loading" :size="200" indeterminate />
            <div v-else-if="!currentDir" v-text="'Please select a directory'" />
            <div v-else-if="search" v-text="'No matches for \'' + search + '\''" />
            <div v-else v-text="'No Files in current directory'" />
        </div>

        <!-- Tiles -->
        <v-virtual-scroll
            v-else-if="items[0]"
            :items="itemsToDisplay"
            :item-height="grid.size"
            :bench="3"
            style="overflow-y: scroll"
        >
            <template v-slot:default="{ item }">
                <div
                    class="d-flex justify-center"
                    style="width: 100%;"
                    :style="'height:' + grid.size + 'px;'"
                >
                    <div
                        v-for="(path, p) in item"
                        :key="path + p"
                        :style="'width:' + grid.size + 'px; height:' + grid.size + 'px;'"
                        style="padding: 20px 10px 0 10px"
                    >
                        <v-hover v-if="path" v-slot="{ hover }">
                            <!-- Tile -->
                            <v-card
                                tile
                                hover
                                :class="path === currentFile ? 'tile_selected' : 'tile_bg'"
                                style="padding: 10px; cursor:pointer;"
                                :style="[
                                    'height:' + (grid.size - 20) + 'px',
                                    (hover ? 'outline: 3px solid lightsteelblue' : ''),
                                    (marked.includes(path) ? ('outline: 3px solid ' + (hover ? 'cornflowerblue' : 'steelblue')) : '')
                                ].join(';\n')"
                                @click.exact="mark(path)"
                                @click.ctrl="mark(path, true)"
                                @contextmenu="(element) => showContextMenu(element, path)"
                            >
                                <v-btn
                                    v-if="select"
                                    fab
                                    absolute
                                    x-small
                                    dark
                                    class="blue_prim ml-n2 mt-n2"
                                    :disabled="selected === path"
                                    v-on:click="$emit('select', path)"
                                >
                                    <v-icon v-text="'touch_app'" />
                                </v-btn>

                                <!-- Img -->
                                <adv-img
                                    contain
                                    :height="grid.size - 80"
                                    :src="path"
                                    :background="$vuetify.theme.dark ? 'imgbg' : 'white'"
                                    style="outline: 1px solid grey"
                                />

                                <!-- Tooltip / Name -->
                                <v-tooltip top>
                                    <template v-slot:activator="{ on }">
                                        <div
                                            class="d-flex justify-center align-center caption"
                                            style="margin-top: 10px; height: 30px; cursor: pointer;"
                                            :style="'width:' + (grid.size - 40) + 'px;'"
                                            v-on="on"
                                        >
                                            <div class="text-truncate" v-text="path.split('/').pop().split('.')[0]" />
                                            <div v-text="'.' + path.split('.').pop()" />
                                        </div>
                                    </template>
                                    <span v-text="path.split('/').pop()" />
                                </v-tooltip>

                            </v-card>
                        </v-hover>
                    </div>
                </div>
            </template>
        </v-virtual-scroll>
    </v-fade-transition>

</div>
</template>

<script>

export default {
    data () {
        return {
        }
    },

    props: {
        dialog:         { type: Boolean, default: false },
        right:          { type: Number, default: 40 },
        currentDir:     { type: [String, Boolean], default: null },
        currentFile:    { type: [String, Boolean], default: null },
        search:         { type: String, default: null },
        marked:         { type: Array, default: () => [] },
        select:         { type: Boolean, default: false },
        selected:       { type: String, default: null },
    },

    computed: {
        loading () { return this.$store.state.storage.files.loading || !this.directories[0] },

        directories () { return this.$store.state.storage.directory.items },

        items () {
            const stored = this.$store.state.storage.files.items
            //console.log(stored)

            if (this.search) {
                const items = stored.filter((item) => item.toLowerCase().includes(this.search.toLowerCase()))

                this.$store.dispatch('showSnack', {
                    message: items.length + ' match' + (items.length === 1 ? '' : 'es') + ' for "' + this.search + '"',
                    color: items.length > 0 ? 'success' : 'error'
                })

                return items
            }
            else return stored
        },

        itemsToDisplay () {
            const flat = this.items
            const remapped = []

            for (let row = 0; row <= flat.length; row += this.grid.cols) {
                const rowToPush = [];
                for (let col = 0; col < this.grid.cols; ++col) {
                    rowToPush.push(flat[row + col] ?? null)
                }
                remapped.push(rowToPush)
            }

            return remapped
        },

        width () {
            return this.$vuetify.breakpoint.width - (200 + this.right + (this.dialog ? 50 : 0))
        },

        height () {
            return this.$vuetify.breakpoint.height - (this.dialog ? 170 : 90)
        },

        zoomPosition () {
            return this.left + (this.width / 2) - 75
        },
        zoom () {
            return this.$store.state.storage.galery.zoom
        },

        grid () {
            const width = this.width - 40

            let cols = 6

            if (width < 1500) cols = 5
            if (width < 1200) cols = 4
            if (width < 900) cols = 3
            if (width < 600) cols = 2
            if (width < 400) cols = 1

            return {
                cols,
                size: Math.floor (width / cols)
            }
        }
    },

    watch: {
        currentDir () { this.fetchIndex() },
        //currentFile () { this.selectCurrentFile () },
        //filtered () { this.selectCurrentFile () }
    },

    mounted() {
        this.fetchIndex()
    },

    methods: {
        setCurrentDir (dir) {
            if (dir) this.$emit('setCurrentDir', dir)
        },

        async fetchIndex () {
            if (this.currentDir) {
                await this.$store.dispatch('fetchFiles', { directory: this.currentDir })
                if (this.currentFile && !this.items.includes(this.currentFile)) alert(this.currentFile + ' was not found')
            }
            else this.$store.commit('setFilesItems', [])
        },

        mark (item, push = false) {
            this.$emit('markFile', item, push)
        },

        showContextMenu (element, item) {
            this.$root.showContextMenu('file', element, item)
        },

        setOutline (path, hover) {
            let color = null

            if (path = this.currentFile) color = 'yellow'
            else if (this.marked.includes(path)) color = 'blue'

            //console.log(path, this.currentFile)

            return color ? ('outline: 3px solid ' + color) : ''
        }
    }
}

</script>

<style scoped>
    #file-galery-zoom {
        position: fixed;
        bottom: 0;
        right: 0;
        left: 0;
        width: 150px;
        height: 75px;
        border-top-left-radius: 75px 75px;
        border-top-right-radius: 75px 75px;
        box-shadow: 1px 1px 7px black;
        z-index: 100;
        background-color: white;
    }
</style>
