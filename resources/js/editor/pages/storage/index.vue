<template>
<div class="d-flex">

    <!-- Tree -->
    <div style="width: 220px; z-index: 101" class="mr-5">
        <directories
            :currentDir="currentDir"
            v-on:setPath="setCurrentPath"
        />
    </div>

    <div style="width: 100%">

        <!-- Toolbar -->
        <v-card tile raised class="pl-2 d-flex align-center grey_sec" :height="50" style="z-index:100">

            <!-- Breadcrumbs-->
            <breadcrumbs
                :directory="currentDir"
                :height="50"
                v-on:setPath="setCurrentPath"
            />
            <v-spacer />

            <!-- Upload -->
            <upload :directory="currentDir" v-on:update="setFileIndex">
                <template v-slot:activator="slot">
                    <adv-btn
                        icon="cloud_upload"
                        :tooltip="'upload Files to current Directory (/' + currentDir + ')'"
                        :disabled="!currentDir || directoryLoading"
                        v-on:click="slot.controls.active = true"
                    />
                </template>
            </upload>

            <!-- Refresh -->
            <adv-btn
                icon="sync"
                :loading="$store.state.storage.files.loading"
                :disabled="!currentDir || directoryLoading"
                tooltip="Refresh File Index"
                v-on:click="setFileIndex()"
            />

            <!-- Search -->
            <div class="pl-2 pr-3" :style="'width:' + searchInputWidth + 'px'">
                <v-text-field
                    dense clearable
                    v-model="searchFile"
                    placeholder="Dateien filtern"
                    class="mb-n3"
                />
            </div>
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

        </v-card>

        <div style="width: 100%">
            <galery
                :currentDir="currentDir"
                :currentFile="currentFile"
                :search="searchFile"
                :selected="selectedFiles"
                v-on:setPath="setCurrentPath"
                v-on:selectFile="selectFile"
            />
        </div>
    </div>

</div>
</template>


<script>
import directories from './modules/directories.vue'
import breadcrumbs from './modules/breadcrumbs.vue'
import galery from './modules/galery.vue'
import upload from './modules/upload.vue'

export default {
    components: {
        directories,
        galery,
        breadcrumbs,
        upload
    },

    data () {
        return {
            currentDir: false,
            currentFile: false,
            searchFile: null,
            selected: [],
        }
    },

    props: {
    },

    computed:{
        directoryLoading () { return this.$store.state.storage.directory?.loading },
        directories ()      { return this.$store.state.storage.directory?.items },
        searchInputWidth () {
            if (this.$vuetify.breakpoint.lgAndUp) return 250
            if (this.$vuetify.breakpoint.mdAndUp) return 200
            return 100
        },
        selectedFiles () {
            const selected = this.selected

            if (this.currentFile && !this.selected.includes(this.currentFile)) selected.push(this.currentFile)

            return selected.sort()
        }
    },

    watch: {
        $route (to, from) {
            this.getRoute()
        },
        directories () {
            if (this.directories?.[0]) this.getRoute()
        }
    },

    created() {
        this.$store.dispatch('fetchDirectories')
    },

    methods: {
        setCurrentPath (path) {
            this.searchFile = null
            if (path) {
                if (!path.startsWith('/')) path = '/' + path
                if (path.slice(-1) === '/') path = path.slice(0, -1)

                if (this.$route.params.pathMatch === path) this.setFileIndex()
                else this.$router.push('/storage' + path)
            }
            else if (this.$route.path !== '/storage') this.$router.push('/storage')
        },
        getRoute () {
            let path = this.$route.params.pathMatch

            if (path) {
                this.searchFile = null
                if (path.startsWith('/') && path.length > 1) {
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
                        this.$router.push('/storage')
                    }
                }
                else this.$router.push('/storage')
            }
            else {
                this.currentDir = null
                this.currentFile = null
            }
        },

        setFileIndex (dir) {
            this.$store.dispatch('fetchFiles', { directory: dir ?? this.currentDir })
        },

        selectFile (path, push = false) {
            const index = this.selected.indexOf(path)

            if (push) {
                if (index > 0) {
                    if (this.selected.length > 1) this.selected.splice(index, 1)
                    else this.selected = []
                }
                else this.selected.push(path)
            }

            else {
                this.selected = []
                if (this.$route.params.pathMatch === '/' + path) {
                    path = path.split('/')
                    path.pop()
                    this.$router.replace('/storage/' + path.join('/'))
                }
                else this.$router.replace('/storage/' + path)
            }
        },

        shiftSelection (step) {
            let index = this.selected.indexOf(this.currentFile)
            if (step === -1 && index === 0) index = this.selected.length - 1
            else if (step === 1 && index === this.selected.length - 1) index = 0
            else index += step

            this.$router.replace('/storage/' + this.selected[index])
            //const path = '/storage/' + files[index]

            //this.$router.replace({ path, query: { selected: this.$route.query.selected }})
        }
    }
}

</script>
