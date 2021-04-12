<template>
<div>
                    
    <div style="position: relative; display: inline-block; width: 100%">

        <!-- JK: Text Field -->
        <div>
            <v-text-field
                v-on:click="expand = true"
                v-on:input="() => { if (sync) { Refresh (true) }}"
                dense outlined filled
                v-model="search"
                :label="label"
                :prepend-icon="icon ? icon : 'label'"
                :placeholder="select ? ('( ' + select + ' ) ' + SelectString (select)) : ''"
                :disabled="disabled"
                :hint="disabled ? 'Value inherited from type' : ''"
                :persistent-hint="disabled"
            >
                <template v-slot:append>
                    <div 
                        class="mt-1 mr-5" 
                        style="white-space: nowrap;" 
                        v-text="select ? '( ' + select + ' )' : ''"
                    ></div>
                    <v-icon
                        v-text="expand ? 'expand_less' : 'expand_more'"
                        @click="expand = !expand"
                    ></v-icon>
                </template>

            </v-text-field>
        </div>


        <!-- JK: Expanded Menu ------------------------------------------------------------------------------------ -->
        <v-card tile 
            v-if="expand && !disabled"
            :loading="loading"
            class="d-flex component-wrap mb-8 mt-n3" 
            style="display: block; position: absolute; z-index: 1"
            width="100%"
        >
            <!-- JK: Left Panel: Controls -->
            <div>
                <advbtn
                    color_main="transparent"
                    icon="launch"
                    :tooltip="'Open '+label+' Dialog'"
                    v-on:click="dialog=true"
                ></advbtn>
                <advbtn
                    color_main="transparent"
                    icon="clear"
                    tooltip="Clear Input"
                    v-on:click="search = null"
                ></advbtn>
                <advbtn
                    color_main="transparent"
                    icon="horizontal_rule"
                    tooltip="Clear Value"
                    v-on:click="SelectItem (null)"
                ></advbtn>
                <advbtn
                    color_main="transparent"
                    icon="refresh"
                    :tooltip="'Refresh '+label+' List'"
                    v-on:click="Refresh (true)"
                ></advbtn>
                <advbtn
                    color_main="transparent"
                    icon="keyboard"
                    :tooltip="keyboard ? 'Hide Keyboard' : 'Show Keyboard'"
                    :key="keyboard"
                    v-on:click="keyboard = !keyboard"
                ></advbtn>
            </div> <v-divider vertical></v-divider>
            
            <!-- JK: Right Panel: Input -->
            <div style="width: 100%">

                <!-- JK: Screen Keyboard -->
                <div v-if="keyboard">
                    <div class="d-flex flex-wrap">

                        <advbtn v-for="key in sk_keys" v-bind:key="key.value"
                            color_main="transparent"
                            :text="key.value"
                            :tooltip="key.text"
                            v-on:click="SKinput (key.value)"
                        ></advbtn>

                        <div class="d-flex flex-wrap">
                            <advbtn
                                color_main="transparent"
                                text="&blank;"
                                tooltip="Blank Space"
                                v-on:click="SKinput (' ')"
                            ></advbtn>
                            <advbtn
                                color_main="transparent"
                                text="&larr;"
                                tooltip="Delete last Sign"
                                v-on:click="SKinput ('DELETE_LAST')"
                            ></advbtn>
                            <advbtn
                                color_main="transparent"
                                text="&Cross;"
                                tooltip="Delete all Signs"
                                v-on:click="SKinput ('DELETE_ALL')"
                            ></advbtn>
                        </div>
                    </div>
                    <v-divider></v-divider>
                </div>

                <!-- JK: Entity List -->
                <div>
                    <div v-if="!items[0]" class="body-2 pa-5">{{loading ? 'Loading List ...' : 'Sorry, no matching Items'}}</div>

                    <v-responsive
                        class="overflow-y-auto"
                        min-height="250"
                        max-height="300"
                    >                        
                        <v-lazy
                            v-model="lazy"
                            :options="{ threshold: .5 }"
                            transition="fade-transition"
                            class="fill-height"
                        ><!--min-height="200"-->
                            <v-list dense class="transparent">
                                <v-list-item v-for="(item, i) in items" :key="i" @click="SelectItem(item.id);">
                                    <v-list-item-avatar tile v-if="item.img" width="50px" class="imgbg pa-2">
                                        <v-img :src="item.img"></v-img>
                                    </v-list-item-avatar>
                                    <v-list-item-content>
                                        ( {{ item.id }} ) &ensp; {{ item.string }}
                                    </v-list-item-content>
                                </v-list-item>
                            </v-list>
                        </v-lazy>
                    </v-responsive>

                </div>
                
            </div>
        </v-card>

    </div>
        

    <!-- JK: Child Dialog -->
    <ChildDialog 
        v-if="dialog"
        :prop_active="dialog"
        :prop_component="entity"
        :prop_item="{ key: 'id', id: select }" 
        v-on:assignment="(emit) => { Receive(emit) }"
        v-on:close="dialog=false"
    ></ChildDialog> 

</div>
</template>


<script>

