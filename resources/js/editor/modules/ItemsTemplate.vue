<template>
    <div>
        <v-card tile :loading="loading.active">

            <!-- JK: Header -->
            <v-app-bar color="sysbar">

                <v-spacer></v-spacer>

                <!-- JK: Pagination -->
                <div class="mt-1">
                    <v-btn color="sysbar" depressed small
                        @click="SetOffset (0)" 
                        :disabled="server.offset > 0 ? false : true"
                        :text="server.offset > 0 ? false : true"
                        :tile="server.offset > 0 ? true: false"
                    > <v-icon>first_page</v-icon> </v-btn>

                    <v-btn color="sysbar" depressed small
                        @click="SetOffset (server.offset - server.limit)" 
                        :disabled="server.offset > 0 ? false : true"
                        :text="server.offset > 0 ? false : true"
                        :tile="server.offset > 0 ? true : false"
                    > <v-icon>navigate_before</v-icon> </v-btn>

                    <!-- JK: Page Jumper -->
                    <v-menu offset-y :close-on-content-click="false" transition="scale-transition">
                        <template v-slot:activator="{ on: menu }">
                            <v-tooltip bottom>
                            <template v-slot:activator="{ on: tooltip }">
                                <v-btn v-on="{ ...tooltip, ...menu }" class="caption" color="sysbar" depressed small
                                    :disabled="server.count < server.limit" 
                                    :text="server.count < server.limit"
                                    :tile="server.count >= server.limit"
                                >
                                    {{ server.count == 0 ? '0 / 0' : ( (Math.ceil(server.offset / server.limit) +1) +' / '+ Math.ceil(server.count / server.limit) ) }}
                                </v-btn>
                            </template>
                            <span>Go to specific page</span>
                            </v-tooltip>
                        </template>
                        <v-card>
                            <v-text-field dense clearable autofocus
                                v-model="page_jump"
                                v-on:input="SetOffset (page_jump > 0 ? ((page_jump-1) * server.limit) : 0)"
                                hint="jump to page" persistent-hint
                            ></v-text-field>
                        </v-card>                       
                    </v-menu>

                    <v-btn color="sysbar" depressed small
                        @click="SetOffset (server.offset + server.limit)" 
                        :disabled="server.offset < server.count - server.limit ? false : true"
                        :text="server.offset < server.count - server.limit ? false : true"
                        :tile="server.offset < server.count - server.limit ? true : false" 
                    > <v-icon>navigate_next</v-icon> </v-btn>

                    <v-btn color="sysbar" depressed small
                        @click="SetOffset ( (Math.ceil (server.count / server.limit) -1) * server.limit)" 
                        :disabled="server.offset < server.count - server.limit ? false : true"
                        :text="server.offset < server.count - server.limit ? false : true"
                        :tile="server.offset < server.count - server.limit ? true : false"
                    > <v-icon>last_page</v-icon> </v-btn>
                </div>

                <v-spacer></v-spacer>

            </v-app-bar>

            <!-- JK: Toolbar -->
            <v-card tile class="appbar mb-2">
                <div class="component-wrap d-flex">

                    <!-- JK: Limit -->
                    <v-menu offset-y transition="scale-transition">
                        <template v-slot:activator="{ on: menu }">
                            <v-tooltip bottom>
                            <template v-slot:activator="{ on: tooltip }">
                                <v-btn v-on="{ ...tooltip, ...menu }" tile depressed>
                                    {{ server.limit }}
                                </v-btn>
                            </template>
                            <span>Set number of items per page</span>
                            </v-tooltip>
                        </template>
                        <v-list>
                            <v-list-item v-for="(item, i) in limits" :key="i" @click="SetLimit (item)" :class="server.limit == item ? 'font-weight-black' : ''">
                                <v-list-item-title>{{ item }}</v-list-item-title>
                            </v-list-item>
                        </v-list>
                    </v-menu>

                    <!-- JK: Order by -->
                    <v-menu offset-y transition="scale-transition">
                        <template v-slot:activator="{ on: menu }">
                            <v-tooltip bottom>
                            <template v-slot:activator="{ on: tooltip }">
                                <v-btn v-on="{ ...tooltip, ...menu }" tile depressed>
                                    <v-icon class="mr-1">sort_by_alpha</v-icon>
                                </v-btn>
                            </template>
                            <span>Order Items</span>
                            </v-tooltip>
                        </template>
                        <v-list>
                            <v-list-item v-for="(item, i) in sorters" :key="i" @click="OrderBy (item.value)" :class="server.sort_by == item.value ? 'font-weight-black' : ''">
                                <v-list-item-title>{{ item.text }} <span class="ml-1">{{ server.sort_by == item.value ? (server.sort_by_op == 'ASC' ? '&darr;' : '&uarr;') : '&uarr;'}}</span></v-list-item-title>
                            </v-list-item>
                        </v-list>
                    </v-menu>
                    
                    <!-- JK: Highlight Published -->
                    <v-tooltip bottom v-if="prop_attributes.public != undefined">
                        <template v-slot:activator="{ on }">
                            <v-btn v-on="on" @click="highlight = !highlight" tile depressed>
                                <v-icon>{{ highlight ? 'star_outline' : 'star' }}</v-icon>
                            </v-btn>
                        </template>
                        <span>{{ highlight ? 'Hide publication status' : 'Highlight publication status' }}</span>
                    </v-tooltip>
                    
                    <!-- JK: Toggle Cards -->
                    <v-tooltip bottom v-if="cards">
                        <template v-slot:activator="{ on }">
                            <v-btn v-on="on" @click="cards_active = !cards_active" tile depressed>
                                <v-icon>{{ cards_active ? 'list' : 'view_comfy'}}</v-icon>
                            </v-btn>
                        </template>
                        <span>View items {{ cards_active ? 'in table' : ' as cards'}}</span>
                    </v-tooltip>
                                
                    <!-- JK: Cards Scale down -->
                    <v-tooltip bottom v-if="cards_active">
                        <template v-slot:activator="{ on }">
                            <v-btn v-on="on" @click="cards_scale = cards_scale -1" depressed
                                :disabled="cards_scale < 1 ? true : false" 
                                :text="cards_scale < 1 ? true : false"
                                :tile="cards_scale < 1 ? false : true"
                            > <v-icon>remove</v-icon>
                            </v-btn>
                        </template>
                        <span>Scale down cards</span>
                    </v-tooltip>
                    
                    <!-- JK: Cards Scale up -->
                    <v-tooltip bottom v-if="cards_active">
                        <template v-slot:activator="{ on }">
                            <v-btn v-on="on" @click="cards_scale = cards_scale +1" depressed
                                :disabled="cards_scale > 1 ? true : false" 
                                :text="cards_scale > 1 ? true : false"
                                :tile="cards_scale > 1 ? false : true"
                            > <v-icon>add</v-icon>
                            </v-btn>
                        </template>
                        <span>Scale up cards</span>
                    </v-tooltip>

                    <!-- JK: Cards Preview -->
                    <v-menu offset-y v-if="cards_active && (prop_img == 'coins')" transition="scale-transition">
                        <template v-slot:activator="{ on: menu }">
                            <v-tooltip bottom>
                            <template v-slot:activator="{ on: tooltip }">
                                <v-btn v-on="{ ...tooltip, ...menu }" tile depressed>
                                    <span class="ml-1 mt-n1">
                                        {{ cards_preview === 0 ? '&#9679;&#9679;' : (cards_preview === 1 ? '&#9679;&#9675;' : '&#9675;&#9679;') }}
                                    </span>
                                </v-btn>
                            </template>
                            <span>Change preview mode</span>
                            </v-tooltip>
                        </template>
                        <v-list>
                            <v-list-item v-for="(item, i) in cards_previews" :key="i" @click="cards_preview = item.value">
                                <v-list-item-title>{{ item.text }}</v-list-item-title>
                            </v-list-item>
                        </v-list>
                    </v-menu>

                    <v-spacer></v-spacer>

                    <!-- JK: First Filter (depending on array) -->
                    <div style="max-width: 50 px">
                        <v-text-field dense clearable 
                            v-if="!$root.preferences.show_filters"
                            v-model="filters [0] ['input']"
                            :placeholder="'Search '+filters [0] ['text']"
                            hide-details
                            class="ml-3"
                            v-on:input="Search ()"
                        ></v-text-field>
                    </div>

                    <!-- JK: Clear Filters -->
                    <v-tooltip bottom v-if="$root.preferences.show_filters">
                        <template v-slot:activator="{ on }"> 
                            <v-btn v-on="on" @click="SearchClear ()" tile depressed>
                                <v-icon>clear</v-icon>
                            </v-btn>
                        </template>
                        <span>Clear all filters</span>
                    </v-tooltip>

                    <!-- JK: Toggle Filter -->
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on }"> 
                            <v-btn v-on="on" @click="$root.preferences.show_filters = !$root.preferences.show_filters" tile depressed>
                                <v-icon small>search</v-icon><span class="ml-2 mt-n1">{{ $root.preferences.show_filters ? '&#9650;' : '&#9660;' }}</span>
                            </v-btn>
                        </template>
                        <span>{{ $root.preferences.show_filters ? 'Hide' : 'Show' }} all filters</span>
                    </v-tooltip>

                </div>
            </v-card>

            <!-- JK: Filters -->
            <v-container v-if="$root.preferences.show_filters" transition="scale-transition" class="mt-n3 ml-1 mr-1">
                <v-row>

                    <v-col v-for="(item, i) in filters" :key="i" cols=12 sm=6 md=4 lg=4 xl=3>
                        <div class="component-wrap d-flex">
                            <v-text-field dense outlined filled clearable
                                v-model="item.input"
                                :label="item.text"
                                hide-details
                                v-on:input="Search ()"
                            ></v-text-field>

                            <ScreenKeys v-if="item.sk" class="ml-1"
                                :prop_layout="item.sk"
                                :prop_var="i"
                                prop_name="input"
                                v-on:KeyClicked="ScreenKeysReceive"
                            ></ScreenKeys>
                        </div>
                    </v-col>

                </v-row>
            </v-container>

            <!-- JK: Body -->
            <v-card-text class="ma-0 pa-0">

                <!-- JK: Data Table -->
                <div id="DataTableSync" v-if="items.length > 0 && sync && !cards_active">              
                    <v-data-table :dense="server.limit > table_dense"
                        :items="items"
                        hide-default-header
                        hide-default-footer
                    >
                        <!-- JK: Data Table Header -->
                        <template v-slot:header>
                            <thead>
                                <tr>
                                    <th v-for="(item, i) in headers" :key="i" >
                                        <v-hover v-if="item.sortable">
                                            <div @click="OrderBy (item.value)" slot-scope="{ hover }" :class="`${hover ? 'invert--text': ''}`">
                                                {{ item.text }} 
                                                <span v-if="item.value == server.sort_by" class="ml-1">{{ server.sort_by_op == 'ASC' ? '&uarr;' : '&darr;' }}</span>
                                            </div>
                                            
                                        </v-hover>
                                        <div v-if="!item.sortable">
                                            {{ item.text }}
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                        </template>

                        <!-- JK: Data Table Body -->  
                        <template v-slot:body>
                            <tbody>
                                <tr v-for="(item, i) in items" :key="i" @click="ItemSelect (item)" :class="(highlight ? (item.public == 1 ? 'green--text': 'red--text') : '')+(item.id == selected ? ' primary' : '')">
                                    <td v-for="(header, i) in headers" :key="i">{{ item [header.value] }}</td>
                                </tr>
                            </tbody>
                        </template>             
                    
                    </v-data-table>
                </div>
                
                <!-- JK: Data Cards -->
                <div id="DataCards" v-if="items.length > 0 && sync && cards_active">
                    <v-row class="pr-5 pl-5 pt-3 pb-3">

                        <v-col v-for="(item, i) in items" :key="i" cols="12"
                            :sm="cards_scales [cards_scale] ['sm']" 
                            :md="cards_scales [cards_scale] ['md']" 
                            :lg="cards_scales [cards_scale] ['lg']"
                        >
                            <v-hover>
                                <template v-slot:default="{ hover }" >
                                    <v-card @click="ItemSelect (item)" tile hover :style="(item.id == selected) ? 'border: 2px solid #01579b;' : ''">
                                        
                                        <v-card-text class="appbar">
                                            <!-- Image -->
                                            <Imager coin square hide_text
                                                :item="item"
                                                :key="item.id"
                                                :hide_obverse="cards_preview == 2"
                                                :hide_reverse="cards_preview == 1"
                                            ></Imager>

                                            <!-- Name -->
                                            <div class="mt-4 text-center caption">{{ item.id }}</div>
                                            <!--<v-simple-table dense>
                                                <template v-slot:default>
                                                    <tr v-for="(detail, i) in headers" :key="i">
                                                        <td>{{ detail.text }}</td>
                                                        <td class="text-truncate">{{ item [detail.value] }}</td>
                                                    </tr>
                                                </template>
                                            </v-simple-table>-->
                                        </v-card-text>

                                        <v-fade-transition>
                                            <v-overlay v-if="hover" absolute color="blue lighten-4"></v-overlay>
                                        </v-fade-transition>
                                    </v-card>
                                </template>
                            </v-hover>
                        </v-col>

                    </v-row>
                </div>

                <!-- JK: Data Table -->
                <div id="DataTable" v-if="items_all.length > 0 && !sync">              
                    <v-data-table hide-default-footer 
                        @click:row="RowClick" single-select
                        :dense="server.limit > table_dense"
                        :items="items_all"
                        :headers="headers"
                        :items-per-page="server.limit"
                        :page.sync="page_current"
                        :sort-by.sync="sort.by"
                        :sort-desc.sync="sort.desc"
                        @page-count="server.count = $event * server.limit"
                    ></v-data-table>
                </div>

                <!-- Placeholder for empty Items Array -->
                <div v-if="items.length == 0 && items_all.length == 0" class="mt-10 mb-10 headline text-center" width="100%">
                    <div class="pb-10">No matching results</div>
                </div>

            </v-card-text>

            <!-- Pagination Bottom -->
            <v-card color="appbar" class="text-center mt-4">
                
                <v-btn color="appbar" depressed small
                    @click="SetOffset (0)" 
                    :disabled="server.offset > 0 ? false : true"
                    :text="server.offset > 0 ? false : true"
                    :tile="server.offset > 0 ? true: false"
                > <v-icon>first_page</v-icon> </v-btn>

                <v-btn color="appbar" depressed small
                    @click="SetOffset (server.offset - server.limit)" 
                    :disabled="server.offset > 0 ? false : true"
                    :text="server.offset > 0 ? false : true"
                    :tile="server.offset > 0 ? true : false"
                > <v-icon>navigate_before</v-icon> </v-btn>

                <!-- JK: Page Jumper -->
                <v-menu offset-y :close-on-content-click="false" transition="scale-transition">
                    <template v-slot:activator="{ on: menu }">
                        <v-tooltip bottom>
                        <template v-slot:activator="{ on: tooltip }">
                            <v-btn v-on="{ ...tooltip, ...menu }" class="caption" color="appbar" depressed small
                                :disabled="server.count < server.limit" 
                                :text="server.count < server.limit"
                                :tile="server.count >= server.limit"
                            >
                                {{ server.count == 0 ? '0 / 0' : ( (Math.ceil(server.offset / server.limit) +1) +' / '+ Math.ceil(server.count / server.limit) ) }}
                            </v-btn>
                        </template>
                        <span>Go to specific page</span>
                        </v-tooltip>
                    </template>
                    <v-card>
                        <v-text-field dense clearable autofocus
                            v-model="page_jump"
                            v-on:input="SetOffset (page_jump > 0 ? ((page_jump-1) * server.limit) : 0)"
                            hint="jump to page" persistent-hint
                        ></v-text-field>
                    </v-card>                       
                </v-menu>

                <v-btn color="appbar" depressed small
                    @click="SetOffset (server.offset + server.limit)" 
                    :disabled="server.offset < server.count - server.limit ? false : true"
                    :text="server.offset < server.count - server.limit ? false : true"
                    :tile="server.offset < server.count - server.limit ? true : false" 
                > <v-icon>navigate_next</v-icon> </v-btn>

                <v-btn color="appbar" depressed small
                    @click="SetOffset ( (Math.ceil (server.count / server.limit) -1) * server.limit)" 
                    :disabled="server.offset < server.count - server.limit ? false : true"
                    :text="server.offset < server.count - server.limit ? false : true"
                    :tile="server.offset < server.count - server.limit ? true : false"
                > <v-icon>last_page</v-icon> </v-btn>

            </v-card>
        
        </v-card>
    </div>
