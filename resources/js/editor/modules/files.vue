<template>
    <div>
        <v-row>
                
            <v-col cols="12" sm="12" md="9">
                <v-card tile :loading="loading">

                    <!-- JK: Pagination (top) -->
                    <v-app-bar color="sysbar">

                        <v-spacer></v-spacer>
                        
                        <!-- JK: Pagination -->
                        <div class="mt-1">
                            <v-btn color="sysbar" depressed small
                                @click="SetOffset (0)" 
                                :disabled="offset > 0 ? false : true"
                                :text="offset > 0 ? false : true"
                                :tile="offset > 0 ? true: false"
                            > <v-icon>first_page</v-icon> </v-btn>

                            <v-btn color="sysbar" depressed small
                                @click="SetOffset (offset - limit)" 
                                :disabled="offset > 0 ? false : true"
                                :text="offset > 0 ? false : true"
                                :tile="offset > 0 ? true : false"
                            > <v-icon>navigate_before</v-icon> </v-btn>

                            <!-- JK: Page Jumper -->
                            <v-menu offset-y :close-on-content-click="false" transition="scale-transition">
                                <template v-slot:activator="{ on: menu }">
                                    <v-tooltip bottom>
                                    <template v-slot:activator="{ on: tooltip }">
                                        <v-btn v-on="{ ...tooltip, ...menu }" class="caption" color="sysbar" depressed small
                                            :disabled="count < limit" 
                                            :text="count < limit"
                                            :tile="count >= limit"
                                        >
                                            {{ count == 0 ? '0 / 0' : ( (Math.ceil(offset / limit) +1) +' / '+ Math.ceil(count / limit) ) }}
                                        </v-btn>
                                    </template>
                                    <span>Go to specific page</span>
                                    </v-tooltip>
                                </template>
                                <v-card>
                                    <v-text-field dense clearable autofocus
                                        v-model="page_jump"
                                        v-on:input="SetOffset (page_jump > 0 ? ((page_jump-1) * limit) : 0)"
                                        hint="jump to page" persistent-hint
                                    ></v-text-field>
                                </v-card>                       
                            </v-menu>

                            <v-btn color="sysbar" depressed small
                                @click="SetOffset (offset + limit)" 
                                :disabled="offset < count - limit ? false : true"
                                :text="offset < count - limit ? false : true"
                                :tile="offset < count - limit ? true : false" 
                            > <v-icon>navigate_next</v-icon> </v-btn>

                            <v-btn color="sysbar" depressed small
                                @click="SetOffset ( (Math.ceil (count / limit) -1) * limit)" 
                                :disabled="offset < count - limit ? false : true"
                                :text="offset < count - limit ? false : true"
                                :tile="offset < count - limit ? true : false"
                            > <v-icon>last_page</v-icon> </v-btn>
                        </div>                       

                        <v-spacer></v-spacer>

                    </v-app-bar>

                    <!-- JK: Toolbar -->
                    <v-card tile class="appbar">
                        <v-row class="ma-0 pa-0">
                            
                            <v-col cols="12" sm="4" class="ma-0 pa-0">
                                <div class="component-wrap d-flex">
                                    <!-- JK: Limit -->
                                    <v-menu offset-y transition="scale-transition">
                                        <template v-slot:activator="{ on: menu }">
                                            <v-tooltip bottom>
                                            <template v-slot:activator="{ on: tooltip }">
                                                <v-btn v-on="{ ...tooltip, ...menu }" tile depressed>
                                                    {{ limit }}
                                                </v-btn>
                                            </template>
                                            <span>Set number of items per page</span>
                                            </v-tooltip>
                                        </template>
                                        <v-list>
                                            <v-list-item v-for="(item, i) in limits" :key="i" @click="SetLimit (item)" :class="limit == item ? 'font-weight-black' : ''">
                                                <v-list-item-title>{{ item }}</v-list-item-title>
                                            </v-list-item>
                                        </v-list>
                                    </v-menu>
                                
                                    <!-- JK: Cards Scale down -->
                                    <v-tooltip bottom>
                                        <template v-slot:activator="{ on }">
                                            <v-btn v-on="on" @click="scale = scale -1" depressed
                                                :disabled="scale < 1 ? true : false" 
                                                :text="scale < 1 ? true : false"
                                                :tile="scale < 1 ? false : true"
                                            > <v-icon>remove</v-icon>
                                            </v-btn>
                                        </template>
                                        <span>Scale down cards</span>
                                    </v-tooltip>
                                    
                                    <!-- JK: Cards Scale up -->
                                    <v-tooltip bottom>
                                        <template v-slot:activator="{ on }">
                                            <v-btn v-on="on" @click="scale = scale +1" depressed
                                                :disabled="scale > 1 ? true : false" 
                                                :text="scale > 1 ? true : false"
                                                :tile="scale > 1 ? false : true"
                                            > <v-icon>add</v-icon>
                                            </v-btn>
                                        </template>
                                        <span>Scale up cards</span>
                                    </v-tooltip>

                                    <!-- JK: Upload -->
                                    <v-tooltip bottom>
                                        <template v-slot:activator="{ on }"> 
                                            <v-btn tile depressed v-on="on" v-on:click="UploadDialog ()">
                                                <v-icon>cloud_upload</v-icon>
                                            </v-btn>
                                        </template>
                                        <span>Upload file to server</span>
                                    </v-tooltip>
                                </div>
                            </v-col>

                            <!-- Server Directory -->
                            <v-col cols="12" sm="4" class="ma-0 pa-0 pl-5 pr-5">
                                <div class="component-wrap d-flex">
                                    <v-autocomplete dense class="mb-n3"
                                        v-model="directory_search"
                                        :placeholder="directory"
                                        v-on:change="DirectorySearch ()"
                                        :items="list"
                                        prepend-icon="cloud_queue"
                                        no-data-text="no matching directory found"
                                        :disabled="prop_item ? (prop_item.parent ? true : false) : false"
                                    ></v-autocomplete>
                                </diV>
                            </v-col>

                            <!-- File Search -->
                            <v-col cols="12" sm="4" class="ma-0 pa-0 pl-5 pr-5">                          
                                <v-autocomplete dense class="mb-n3"
                                    v-model="item_search"
                                    v-on:change="FileSearch (item_search, 'name')"
                                    :items="items ? items.map (item => {return item.name}) : []"
                                    :disabled="items ? false : true"
                                    placeholder="Search"
                                    prepend-icon="search"
                                    no-data-text="no matching file found"
                                ></v-autocomplete>
                            </v-col>
                        </v-row>
                    </v-card>

                    <!-- File Tiles -->
                    <v-card-text>        
                            <v-row>

                                <v-col v-if="count == 0" cols=12 sm=12 class="text-center headline mt-10 mb-8">
                                    {{ loading ? 'Waiting for content ...' : 'Sorry, no files in "'+directory+'"' }}
                                </v-col>

                                <v-col v-for="(item, i) in tiles_items" :key="i" 
                                    :cols=12 
                                    :sm="scales [scale] ['sm']" 
                                    :md="scales [scale] ['md']" 
                                    :lg="scales [scale] ['lg']"
                                >
                                    <!-- Image Tile -->
                                    <v-hover>
                                        
                                        <template v-slot:default="{ hover }" >
                                            <v-card @click="ItemSelect (item)" tile hover :style="(item_selected && item.name == item_selected.name) ? 'border: 2px solid #01579b;' : ''">
                                                <v-card-text class="appbar">                                                    
                                                    <Imager contain hide_text raised
                                                        :item="item"
                                                        :key="item.name"
                                                        name="url"
                                                    ></Imager>
                                                    <div class="mt-4 text-center caption text-truncate">{{ item.name }}</div>
                                                </v-card-text>

                                                <v-fade-transition>
                                                    <v-overlay v-if="hover" absolute color="blue lighten-4"></v-overlay>
                                                </v-fade-transition>
                                            </v-card>
                                        </template>
                                        
                                    </v-hover>
                                </v-col>

                            </v-row>          
                    </v-card-text>

                    <!-- Pagination Bottom -->
                    <v-card color="appbar" class="text-center mt-4">

                        <v-btn color="appbar" depressed small
                            @click="SetOffset (0)" 
                            :disabled="offset > 0 ? false : true"
                            :text="offset > 0 ? false : true"
                            :tile="offset > 0 ? true: false"
                        > <v-icon>first_page</v-icon> </v-btn>

                        <v-btn color="appbar" depressed small
                            @click="SetOffset (offset - limit)" 
                            :disabled="offset > 0 ? false : true"
                            :text="offset > 0 ? false : true"
                            :tile="offset > 0 ? true : false"
                        > <v-icon>navigate_before</v-icon> </v-btn>

                        <!-- JK: Page Jumper -->
                        <v-menu offset-y :close-on-content-click="false" transition="scale-transition">
                            <template v-slot:activator="{ on: menu }">
                                <v-tooltip bottom>
                                <template v-slot:activator="{ on: tooltip }">
                                    <v-btn v-on="{ ...tooltip, ...menu }" class="caption" color="appbar" depressed small
                                        :disabled="count < limit" 
                                        :text="count < limit"
                                        :tile="count >= limit"
                                    >
                                        {{ count == 0 ? '0 / 0' : ( (Math.ceil(offset / limit) +1) +' / '+ Math.ceil(count / limit) ) }}
                                    </v-btn>
                                </template>
                                <span>Go to specific page</span>
                                </v-tooltip>
                            </template>
                            <v-card>
                                <v-text-field dense clearable autofocus
                                    v-model="page_jump"
                                    v-on:input="SetOffset (page_jump > 0 ? ((page_jump-1) * limit) : 0)"
                                    hint="jump to page" persistent-hint
                                ></v-text-field>
                            </v-card>                       
                        </v-menu>

                        <v-btn color="appbar" depressed small
                            @click="SetOffset (offset + limit)" 
                            :disabled="offset < count - limit ? false : true"
                            :text="offset < count - limit ? false : true"
                            :tile="offset < count - limit ? true : false" 
                        > <v-icon>navigate_next</v-icon> </v-btn>

                        <v-btn color="appbar" depressed small
                            @click="SetOffset ( (Math.ceil (count / limit) -1) * limit)"
                            :disabled="offset < count - limit ? false : true"
                            :text="offset < count - limit ? false : true"
                            :tile="offset < count - limit ? true : false"
                        > <v-icon>last_page</v-icon> </v-btn>

                    </v-card>

                </v-card>
            </v-col>

            <!-- File Details -->
            <v-col cols="12" sm="6" md="3">
                <v-card tile :loading="loading.active"> 

                    <v-app-bar color="sysbar title">
                        <span v-if="!item_selected">No file selected</span>
                        <div v-if="item_selected" class="text-truncate">
                            <v-tooltip top>
                                <template v-slot:activator="{ on }">
                                    <span v-on="on">{{ item_selected.name }}</span>
                                </template>
                                <span>{{ item_selected.name }}</span>
                            </v-tooltip>
                        </div>
                    </v-app-bar>

                    <v-card tile class="appbar component-wrap d-flex">
                        <!-- Edit Metadata -->
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }"> 
                                <v-btn v-on="on" v-on:click="meta_dialog = true" color="appbar" depressed
                                    :disabled="!item_selected ? true : false"
                                    :text="!item_selected ? true : false"
                                    :tile="item_selected ? true : false"                                
                                > <v-icon>list_alt</v-icon> </v-btn>
                            </template>
                            <span>Edit metadata</span>
                        </v-tooltip>
                        <!-- Digilib -->
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <a :href="item_selected ? ('https://digilib.bbaw.de/digitallibrary/servlet/Scaler?fn=silo10/thrakien/'+item_selected.url.substring(8)+'&dh=750&dw=750') : ''" target="_blank">
                                    <v-btn v-on="on" color="appbar" depressed
                                        :disabled="!item_selected ? true : false"
                                        :text="!item_selected ? true : false"
                                        :tile="item_selected ? true : false" 
                                    > <v-icon>open_in_new</v-icon> </v-btn>
                                </a>
                            </template>
                            <span>Open in Digilib</span>
                        </v-tooltip>
                        <!-- Download File -->
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }"> 
                                <a :href="item_selected ? item_selected.url : ''" DOWNLOAD>
                                    <v-btn v-on="on" color="appbar" depressed
                                        :disabled="!item_selected ? true : false"
                                        :text="!item_selected ? true : false"
                                        :tile="item_selected ? true : false" 
                                    > <v-icon>save_alt</v-icon> </v-btn>
                                </a>
                            </template>
                            <span>Download file</span>
                        </v-tooltip>
                        <v-spacer></v-spacer>
                        <!-- Delete File -->
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }"> 
                                <v-btn v-on="on" v-on:click="Delete ()" color="appbar" depressed
                                    :disabled="!item_selected ? true : false"
                                    :text="!item_selected ? true : false"
                                    :tile="item_selected ? true : false" 
                                > <v-icon>delete</v-icon> </v-btn>
                            </template>
                            <span>Delete file</span>
                        </v-tooltip>
                    </v-card>
                    
                    <v-card-text>
                        <v-card tile raised class="appbar mb-2">
                            <v-card-text v-if="!item_selected" class="text-center pt-10 pb-10"><p class="mb-10">Image Preview</p>No File selected, yet</v-card-text>
                            
                            <Imager v-if="item_selected" 
                                square contain hide_text
                                :item="item_selected"
                                name="url"
                            ></Imager>

                        </v-card>

                        <div v-if="item_selected">
                            <div class="title mt-6 mb-2">Meta Data</div>
                            <v-simple-table dense>
                                <template v-slot:default>
                                    <tr v-for="(detail, key, i) in meta" :key="i">
                                        <td><b>{{ meta [key].text }}</b></td>
                                        <td>{{ item_selected [key] }}</td>
                                    </tr>
                                </template>
                            </v-simple-table>
                        </div>
                    </v-card-text>

                </v-card>
            </v-col>           
                
        </v-row>

        <!-- Upload Dialog -->
        <upload
            :prop_active="upload_active"
            :prop_target="upload_target"
            v-on:ChildEmit="UploadReceive"
            v-on:close="upload_active=false"
        ></upload> 

    </div>
