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
            <div>
                <adv-btn
                    icon="cloud_upload"
                    :tooltip="'upload Files to current Directory (/' + currentDir + ')'"
                    color-hover="header_hover"
                    :disabled="!currentDir || directoryLoading"
                    v-on:click="upload = true"
                />
            </div>

            <!-- Directory-->
            <div class="pr-2">
                <adv-btn
                    icon="create_new_folder"
                    :tooltip="'Create new subdirectory in current Directory (/' + currentDir + ')'"
                    color-hover="header_hover"
                    :disabled="!currentDir || directoryLoading"
                    v-on:click="addDirectory"
                />
            </div>

            <!-- Breadcrumbs -->
            <breadcrumbs
                :directory="currentDir"
                :height="50"
                v-on:setPath="setCurrentPath"
            />

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
                color-hover="header_hover"
                v-on:click="setFileIndex()"
            />
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

    <!-- Upload -->
    <upload
        :show="upload"
        :directory="currentDir"
        v-on:update="setFileIndex"
        v-on:setPath="setCurrentPath"
        v-on:close="upload = false"
    />

    <!-- New Dir -->
    <small-dialog
        :show="newDir.show"
        icon="create_new_folder"
        :text="'New Directory' + (newDir.directory ? (' in ' + newDir.directory) : '')"
        v-on:close="newDir.show = false"
    >
        <div>
            <breadcrumbs
                :directory="newDir.directory"
                :height="50"
                v-on:setPath="(emit) => { newDir.directory = emit }"
            />
            <v-text-field dense outlined filled clearable
                v-model="newDir.name"
                label="Name for new Directory"
                class="mt-5"
            />
            <div class="text-center mb-3">
                <div>For compatibility reasons, critical characters are removed from the directory name. This is a preview, the name after creation may differ.</div>
                <div class="mt-1 mb-3"><b v-html="printNewDirName()" /></div>
                <div>Please keep in mind that directory names are public. Please pay attention to word choice and correct spelling.</div>
            </div>
        </div>

        <v-btn
            tile
            block
            class="blue_prim"
            :dark="!blocked || newDir.name ? true : false"
            :disabled="blocked || !newDir.name"
            v-text="'save'"
            @click="createNewDir"
        />
    </small-dialog>

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
            upload: false,
            newDir: {
                show: false,
                name: null,
                directory: null
            },
            blocked: false
        }
    },

    props: {
        dialog:     { type: Boolean, default: false },
        routedPath: { type: String, default: null },
        select:     { type: Boolean, default: false },
        selected:   { type: String, default: null },
        identifier: { type: String, default: null }
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
                this.marked = []
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
            if (this.identifier) this.$emit('select', { key: this.identifier, path: emit })
            else this.$emit('select', emit)
            if (confirm(emit + ' has been selected. Close Dialog?')) ++this.closing
        },

        // New Directory
        addDirectory () {
            this.newDir = {
                show: true,
                name: null,
                directory: this.currentDir
            }
        },

        printNewDirName () {
            let name = this.newDir.name
            if (!name) return '--'
            name = name
                .toLowerCase()
                .replaceAll(/\s+/g, ' ')
                .replaceAll(' ', '_')
                .replaceAll('ö', 'oe')
                .replaceAll('ü', 'ue')
                .replaceAll('ä', 'ae')
                .replaceAll(/[^0-9a-z_]/g, '-')
            return name ?? '--'
        },

        async createNewDir () {
            const url = this.$root.baseURL + '/storage-api/action/create'

            this.blocked = this.$root.loading = true
            await axios.post(url,
                Object.assign ({}, {
                    name: this.newDir.name,
                    directory: this.newDir.directory
                }))
                .then(async (response) => {
                    const data = response?.data
                    if (data.success && data.path) {
                        this.$store.dispatch('showSnack', {
                            color: 'success',
                            message: data.name + ' was ' + (data.existing ? 'already existing' : 'created')
                        })
                        await this.$store.dispatch('fetchDirectories')
                        this.setCurrentPath(data.path)
                        this.newDir.show = false
                    }
                    else console.error(response)
                })
                .catch((error) => {
                    console.error(error.message)
                    this.$root.AXIOS_ERROR_HANDLER(error)
                })
            this.blocked = this.$root.loading = false
        }
    }
}

</script>
