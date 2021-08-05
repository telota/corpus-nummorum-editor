<template>
<small-dialog
    :show="show"
    background="tile_bg"
    no-padding
    v-on:close="close()"
>
    <div class="pa-3">
        <!-- File Input -->
        <v-file-input dense outlined clearable
            v-model="file"
            placeholder="Select a file on your PC"
            prepend-icon="desktop_windows"
            :show-size="1000"
        />

        <!-- Author -->
        <v-text-field dense outlined disabled
            v-model="meta.author"
            label="Author"
            prepend-icon="person"
            hint="not yet supported"
            persistent-hint
            counter=120
        />

        <!-- Copyright -->
        <v-text-field dense outlined disabled
            v-model="meta.copyright"
            label="Copyright"
            prepend-icon="copyright"
            hint="not yet supported"
            persistent-hint
            counter=120
                />

        <div class="mt-4">
            <p>
                Select a file and upload it to the server.<br />
                Directory: <strong>/{{ directory }}</strong>
            </p>
            <p>
                The maximum file size allowed is <strong>{{ Math.min($root.system.maxPost, $root.system.maxUpload, $root.system.memoryLimit) }}&nbsp;MB</strong>.
            </p>
        </div>
    </div>

    <!-- Upload Button -->
    <v-btn
        tile
        block
        large
        :dark="file ? true : false"
        class="title"
        :class="uploading ? 'red' : 'blue_prim'"
        v-text="'upload'"
        :loading="uploading"
        :disabled="!file"
        style="height: 40px;"
        @click="() => { if (uploading) { abortUpload() } else upload() }"
    />
</small-dialog>
</template>


<script>
export default {
    data () {
        return {
            target_default: 'uploads',
            file:           null,
            meta:           {
                author: null,
                copyright: null
            },
            feedback:       null,
            uploading:      false
        }
    },

    props: {
        show:       { type: Boolean, default: false },
        directory:  { type: String, default: 'uploads' },
        identifier: { type: String, default: 'image' }
    },

    methods: {

        async upload () {
            let success = false
            var self = this

            this.$root.loading = this.uploading = true

            const formData = new FormData()
            formData.append('directory', this.directory)
            formData.append ('file', this.file)
            formData.append('meta', JSON.stringify(this.meta))

            await axios.post('/storage-api/action/upload', formData)
                .then(function (response) {
                    const data = response?.data?.[0]

                    if (data?.success) {
                        console.log('upload successful', self.identifier, data)
                        self.$emit('upload', { key: self.identifier, path: data.path } )
                        self.$store.dispatch('showSnack', { color: 'success', message: data.path + ' uploaded' })
                        success = true
                    }
                    else {
                        console.error('Upload Error', response)
                        self.$store.dispatch('showSnack', { color: 'error', message: 'Upload Error!' })
                    }
                })
                .catch(function (error) { self.$root.AXIOS_ERROR_HANDLER(error) })

            this.$root.loading = this.uploading = false;

            if (success) self.close()
        },

        close () {
            this.file = null
            this.$emit('close', true)
        },
    }
}

</script>
