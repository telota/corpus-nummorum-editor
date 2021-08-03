<template>
<div>
    <dialog-template
        :dialog="dialog"
        :text="'File Manager | ' + currentPath"
        :closing="closing"
        :select="select"
        :selected="selected"
        v-on:close="$emit('close')"
    >
        <!-- Toolbar -->
        <component-toolbar :dialog="dialog">

            <!-- Upload -->
            <upload :directory="currentDir" v-on:update="setFileIndex">
                <template v-slot:activator="slot">
                    <div class="pr-2">
                    <adv-btn
                        icon="cloud_upload"
                        :tooltip="'upload Files to current Directory (/' + currentDir + ')'"
                        :disabled="!currentDir || directoryLoading"
                        v-on:click="slot.controls.active = true"
                    />
                    </div>
                </template>
            </upload>

            <!-- Breadcrumbs -->
            <breadcrumbs
                :directory="currentDir"
                :height="50"
                v-on:setPath="setCurrentPath"
            />

            <!-- Search
            <div class="pl-2 pr-3" :style="'width:' + searchInputWidth + 'px'">
                <v-text-field
                    dense clearable
                    v-model="searchFile"
                    placeholder="Dateien filtern"
                    class="mb-n3"
                />
            </div> -->

            <div :style="'width:' + searchInputWidth + 'px'">
                <input-template
                    v-model="searchFile"
                    icon="search"
                    class="ml-3 mr-2"
                    placeholder="File Filter"
                    clearable
                />
            </div>

            <!-- Global Search
            <globalsearch v-on:setPath="setCurrentPath">
                <template v-slot:activator="slot">
                    <advbtn
                        icon="travel_explore"
                        :disabled="directoryLoading"
                        tooltip="Globale Suche"
                        large
                        v-on:click="slot.controls.active = true"
                    />
                </template>
            </globalsearch> -->

            <!-- Refresh -->
            <adv-btn
                icon="sync"
                :loading="$store.state.storage.files.loading"
                :disabled="!currentDir || directoryLoading"
                tooltip="Refresh File Index"
                v-on:click="setFileIndex()"
            />

            <!--<v-icon v-text="'search'" />
            <div
                class="d-flex align-center pa-2 ml-2"
                style="height: 44px;"
                :style="'width:' + searchInputWidth + 'px; border-bottom: 1px solid ' + ($vuetify.theme.dark ? 'white' : 'black')"
            >
                <input
                    id="galery-filter"
                    name="galery-filter"
                    v-model="searchFile"
                    placeholder="Dateinamen filtern"
                    class="ml-1"
                    style="border: 0; outline: none; width: 100%; padding: 5px; line-height: 20px;"
                />
                <v-fade-transition>
                    <v-icon
                        v-if="searchFile"
                        v-text="'clear'"
                        @click="searchFile = null"
                    />
                </v-fade-transition>
            </div>-->
        </component-toolbar>

        <!-- Directory Drawer -->
        <directories
            :dialog="dialog"
            :currentDir="currentDir"
            v-on:setPath="setCurrentPath"
        />

        <!-- Content -->
        <galery
            :dialog="dialog"
            :currentDir="currentDir"
            :currentFile="currentFile"
            :search="searchFile"
            :marked="markedFiles"
            :right="showDetails ? 300 : 40"
            :select="select"
            :selected="selected"
            v-on:setPath="setCurrentPath"
            v-on:markFile="markFile"
            v-on:select="emitSelect"
        />

        <!-- Details -->
        <file-details
            :dialog="dialog"
            :expand="showDetails"
            :currentDir="currentDir"
            :currentFile="currentFile"
            :marked="markedFiles"
            :select="select"
            :selected="selected"
            v-on:expand="(emit) => { showDetails = emit }"
            v-on:setPath="setCurrentPath"
            v-on:markFile="markFile"
            v-on:shiftSelection="shiftSelection"
            v-on:select="emitSelect"
        />

    </dialog-template>

</div>
</template>


