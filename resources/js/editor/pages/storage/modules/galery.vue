<template>
<!-- Items -->
<div
    id="file-galery"
    :style="'padding-left:' + left + 'px; padding-right:' + right + 'px;'"
>
    <div id="file-galery-container">
        <v-fade-transition>
            <!-- Loading -->
            <div
                v-if="loading"
                class="d-flex align-center justify-center"
                style="position: absolute; top: 0; bottom: 0;"
                :style="'left:' + left + 'px; right:' + right + 'px;'"
            >
                <v-progress-circular :size="200" indeterminate />
            </div>

            <!-- Tiles -->
            <v-virtual-scroll
                v-else-if="items[0]"
                :items="itemsToDisplay"
                :item-height="grid.dimensions"
                style="width: 100%"
                :dis-bench="3"
            >
                <template v-slot:default="{ item }">
                    <div style="padding: 10px">
                        <div
                            class="d-flex justify-space-between"
                            style="width: 100%;"
                            :style="'height:' + grid.dimensions + 'px;'"
                        >
                            <div
                                v-for="(path, p) in item"
                                :key="path + p"
                                :style="'width:' + grid.dimensions + 'px; padding:10px; height:' + grid.dimensions + 'px;'"
                            >
                                <v-hover v-if="path" v-slot="{ hover }">
                                    <!-- Tile -->
                                    <v-card
                                        tile
                                        style="padding: 10px; cursor:pointer;"
                                        :style="'height:' + (grid.dimensions - 20) + 'px;' +
                                            (hover ? 'outline: 3px solid lightsteelblue;' : '') +
                                            (selected.includes(path) ? ('outline: 3px solid ' + (hover ? 'cornflowerblue' : 'steelblue') + ';') : '') +
                                            (path === currentFile ? ('outline: 3px solid ' + (hover ? 'mediumblue' : 'navy') +';') : '')
                                        "
                                        hover
                                        @click.exact="select(path)"
                                        @click.ctrl="select(path, true)"
                                        @contextmenu="(element) => showContextMenu(element, path)"
                                    >
                                        <!-- Img -->
                                        <advImg contain :height="grid.dimensions - 80" :src="path" />

                                        <!-- Tooltip / Name -->
                                        <v-tooltip top>
                                            <template v-slot:activator="{ on }">
                                                <div
                                                    class="d-flex justify-center align-center"
                                                    style="margin-top:10px; height:30px; cursor:pointer;"
                                                    :style="'width:' + (grid.dimensions - 40) + 'px;'"
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
                    </div>
                    <!--<div style="height: 200px; margin: 20px;">
                    <v-row class="pt-0 pb-0">
                        <v-col
                            v-for="(path, p) in item"
                            :key="path + p"
                            :cols="cols"
                        >
                            <v-card tile class="pa-2">
                                <advImg contain :src="path" />
                            </v-card>
                        </v-col>
                    </v-row>
                    </div>-->
                </template>
            </v-virtual-scroll>
        </v-fade-transition>

        <!-- Loading
        <div
            v-if="loading"
            class="d-flex align-center justify-center"
            style="position: absolute; top: 0; bottom: 0; width: 100%"
        >
            <v-progress-circular :size="200" indeterminate />
        </div>-->

        <!-- Tiles
        <v-fade-transition>
            <v-row v-if="!loading && filtered[0]" class="ma-2">
                <v-col
                    v-for="(item, i) in filtered"
                    :key="item || i"
                    :cols="cols"
                >
                    <div
                        @click.exact="select(item)"
                        @click.ctrl="select(item, true)"
                        @contextmenu="(element) => showContextMenu(element, item)"
                    >
                        <advImg contain :src="item" />
                    </div>
                </v-col>
            </v-row>
        </v-fade-transition> -->

        <!-- Zoom -->
        <v-expand-transition>
            <div
                id="file-galery-zoom"
                :style="'margin-left:' + zoomPosition + 'px'"
                class="d-flex"
            >
                <v-hover
                    v-for="(side, s) in ['left', 'right']"
                    :key="side + s"
                    v-slot="{ hover }"
                >
                    <div
                        class="d-flex align-center justify-center"
                        style="width: 75px;height: 75px;border-top-right-radius: 75px 75px;"
                        :style="(s === 0 ? 'transform: scaleX(-1);' : '') + (hover && (s === 0 ? (zoom > 0) : (zoom < 2)) ? 'background-color:#ddd;cursor:pointer;' : '')"
                        @click="$store.commit('setGaleryZoom', s === 0 ? -1 : 1)"
                    >
                        <v-icon
                            x-large
                            class="mt-4 mr-3"
                            :disabled="s === 0 ? (zoom < 1) : (zoom > 1)"
                            v-text="s === 0 ? 'zoom_out' : 'zoom_in'"
                        />
                    </div>
                </v-hover>
            </div>
        </v-expand-transition>
    </div>