</template>


<script>

// JK: Set stable variables (outside of vue component scope)
const CompLabelS            = 'File';
const CompLabelP            = 'Files';
const CompLabelParent       = 'References';
const CompName              = 'files';

const entity                = 'uploads';


export default
{
    data () 
    {
        return {
            loading:            false,

            directory:          'storage/'+(this.prop_item ? (this.prop_item.parent ? this.prop_item.parent : entity)  : entity),
            directory_search:   null,
            
            list:               [],

            items:              [],
            item_selected:      null,
            item_search:        null,

            offset:             0,
            limit:              12,
            limits:             this.$store.state.ItemsPerPage,
            page_current:       1,
            page_jump:          null,

            scales:             [
                {sm: 3, md: 2, lg: 1, text: 'small'},
                {sm: 4, md: 3, lg: 2, text: 'medium'},
                {sm: 6, md: 6, lg: 4, text: 'large'}
            ],
            scale:              1,

            meta:               {
                name:       {text: 'Name'},
                owner:      {text: 'Owner'},
                copyright:  {text: 'Copyright'},
                url:        {text: 'URL'},
            },
            meta_dialog:        false,

            // Controls for Upload Dialog
            upload_active:      false,
            upload_target:      null,
        }
    },

    props: 
    {
        prop_item: {type: Object}
    },
    
    computed: 
    {
        count () {
            return this.items.length;
        },

        tiles_items () {
            var self = this; 
            let items_comp = [];
            
            if (self.items) 
            {
                self.items.forEach (function (item, i) 
                {
                    if ( i >= self.offset && i < (self.offset + self.limit) ) 
                    {
                        //console.log (self.items[i].url)
                        items_comp.push (item);
                    }
                })
            }

            return items_comp;
        },
    },

    created()
    {
        this.$store.commit( 'setBreadcrumbs',[ // JK: Set Breadcrumbs
            {label:CompLabelParent,to:''},
            {label:CompLabelP,to:''},
        ]);
            
        if (this.prop_item) // if file is set and exists extract directory from file 
        {      
            if (this.prop_item.id) {      
                let path        = this.prop_item.id.split('/'); path.pop ();
                this.directory  = path.join ('/');
                if (this.directory.substring(0,8) != 'storage/') {this.directory = 'storage/'+this.directory;}
            }
        }

        var self = this;

        this.DataOutput () // JK: Get data from server
            .then(function() {if (self.prop_item) {self.Receiver (); } }) // JK: Parent-Child-Communication        
    },

    methods: 
    {
        // JK: Upload Dialog
        UploadDialog () { // JK: Call upload dialog
            //console.log (this.directory)
            this.upload_target = this.directory;
            //console.log (this.upload_target)
            this.upload_active = true;
        },

        UploadReceive (emit) { // JK: receive emitted Input from upload dialog
            var self = this;
            this.DataOutput ()
                .then(function() { self.FileSearch (emit.url, 'url') })
            this.upload_active = false;
        },

        // JK: Child-Parent-Communication
        Receiver () { // JK: Process properties given by parent (in most cases Child Dialog Component)
                //console.log ('prop_item {key: '+this.prop_item.key+', id: '+this.prop_item.id+'}');
            if (this.prop_item.id) {
                    console.log ('selected: '+this.prop_item.id);
                //let selected = this.items.find ( (item) => item.id === this.prop_item.id );
                this.FileSearch (this.prop_item.id, 'url')
            }
        },

        // JK: Files
        SetOffset (input) {
            this.offset = input > 0 ? input : 0;
        },

        SetLimit (input) {            
            this.limit = input;
        },

        DirectorySearch () {
            this.directory = this.directory_search;
            this.DataOutput ();
        },

        async DataOutput () { // JK: Function to get data from server
            this.loading = true; // JK: Loading ON

            this.items = []; // JK: reset Items

            var self = this;

            let dbi = await axios.get ('dbi/files/browse/storage');
            self.list = Object.values (dbi.data);

            let found = false;

            self.list.forEach (function (directory)  // JK: Check if selected directory exists in directory list
            {
                if (self.directory == directory) {
                    found = true
                } 
            })

            if (found) 
            { 
                dbi = await axios.get ('dbi/files/browse/'+self.directory);
                self.items = Object.values (dbi.data);
            } 
            else 
            {
                alert ('(404) Directory "'+self.directory+'" could not be found. It might have been deleted or renamed.'); // You will be redirected to "'+entity+'".'
                //self.directory = 'storage/'+entity;
                //self.RecursiveReload ();
            }

            this.loading = false; // JK: Loading OFF
        },

        RecursiveReload () {
            this.DataOutput ();
        },

        FileSearch (file, key) {  // JK: look for item in items by url and set offset
            this.item_search = null;
            this.item_selected = this.items.find ( (item) => file === item [key] );
            
            if (this.item_selected) { // check if selected item really exists (if given by prop_item)
                this.ItemSelect (this.item_selected);
                let index = this.items.indexOf (this.item_selected);

                if (index < this.offset || index >= (this.offset + this.limit)) 
                {
                    this.offset = Math.floor (index / this.limit) * this.limit;
                }
            } else {
                alert('(404) File "'+file+'" could not be found. It might have been deleted or renamed.');
            }
        },

        ItemSelect (item) {
            this.item_selected = item;
            let id = item.url.substring(0,8) == 'storage/' ? item.url.substring(0,8) : item.url;
            this.$emit('ChildEmit', {component: entity, id: id} );
        },
        
        async Delete () {

            let confirmed   = confirm ('Are you sure you want to delete '+this.item_selected.name+'?'); 

            if (confirmed == true) 
            {
                var self = this;

                self.$root.loading = true;

                let response = null;
                let url = 'dbi/files/delete/file';

                console.log ('AXIOS: POST Input to "'+url+'". Awaiting Server Response ...');            

                await axios.post (url, Object.assign ({}, {file: this.item_selected.url}))
                    .then  (function (response) {
                        
                        if (response.data.success)
                        {
                            self.item_selected  = null;
                            self.DataOutput ();
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
            } 
            else {
                console.log ('Deletion: declined.');
            }   
        },
    }
}

</script>