export default {
    data () {
        return {
            search:         null,
            select:         this.selected ? this.selected : null,

            sk_keys:        this.$store.state.screenkeys[this.sk ? this.sk : 'la_uc'],
            keyboard:       this.sk ? true : false,

            loading:        false,
            dialog:         false,
            expand:         false,
            lazy:           false,
        }
    },

    props: {
        no_autocomplete:    { type: Boolean },
        entity:             { type: String },
        label:              { type: String },
        selected:           { type: [Number, String] },
        sk:                 { type: String },
        icon:               { type: String },
        search_mixed_chars: { type: Boolean },
        search_mixed_words: { type: Boolean },
        sync:               { type: Boolean },
        inherited:          { type: Boolean }
    },
    
    computed: {
        items () {
            var self = this;

            if (this.sync) {
                return self.$store.state.lists.data[self.entity];
            }

            else {
                return self.$store.state.lists.data[self.entity].filter((item) => {

                    // JK: Search is Null
                    if (self.search === null) {
                        return item 
                    }
                    
                    // JK: Search is a Number / ID
                    else if (/^\d+$/.test(self.search)) {
                        if ( item.id == parseInt (self.search) ) { 
                            return item
                        }
                    }

                    // JK: Search is a String
                    else {
                        // JK: mixed Order
                        if (self.search_mixed_chars || self.search_mixed_words) {
                            /*let match = '';
                            
                            self.search [key].toUpperCase().split(self.search_mixed_chars ? '' : ' ').forEach (
                                function (to_find) 
                                {
                                    match = match + (!item.toUpperCase().includes (to_find) ? 0 : 1);
                                }
                            )

                            if (!match.includes (0)) {return item;}*/
                            

                            let match = 2

                            self.search.toUpperCase().split(self.search_mixed_chars ? '' : ' ').forEach((to_find) => {
                                match = !item.string.toUpperCase().includes (to_find.toUpperCase()) ? 0 : (match == 2 ? 1 : (match == 1 ? 1 : 0))
                            });

                            if (match == 1) {return item}
                        }

                        // JK: usual Order
                        else {
                            if (item.string.toLowerCase().includes(self.search.toLowerCase())) { 
                                return item
                            }
                        }
                    }
                });
            }                
        },

        disabled () {
            return this.inherited ? true : false
        }
    },
    
    /*watch:
    {
        selected: function () {
            console.log ('test: '+this.selected)
            if (this.select !== this.selected) {this.select = this.selected; this.search = this.SelectString (this.selected);}
        },
    },*/

    created () {
        this.Refresh (false)
    },

    methods: {
        Receive (emit) {
            this.$emit('emitid', emit.id)
            this.$root.snackbar(this.label + ' ' + emit.id + ' selected.')
        },

        SKinput (input) {
            if (input == 'DELETE_ALL') {
                this.search = null
            } 
            else if (input == 'DELETE_LAST') {
                this.search = this.search ? this.search.substring(0, this.search.length -1) : null
            } 
            else {
                this.search = this.search ? this.search+input : input   
            }

            if (this.sync) { this.Refresh (true) }
        },

        SelectItem (id) {
            this.$emit('emitid', id)
            this.select = id
            this.search = this.SelectString(id)
            this.expand = false
        },

        SelectString (id) {
            let string = id ? this.$store.state.lists.data [this.entity].filter((item) => { return item.id == id }) : []
            return string[0] ? string[0].string : null
        },

        async Refresh (refresh) {
            const self = this
            self.loading = true
            
            // JK: Partial Fetching
            if (this.sync) {
                let string = self.search

                // JK: this timeout prevents the function from fireing requests before user has typed the full string (if he/she is typing fast^^)
                setTimeout(async function () {
                    if (string === self.search) {

                        // JK: process search string for PHP
                        if (self.search) {
                            string = parseInt(self.search) ? parseInt(self.search) : self.search.split(self.search_mixed_chars ? '' : ' ')
                        }

                        await axios.post('dbi/lists/' + self.entity, Object.assign({}, { id: self.select ? self.select : null, string: string }))
                            .then((response) => {
                                self.$store.commit('SET_ENTITYLIST', { entity: self.entity, data: response.data })
                                //console.log ('Entity Lists: Data sync fetched. '+response.data.length+' items for "'+self.entity+'" in Store.');
                                if (!refresh) { self.search = self.SelectString(self.select) }
                            })
                            .catch((error) => { self.$root.AXIOS_ERROR_HANDLER(error) })
                    }
                }, 500)
            }

            // JK: Full Fetching
            else {
                let run = refresh ? true : (self.select == null ? false : (self.SelectString(self.select) == null ? true : false));

                // JK: check if renewal of entity list is required
                if (self.$store.state.lists.data[self.entity][0] === undefined || run === true) {
                    let dbi = await self.$root.DBI_SELECT_GET ('lists', self.entity)

                    if (dbi?.contents) {
                        if (typeof dbi.contents === 'object') {
                            self.$store.commit('SET_ENTITYLIST', { entity: self.entity, data: dbi.contents } )
                            console.log('Entity Lists: Data fetched. ' + dbi.contents.length + ' items for "' + self.entity + '" in Store.')

                            self.search = self.SelectString(self.select)
                        }
                        else {
                            self.$store.commit('SET_ENTITYLIST', { entity: self.entity, data: [] })
                            console.log('Entity Lists: (Error) Could not fetch "' + self.entity + '".')
                        }
                    }
                }
                else {                
                    console.log('Entity Lists: No fetch required. ' + self.$store.state.lists.data[self.entity].length + ' items for "' + self.entity + '" in Store.')

                    self.search = self.SelectString(self.select)
                }
            }

            self.loading = false
        }
    }
}

</script>