</div>
</template>

<script>

export default {
    data () {
        return {
        }
    },

    props: {
        currentDir:     { type: [String, Boolean], default: null },
        currentFile:    { type: [String, Boolean], default: null },
        search:         { type: String, default: null },
        selected:       { type: Array }
    },

    computed: {
        loading () { return this.$store.state.storage.files.loading || !this.directories[0] },

        directories () { return this.$store.state.storage.directory.items },

        items () {
            const stored = this.$store.state.storage.files.items

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

        left () { return this.$store.state.storage.directory.width },
        right () { return this.$store.state.storage.editor.width },
        width () {
            return window.innerWidth - this.left - this.right
        },

        zoomPosition () { return this.left + (this.width / 2) - 75 },
        zoom () { return this.$store.state.storage.galery.zoom },
        /*cols () {
            const width = this.width
            const zoom = this.zoom
            let cols = 12

            if (width > 999) cols = zoom === 1 ? 2 : (zoom === 0 ? 1 : 3)
            else if (width > 749) cols = zoom === 1 ? 3 : (zoom === 0 ? 2 : 4)
            else if (width > 499) cols = zoom === 1 ? 4 : (zoom === 0 ? 3 : 6)
            else if (width > 249) cols = zoom === 1 ? 6 : (zoom === 0 ? 4 : 12)

            return cols
        },*/

        grid () {
            //const totalHeight = window.innerHeight - 110

            let dimensions = this.zoom === 1 ? 200 : (this.zoom === 0 ? 150 : 300)
            if (dimensions > this.width) dimensions = this.width
            const cols = Math.floor(this.width / dimensions)

            return {
                cols: cols > 1 ? cols : 1,
                dimensions
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
            if (this.currentDir) this.$store.dispatch('fetchFiles', { directory: this.currentDir })
        },

        select (item, push = false) {
            this.$emit('selectFile', item, push)
            /*if (push) {
                if (this.selected.includes(item)) {
                    if (this.selected.length < 2) this.selected = []
                    else {
                        const index = this.selected.indexOf(item)
                        this.selected.splice(index, 1)
                    }
                    if (item = this.currentDir + '/' + this.currentFile) this.$emit('setPath', this.currentDir)
                }
                else this.selected.push(item)
            }
            else {
                if (this.selected.includes(item)) this.$emit('setPath', this.currentDir)
                else this.$emit('setPath', item)
            }*/
        },

        showContextMenu (element, item) {
            this.$root.showContextMenu('file', element, item)
        },

        setOutline (path, hover) {
            let color = null

            if (path = this.currentFile) color = 'yellow'
            else if (this.selected.includes(path)) color = 'blue'

            console.log(path, this.currentFile)

            return color ? ('outline: 3px solid ' + color) : ''
        }
    }
}

</script>

<style scoped>
    #file-galery {
        position: fixed;
        height: 100vH;
        width: 100%;
        right: 0;
        top: 0;
        padding-top: 110px;
    }

    #file-galery-container {
        width: 100%;
        overflow-y: auto;
        height: 100%;
    }

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
