<template>
<div>

    <!-- Activator -->
    <slot name="activator" v-bind:controls="controls">
        <advbtn
            icon="cloud_upload"
            tooltip="upload Files to current Directory"
            :disabled="controls.disabled"
            v-on:click="controls.active = true"
        />
    </slot>

    <!-- Dialog -->
    <small-dialog
        :show="controls.active"
        icon="cloud_upload"
        :text="'Upload to \'/' + dir + '\''"
        width="90%"
        :max-width="800"
        no-padding
        :z-index="301"
        v-on:close="controls.active = false"
    >
        <div style="position: relative; height: calc(100vh - 190px); width: 100%;">
            <v-card tile class="header_bg d-flex" style="height: 40px;">

                <div
                    class="caption d-flex align-center justify-center"
                    style="position: absolute; width: 100%; height: 40px;"
                    v-html="'<div>Maximale Größe pro Datei: <b>' + maxSize.value + '&nbsp;MB</b></div>'"
                />

                <!-- File Browser -->
                <adv-btn
                    icon="folder_open"
                    tooltip="open FIle Browser"
                    medium
                    @click="openFileManager()"
                />

                <!-- Clear -->
                <adv-btn
                    icon="clear"
                    tooltip="Clear Upload Queue"
                    medium
                    :disabled="!files[0]"
                    @v-on:click="files = []"
                />

                <!-- File Input -->
                <input
                    id="file-input"
                    type="file"
                    name="file-input"
                    accept=".jpg, .jpeg, .png"
                    multiple
                    style="
                        width: 0.1px;
                        height: 0.1px;
                        opacity: 0;
                        overflow: hidden;
                    "
                    @input="setFiles()"
                >

                <v-spacer />

                <!-- Upload History -->
                <v-menu
                    tile
                    offset-y
                    :close-on-content-click="false"
                    left
                >
                    <!-- Activator -->
                    <template v-slot:activator="{ on, attrs }">
                        <v-btn
                            tile
                            depressed
                            color="transparent"
                            v-bind="attrs"
                            v-on="on"
                            style="height: 40px;"
                        >
                            <div
                                class="mr-5"
                                v-html="
                                    uploadStats.done.count + ' / ' + uploadStats.total.count +
                                    (uploadStats.errors ? (', ' + uploadStats.errors + '&nbsp;Fehler') : '')
                                "
                            />
                            <v-progress-circular
                                v-if="uploading.active"
                                size=20
                                indeterminate
                            />
                            <v-icon v-else v-text="'history'" />
                        </v-btn>
                    </template>

                    <!-- Upload History Dropdown -->
                    <v-card tile :width="500">
                        <!-- current Upload -->
                        <template v-if="uploading.active">
                            <div class="d-flex align-center pa-1">
                                <v-progress-circular
                                    size=20
                                    :value="uploading.percentage"
                                />
                                <div v-text="uploading.file" class="caption text-truncate ml-2 font-weight-bold" />
                            </div>
                            <v-divider />
                        </template>

                        <!-- Upload History -->
                        <template v-if="uploaded[0]">
                            <div style="height: 300px; max-height: 50vH; overflow-y: auto; overflow-x: hidden;">
                                <div
                                    v-for="(item, i) in uploaded"
                                    :key="i + item.file"
                                    class="d-flex align-start pa-1"
                                >
                                    <v-icon
                                        :class="item.error ? 'red--text' : 'green--text'"
                                        v-text="item.error ? 'error_outline' : 'task_alt'"
                                    />
                                    <div class="caption ml-2 text-truncate">
                                        <b v-html="item.newName ? item.newName : item.file" /><br/>
                                        ({{ item.file }})
                                    </div>
                                </div>
                            </div>
                            <v-divider />
                            <div
                                class="pa-2 text-center caption"
                                style="cursor: pointer"
                                v-text="'Clear Upload History'"
                                @click="uploaded = []"
                            />
                        </template>

                        <div
                            v-else
                            v-text="'No FIle History'"
                            class="caption text-center grey--text pt-10 pb-10"
                        />
                    </v-card>
                </v-menu>


            </v-card>

            <!-- Body -->
            <div
                class="app_bg"
                style="height: calc(100vh - 350px); position: relative;"
                @drop.prevent="dropFile"
                @dragover.prevent
            >
                <!-- Start -->
                <v-overlay
                    :value="!files[0]"
                    absolute
                >
                    <div
                        class="text-center title"
                        style="cursor: pointer"
                        v-text="'Open File Manager'"
                        @click="openFileManager()"
                    />
                    <div
                        class="text-center body-2 font-weight-bold mt-3"
                        style="cursor: default"
                        v-text="'or drag & drop Files'"
                    />
                </v-overlay>

                <!-- Loading -->
                <v-overlay
                    :value="uploading.active"
                    absolute
                >
                    <v-progress-circular
                        :size="250"
                        :value="uploadProgress.percentage"
                    >
                        <div class="text-center">
                        <div class="title" v-html="uploadProgress.percentage + '&nbsp;%'" />
                        <div class="body-2" v-html="uploadProgress.string" />
                        </div>
                    </v-progress-circular>
                </v-overlay>

                <!-- List -->
                <v-virtual-scroll
                    v-if="files[0]"
                    :items="files"
                    :item-height="120"
                    style="width: 100%; overflow-y: scroll;"
                    :dis-bench="3"
                >
                    <template v-slot:default="{ item, index }">
                        <v-card tile class="grey_sec" style="padding:10px; margin: 10px 15px 10px 15px">
                            <div class="d-flex">
                                <v-responsive class="grey lighten-3 d-flex align-center" width="90" aspect-ratio="1">
                                    <v-img
                                        v-if="item.preview"
                                        :src="item.preview"
                                        contain
                                        width="90"
                                        height="90"
                                    />
                                    <div
                                        v-else
                                        class="font-weight-bold body-1 text-center"
                                        style="width: 90px;"
                                        v-text="item.name.split('.').pop().toUpperCase()"
                                    />
                                </v-responsive>

                                <div class="ml-5" style="width: 100%">
                                    <div v-text="item.name" class="font-weight-bold body-1 text-truncate" />
                                    <div v-html="item.meta.type + ', ' + printFileSize(item.size)" />

                                    <div class="d-flex justify-center mt-2" v-if="uploading.active">
                                        <v-progress-linear
                                            height="1px"
                                            :value="item.name === uploading.file ? uploading.percentage : 0"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <v-icon v-text="'cancel'" @click="files.length > 1 ? files.splice(index, 1) : files = []" />
                                </div>
                            </div>
                        </v-card>
                    </template>
                </v-virtual-scroll>

            </div>

            <!-- Footer -->
            <div class="header_bg" style="height: 120px;">
                <!-- Upload Options -->
                <div class="pa-2" style="height: 80px;">

                    <!-- Options
                    <div class="d-flex flex-wrap">
                        <v-checkbox class="mr-2" label="Dateien gleichen Namens ersetzen" />
                        <v-checkbox class="mr-2" label="Bei Fehlern erneut versuchen" />
                    </div> -->

                    <!-- Breadcrumbs -->
                    <breadcrumbs
                        :directory="directory"
                        disabled
                    />
                </div>

                <!-- Upload Button -->
                <v-btn
                    tile
                    block
                    large
                    :dark="files[0] ? true : false"
                    class="title"
                    :class="uploading.active ? 'red' : 'blue_prim'"
                    v-text="'upload'"
                    :disabled="!files[0]"
                    style="height: 40px;"
                    @click="() => { if (uploading.active) { abortUpload() } else uploadData() }"
                />
            </div>

        </div>
    </small-dialog>