<script>
import directories from './modules/directories.vue'
import breadcrumbs from './modules/breadcrumbs.vue'
import galery from './modules/galery.vue'
import upload from './modules/upload.vue'
import fileDetails from './modules/details.vue'

export default {
    components: {
        directories,
        galery,
        breadcrumbs,
        upload,
        fileDetails
    },

    data () {
        return {
            path: null,
            currentDir: false,
            currentFile: false,
            searchFile: null,
            showDetails: null,
            marked: [],
            closing: 0,
        }
    },

    props: {
        dialog:     { type: Boolean, default: false },
        routedPath: { type: String, default: null },
        select:     { type: Boolean, default: false },
        selected:   { type: String, default: null }
    },

    computed:{
        directoryLoading () {
            return this.$store.state.storage.directory?.loading
        },
        directories ()      {
            return this.$store.state.storage.directory?.items
        },
        searchInputWidth () {
            if (this.$vuetify.breakpoint.lgAndUp) return 400
            if (this.$vuetify.breakpoint.mdAndUp) return 300
            return 150
        },
        markedFiles () {
            const marked = this.marked

            if (this.currentFile && !marked.includes(this.currentFile)) marked.push(this.currentFile)

            return marked.sort()
        },
        currentPath: {
            get: function () {
                return this.dialog ? this.path : this.routedPath
            },
            set: function (path) {
                if (this.dialog) this.path = path
                else {
                    if (path) {
                        if (!path.startsWith('/')) path = '/' + path
                        if (path.slice(-1) === '/') path = path.slice(0, -1)

                        if (this.routedPath === path) this.setFileIndex()
                        else this.$router.push('/storage' + path)
                    }
                    else if (this.routedPath) this.$router.push('/storage')
                }
            }
        }
    },

    watch: {
        currentPath: function () {
            this.handlePath()
        }
    },

    async created() {
        if (!this.directories?.[0]) await this.$store.dispatch('fetchDirectories')
        if (this.dialog && this.selected) this.currentPath = this.selected
        if (this.currentPath) this.handlePath()
    },

    methods: {
        setCurrentPath (path) {
            this.searchFile = null
            this.currentPath = path
        },

        handlePath () {
            let path = this.currentPath
            //console.log(path)

            if (path) {
                this.searchFile = null
                if (!path.startsWith('/')) path = '/' + path

                if (path.length > 1) {
                    path = path.slice(1)
                    if (path.slice(-1) === '/') path = path.slice(0, -1)
                    const split = path.split('/')
                    let directory = null
                    let file = null

                    if (split[split.length - 1]?.includes('.')) file = split.pop()

                    directory = split.join('/')

                    if (this.directories.includes(directory)) {
                        this.currentDir = directory
                        this.currentFile = file ? (directory + '/' + file) : null
                    }
                    else {
                        alert('Unknown Directory "' + directory + '"! You will be redirected to the Index.')
                        this.currentPath = null
                    }
                }
                else this.currentPath = null
            }
            else {
                this.currentDir = null
                this.currentFile = null
            }
        },

        setFileIndex (dir) {
            this.$store.dispatch('fetchFiles', { directory: dir ?? this.currentDir })
        },

        markFile (path, push = false) {
            const index = this.marked.indexOf(path)

            if (push) {
                if (index > 0) {
                    if (this.marked.length > 1) this.marked.splice(index, 1)
                    else this.marked = []
                }
                else this.marked.push(path)
            }

            else {
                this.marked = []
                if (this.currentPath === '/' + path) {
                    path = path.split('/')
                    path.pop()
                    this.currentPath = path.join('/')
                }
                else this.currentPath = path
            }
        },

        shiftSelection (step) {
            let index = this.marked.indexOf(this.currentFile)
            if (step === -1 && index === 0) index = this.marked.length - 1
            else if (step === 1 && index === this.marked.length - 1) index = 0
            else index += step

            this.currentPath = this.marked[index]
        },

        emitSelect (emit) {
            this.$emit('select', emit)
            if (confirm(emit + ' has been selected. Close Dialog?')) ++this.closing
        }
    }
}

</script>
