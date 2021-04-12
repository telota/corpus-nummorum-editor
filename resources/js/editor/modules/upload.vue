<template>
<div id="upload">                            

    <!-- Upload Dialog -->
    <v-dialog v-model="this.prop_active" persistent :width="width">
        <v-card tile>

            <v-system-bar color="sysbar" class="pt-5 pb-4 pl-5">
                <v-icon class="mr-2">cloud_upload</v-icon> <span>File Upload</span>
                <v-spacer></v-spacer>
                <v-icon @click="close ()">close</v-icon>
            </v-system-bar>

            <v-card-text class="mt-5">
                <v-row>
                    <v-col cols="12" sm="12" md="12"> 
                        <!-- File Input -->
                        <v-file-input dense outlined
                            v-model="file"
                            placeholder="Select a file on your PC"
                            prepend-icon="desktop_windows"
                            :show-size="1000"
                        ></v-file-input>
                    </v-col>
                    <v-col cols="12" sm="12" class="mt-n3">
                        <!-- Owner -->
                        <v-text-field dense outlined disabled
                            v-model="metadata ['owner']"
                            label="Owner"
                            prepend-icon="person"
                            hint="not yet supported" persistent-hint
                            counter=120
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="12" class="mt-n5 mb-2">
                        <!-- Copyright -->
                        <v-text-field dense outlined disabled
                            v-model="metadata ['copyright']"
                            label="Copyright"
                            prepend-icon="copyright"
                            hint="not yet supported" persistent-hint
                            counter=120
                        ></v-text-field>
                    </v-col>
                </v-row>
                <div class="mt-4">
                    <p>Select a file and upload it to the server.<br />
                    Directory: <strong>{{ target }}</strong></p> 
                    <p>The maximum file size allowed is <strong>{{ limit }}</strong>. The upload process might take some time depending on file size and connection quality.</p>
                </div>
            </v-card-text>

            <v-card-actions class="mt-n5">
                <v-spacer></v-spacer>

                <v-tooltip bottom>
                    <template v-slot:activator="{ on }"> 
                        <v-btn v-on="on" @click="close ()" icon  class="mr-3">
                            <v-icon>clear</v-icon>
                        </v-btn>
                    </template>
                    <span>Cancel</span>
                </v-tooltip>

                <v-tooltip bottom>
                    <template v-slot:activator="{ on }"> 
                        <v-btn v-on="on" @click="upload ()" icon>
                            <v-icon>done</v-icon>
                        </v-btn>
                    </template>
                    <span>Upload file to server</span>
                </v-tooltip>

                <v-spacer></v-spacer>
            </v-card-actions>
        </v-card>
    </v-dialog>

</div>
</template>



<script>


export default
{
    data () 
    {
        return {            
            width:          425,
            target_default: 'storage/uploads',

            limit:          '10 MB',
            file:           [],
            metadata:       {owner: null, copyright: null},

            feedback:       null,
        }
    },

    props: 
    {
        prop_active:        {type: Boolean},
        prop_target:        {type: String},
        prop_key:           {type: String},
    },
    
    computed: 
    {
        target () {
            return this.prop_target ? this.prop_target : this.target_default;
        }
    },

    methods: 
    {
        async upload () {
            var self = this;

            self.$root.loading = true;

            let response = null;
            let url = 'dbi/files/upload/' + self.target;

            let formData = new FormData()
            formData.append ('file', this.file)

            console.log ('AXIOS: POST Input to "'+url+'". Awaiting Server Response ...');            

            await axios.post (url, formData)
                .then  (function (response) {
                    
                    if (response.data.success)
                    {
                        self.$emit('ChildEmit', {key: self.prop_key, url: response.data.url} );
                        self.close (); 
                        console.log ('RESPONSE CHECK: Server accepted valid input:');
                    }
                    else
                    {
                        self.$root.error = { active: true, validation: response.data.error, };
                        console.log ('RESPONSE CHECK: Server declined invalid input:');
                    }
                    console.log (response);
                })
                .catch (function (error) { self.$root.AXIOS_ERROR_HANDLER (error); });
            
            self.$root.loading = false;
        },

        close () {
            this.file = null;
            this.$emit('close', true);
        }, 
    }
}

</script>