</template>



<script>

export default
{ 
    data ()  // JK: define VueJS Variables
    {
        return {
            entity:             this.prop_entity,
            sync:               this.prop_sync ? true : false,
            //digilib:            {url: 'https://digilib.bbaw.de/digitallibrary/servlet/Scaler?fn=silo10/thrakien/', param: '&dw=500&dh=500'},

            items:              [],     // JK: small array of items for rendering
            items_all:          [],     // JK: If sync is false all server data will be stored in this array
            search:             {},
            selected:           this.prop_item.id,

            loading:            {active: false, text: 'Waiting for Data'},
            limits:             this.$store.state.ItemsPerPage,
            page_current:       1,
            page_jump:          null,
            filters:            [],

            table_dense:        12,
            
            cards:              this.prop_sync ? this.prop_cards : false,
            cards_active:       false,            
            cards_preview:       0,
            cards_previews:      [
                {value: 0, text: 'Obverse and Reverse'},
                {value: 1, text: 'Obverse only'},
                {value: 2, text: 'Reverse only'}
            ],
            cards_scale:         1,
            cards_scales:        this.$store.state.ItemsScales,

            highlight:          false,

            // server communication
            server:               {
                sort_by:        'id',
                sort_by_op:     'DESC',
                count:          0,
                offset:         0,
                limit:          12,
            },
        }
    },


    props: 
    {
        prop_entity:            {type: String},     // JK: this entity will be requested from Server
        prop_attributes:        {type: Object},     // JK: Arrays for Rendering
        prop_sync:              {type: Boolean},    // JK: If true data will be fetched partly
        prop_cards:             {type: Boolean},    // JK: If true provide alternative Display as Tiles
        prop_img:               {type: String},
        prop_item:              {type: Object},     // JK: Selected Item's ID
        prop_refresh:           {type: Number},     // JK: if value changes a DataOuput will be called by watch
        
        //prop_title:             {type: String},     // JK: Title for template
        //prop_img_is_coin:       {type: Boolean},    // JK: If true component will look for "img_o" and "img_r"
    },


    computed: 
    {
        /*filters () { // JK: Create filters from attributes
            var self = this;
            let items = [];

            Object.keys (self.prop_attributes) .forEach (function (key) {
                if (self.prop_attributes[key].filterable) {
                    items.push ({
                        value:  key, 
                        text:   self.prop_attributes[key].text, 
                        sk:     self.prop_attributes[key].sk,
                        input:  null
                    });
                }
            });

            return items;
        },*/

        headers () { // JK: Create headers from attributes
            var self = this;
            let items = []
            
            if (this.sync) 
            {
                Object.keys (self.prop_attributes) .forEach (function (key) {
                    if (self.prop_attributes[key].header) {
                        items.push ({
                            value:      key, 
                            text:       self.prop_attributes[key].text, 
                            sortable:   self.prop_attributes[key].sortable
                        });
                    }
                });
            } 
            else 
            {
                Object.keys (self.prop_attributes) .forEach (function (key) {
                    if (self.prop_attributes[key].header || self.prop_attributes[key].filterable) {
                        items.push ({
                            value:      key, 
                            text:       self.prop_attributes[key].text, 
                            sortable:   self.prop_attributes[key].sortable, 
                            align:      (self.prop_attributes[key].header ? 'left' : ' d-none'),
                            filter:     value => { 
                                if (key == 'id') // Key is ID
                                {
                                    if (!self.search.id) { return true } else { return value == parseInt (self.search.id) }
                                } 
                                else if (key == 'combination') // Key relates to Monogramms Combination
                                {
                                    if (!self.search [key]) { return true } else { 
                                        if (!value) {return false;} else {
                                            let match = ''; 
                                            self.search [key].toUpperCase().split('').forEach (function (char) {
                                                match = match + (!value.toUpperCase().includes (char) ? 0 : 1);
                                            })
                                            return !match.includes (0);                                                
                                        }
                                    }
                                } 
                                else // Any other Key
                                {
                                    if (!self.search [key]) { return true } else { return !value ? false : value.toUpperCase().includes(self.search [key].toUpperCase()) }
                                }
                            }
                        });                        
                    }
                });
            }

            return items;
        },

        sorters () {  // JK: Create sorters from attributes
            var self = this;
            let items = []

            Object.keys (self.prop_attributes) .forEach (function (key) {
                if (self.prop_attributes[key].sortable) {
                    items.push ({
                        value:      key, 
                        text:       self.prop_attributes[key].text
                    });
                }
            });

            return items;
        },

        sort () { // JK: only for non-sync table 
            return {by: this.server.sort_by, desc: this.server.sort_by_op == 'DESC' ? true : false};
        }
    },


    watch:
    {
        prop_refresh: function () {
            if (this.prop_refresh) {
                console.log ('Items Template: Refresh was requested ('+this.prop_refresh+')');                 
                this.DataOutput ();
                this.selected = this.prop_refresh > 0 ? this.prop_refresh : null;
            }
        }
    },


    created () 
    {
        var self = this;
        this.SetFilters ();

        self.$store.commit ('showLoader'); // JK: Loading screen ON

        if (self.sync) 
        {
            if (self.prop_item.id > 0) { 
                self.server.id = self.prop_item.id;
                self.DataOutput ()
                    .then (function () {
                        self.$emit('select', self.items [0]);
                        self.server.id = null;
                        self.DataOutput ();
                    })
            } else {            
                self.DataOutput ();
            }
        } 
        else 
        {
            self.DataOutput ()
                .then (function () {
                    if (self.prop_item.id > 0) {
                        let selected = self.items_all.find ( (item) => item.id === self.prop_item.id );
                        self.$emit('select', selected);
                    }
                })
        }
        
        self.$store.commit ('hideLoader'); // JK: Loading screen OFF
    },

    
    methods: 
    {
        // Screen Keys
        ScreenKeysReceive (emit) {
                //console.log ('SK: '+Object.entries (emit))
            if (emit.val == 'DELETE_ALL') {
                this.filters [emit.var] ['input'] = null;
            } else if (emit.val == 'DELETE_LAST') {
                this.filters [emit.var] ['input'] = this.filters [emit.var] ['input']   ?   this.filters [emit.var] ['input'].substring(0, this.filters [emit.var] ['input'].length - 1)    :   null;
            } else {
                this.filters [emit.var] ['input'] = this.filters [emit.var] ['input']   ?   this.filters [emit.var] ['input'] + emit.val  :   emit.val;
            }

            this.Search ();
        },
        
        // Select and Emit
        ItemSelect: function (item) {
            this.selected = item.id;
            this.$emit('select', item); // JK: Child-Parent-Communication
                console.log ('Items Template emitted selected item (ID: '+item.id+')')
        },

        RowClick: function (item, row) { // JK: only for non-sync table
                // console.log (row);
            row.select (true);
            this.ItemSelect (item);
        },

        // Items Handlers
        SetFilters () {
            var self = this;
            let items = [];

            Object.keys (self.prop_attributes) .forEach (function (key) {
                if (self.prop_attributes[key].filterable) {
                    items.push ({
                        value:  key, 
                        text:   self.prop_attributes[key].text, 
                        sk:     self.prop_attributes[key].sk,
                        input:  null
                    });
                }
            });

            this.filters = items;
        },

        SetOffset (input) {
            this.server.offset = input > 0 ? input : 0;

            if (this.sync) { this.DataOutput (); }
            else { this.page_current = Math.floor (this.server.offset / this.server.limit) +1; }            
        },

        SetLimit (input) {            
            this.server.limit = input;
            if (this.sync) { this.DataOutput (); }
        },

        Search () {
            var self = this;

            self.search = {}; //JK: reset search object
            
            self.filters.forEach (function (item) { // JK: wright search strings to search array
                self.search [item.value] = item.input;
            })
                //console.log ('new Search: '+Object.entries (self.search));

            for (let [key, value] of Object.entries(self.search)) {
                if (value) {
                    //console.log(`${key}: ${value}`);
                    self.server [key] = value;
                }
            }            

            this.SetOffset (0); // JK: reset offset to prevent pagination issues
            if (this.sync) { this.DataOutput (); }
        },

        SearchClear () {
            var self = this;
            
            self.filters.forEach (function (item, i) {
                self.filters [i] ['input'] = null;
                self.search [item.value] = null;
            })

            this.Search ();
        },

        OrderBy (item) {
            console.log (item);
            if (item == this.server.sort_by) {
                this.server.sort_by_op = this.server.sort_by_op != 'ASC' ? 'ASC' : 'DESC';
            } else {
                this.server.sort_by = item;
                this.server.sort_by_op = 'ASC';
            }
            
            if (this.sync) { this.DataOutput (); }
        },
        
        // Get Data from Server
        async DataOutput () {
            this.loading.active = true; // JK: Loading ON

            let source = 'dbi/' +this.entity;            


            // JK: Sync is true: dbi/ will provide only a part (fast, but high DB traffic) -> use for big data (more than 1.000 items)
            if (this.sync)
            {
                console.log ('Items Template: sent partial fetch request to "' + source + '" (sync: true) - response:');

                const params = this.server
                params.sort_by = params.sort_by + '.' + params.sort_by_op

                let input = Object.assign ({}, params);
                let ServerResponse = [];

                await axios.post(source, input).then(function(response) 
                {
                    console.log (response);
                    ServerResponse = response.data;
                })

                const sort_explode = ServerResponse.pagination.sort_by.split(' ')
                ServerResponse.pagination.sort_by = sort_explode[0]
                ServerResponse.pagination.sort_by_op = sort_explode[1]

                this.server = ServerResponse.pagination;                       // JK: server returns operational parameters in [2] (important for pagination)
                //this.search  = ServerResponse [1];                    // JK: server returns search parameters in [1] (not needed at the moment)
                this.items  = Object.values (ServerResponse.contents);       // JK: server returns requested items in [2] (rendered in table and cards)

                console.log (this.items.length+' items received');
            }
                        
            // JK: Sync is false: dbi/ will provide all data (slow, but low DB traffic) -> use for small data (less than 1.000 items)
            else
            {
                console.log ('Items Template: sent full fetch request to "' + source + '" (sync: false) - response:');

                let dbi = await axios.get (source);          
                this.items_all = Object.values (dbi.data?.contents);

                this.server.count = this.items_all.length;

                console.log (this.server.count+' items received');
            }
            
            this.loading.active = false; // JK: Loading OFF      
        }
    }
}

</script>