</div>
</template>


<script>
import breadcrumbs from './breadcrumbs.vue'

export default {
    components: {
        breadcrumbs,
    },

    data () {
        return {
            controls: {
                active: false,
                disabled: false
            },
            fullscreen: false,
            loading: false,
            files: [],
            meta: [],
            dirIsPublic: false,

            uploading: {
                active: false,
                value: 0,
                percentage: 0,
                size: 0,
                file: null
            },
            uploaded: [],
            uploadStats: {
                total: { size: 0, count: 0 },
                done: { size: 0, count: 0 },
                errors: 0
            },
            menuWidth: '350px'
        }
    },

    props: {
        directory: { type: [String, Boolean], default: null }
    },

    computed: {
        dir: {
            get: function () { return this.directory ? this.directory.trim('/') : 'uploads' },
            set: function (value) { this.$emit('setPath', value) }
        },

        maxSize () {
            const system = this.$root.system
            const value = Math.min(system.maxPost, system.maxUpload, system.memoryLimit)

            return {
                value,
                hint: system.maxPost === value && system.memoryLimit === value ? true : false
            }
        },
        uploadProgress () {
            let percentage = 0
            let string = '0 / 0&nbsp;MB'

            if (this.uploadStats.total.size) {
                // Percentage
                const current   = this.uploading?.value > 0 ? this.uploading.value : 0
                const sizeTotal = this.uploadStats?.total?.size > 0 ? this.uploadStats.total.size : 0
                const sizeDone  = this.uploadStats?.done?.size > 0 ? this.uploadStats.done.size : 0

                const value     = (sizeDone + current) / sizeTotal * 100
                percentage      = !value ? 0 : (value > 100 ? 100 : (Math.round(value * 10 ) / 10))

                // String (done/total in Byte)
                let fac = 1
                let unit = 'B'
                if (sizeTotal > 1000000000) { fac = 1000000000; unit = 'TB' }
                else if (sizeTotal > 1000000) { fac = 1000000; unit = 'MB' }
                else if (sizeTotal > 1000) { fac = 1000; unit = 'KB' }

                string = Math.ceil((sizeDone + current) / fac) + ' / ' + Math.ceil(sizeTotal / fac) + '&nbsp;' + unit
            }

            return {
                percentage,
                string
            }
        }
    },

    methods: {
        constructAxiosURL (path, id = null) {
            path = path.trim()
            if (path.slice(0, 4) !== 'http') path = this.$root.baseURL + (path.slice(0, 1) === '/' ? '' : '/') + path
            if (path.slice(-1) === '/') path = path.slice(0, -1)
            if (id) path += (path.slice(0, 1) === '/' ? '' : '/' ? '/' : '') + id
            return path
        },

        async uploadData () {
            const self = this
            const files = this.files?.[0] ? this.files : []

            if (files[0]) {
                const url = this.constructAxiosURL('storage-api/action/upload')
                let count = 0
                let size = this.uploadStats.total.size = this.uploadStats.done.size = 0

                // Get Stats
                files.map((file, i) => {
                    ++count
                    size += file.size
                })

                this.uploadStats.total.count += count
                this.uploadStats.total.size = size

                // Iterate and send Files
                const payload = await files.map(async (item, i) => {

                    // Create Data for transfer
                    const data = new FormData()
                    data.append('directory', this.dir)
                    data.append('file', item.file)
                    data.append('meta', JSON.stringify(item.meta))

                    this.uploading = {
                        active: true,
                        value: 0,
                        percentage: 0,
                        size: item.size,
                        file: item.name,
                    }

                    // Axios
                    await axios.post(url, data, {
                        onUploadProgress: (progress) => {
                            self.uploading.value = progress.loaded
                            self.uploading.percentage = Math.floor(progress.loaded / progress.total * 100)
                        }
                    })
                    .then((response) => {
                        console.log('response', response)
                        response = response.data?.[0]
                        self.setUploaded(item, response.error, response.name)
                    })
                    .catch(error => {
                        console.log('error', error?.response?.data ?? error);
                        self.setUploaded(item, true)
                    });

                    return i
                })

                await Promise.all(payload)

                // Update Parent-Component
                this.$emit('update', this.dir)
            }
        },

        setUploaded (item, error, newName = null) {
            this.uploading = {
                active: false,
                value: 0,
                percentage: 0,
                size: 0,
                file: null
            }

            ++this.uploadStats.done.count
            this.uploadStats.done.size += item.size
            if (error) ++this.uploadStats.errors

            this.uploaded.unshift({
                file: item.name,
                newName,
                size: item.size,
                time: 0,
                error
            })

            if (!error) {
                const index = this.files.map((el) => el.name).indexOf(item.name);
                if (this.files.length > 1) {
                    this.files.splice(index, 1)
                    this.meta?.splice(index, 1)
                }
                else this.files = this.meta = []
            }
        },

        dropFile (e) {
            if (!this.uploading?.active) {
                const files = e.dataTransfer.files
                if (files) {
                    ([...files]).forEach((file) => {
                        this.pushToFiles(file)
                    })
                }
            }
        },

        openFileManager () {
            document.getElementById('file-input').click()
        },

        setFiles (clear) {
            this.uploadStats.total.size = this.uploadStats.done.size = 0
            const data = this.getFilesFromInput()
            if (clear) this.files = []

            else if (data) {
                for (var i = 0; i < data.length; i++) {
                    this.pushToFiles(data[i])
                }
            }
        },

        getFilesFromInput () {
            const element = document.getElementById('file-input');
            return element?.files?.[0] ? element.files : []
        },

        pushToFiles (file) {
            const name = file.name
            const maxSize = this.maxSize.value
            const sizeMB = Math.ceil(file.size / 100000) / 10

            // File is too large
            if (sizeMB > maxSize) alert(name + ' is ' + sizeMB + ' MB large. The maximum allowed is ' + maxSize + ' MB. This File will be ignored!')
            // File is already selected
            else if (this.files.find((inArray) => inArray.name === name)) alert(name + ' is already selected. This File will be ignored!')

            else {
                const previewAvailable = ['jpg', 'jpeg', 'gif', 'svg', 'png'].includes(name.split('.').pop().toLowerCase()) ? true : false

                this.files.push({
                    name,
                    size: file.size,
                    file,
                    preview: previewAvailable ? URL.createObjectURL(file) : false,
                    meta: {
                        originalFileName: file.name,
                        size: file.size,
                        type: file.type,
                        modified: null,
                        email: this.$root.user.email,
                        public: this.dirIsPublic
                    }
                })
            }
        },

        printFileSize (size) {
            if (size > 1000000) return (Math.ceil(size / (1024 * 1024) * 10) / 10) + '&nbsp;MB'
            if (size > 1000) return (Math.ceil(size / 1024 * 10) / 10) + '&nbsp;KB'
            return size + '&nbsp;B'
        }
    }
}

</script>
