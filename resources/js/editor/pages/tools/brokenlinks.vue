<template>
    <div>
        <v-row>
            
            <!-- JK: Broken coins -->            
            <v-col cols="12" sm="6" md="6">
                <v-card>
                    <!-- JK: Search -->
                    <v-app-bar color="sysbar">
                        <span class="headline">Broken Coin Links</span>
                        <v-spacer></v-spacer>
                        <v-text-field dense outlined clearable
                            v-model="search.coins"
                            prepend-icon="search"
                            label="Search"
                            hide-details
                        ></v-text-field>
                    </v-app-bar>
                    <!-- JK: Table -->
                    <v-data-table @click:row="RowClick" single-select
                        :headers="headers.coins"
                        :items="items.coins"
                        :search="search.coins"
                        :loading="loading"
                        :footer-props="{
                            itemsPerPageOptions: [20, 50],
                        }"
                    ></v-data-table>
                </v-card>             
            </v-col>

        </v-row>
        
        <!-- JK: Child Dialog -->
        <!--<ChildDialog v-if="child_dialog_active"
            :prop_active="child_dialog_active"
            :prop_component="child_dialog_data.component"
            :prop_item="{key: child_dialog_data.key, id: child_dialog_data.id}" 
            v-on:assignment="ChildReceive"
            v-on:close="child_dialog_active=false"
        ></ChildDialog>-->       
    
    </div>
</template>


<script>

// JK: Set stable variables (outside of vue component scope)
const CompLabelS            = 'Broken Link';
const CompLabelP            = 'Broken Links';
const CompLabelParent       = 'Tools';
const CompName              = 'brokenlinks';

const entity                = CompName; // JK: change if needed


export default
{    
    components: {},

    data ()  // JK: define VueJS Variables
    {
        return {
            component:          CompLabelS,
            entity:             entity,
            loading:            false,
            item_edited:        {},
            
            headers:            {
                                    coins: [
                                        {value: 'created_at',   text: 'Date'},
                                        {value: 'id_coin',      text: 'Coin ID'},
                                        {value: 'error_code',   text: 'Error Code'},
                                    ],
                                },
            items:              {coins: []},
            search:             {coins: null},
        }
    },

    props: 
    {
        //prop_item: {type: Object}
    },


    computed: 
    {
        
    },

    created () 
    {
        this.$store.commit( 'setBreadcrumbs',[ // JK: Set Breadcrumbs
            {label:CompLabelParent,to:''},
            {label:CompLabelP,to:''},
        ]);

        this.DataOutput ();
        
        /*this.ItemDefault (); // JK: Set default item

        if (this.prop_item) { this.item_edited = this.prop_item; }*/
    },
    
    methods: 
    {
        RowClick: function (item, row) { // JK: click on table row to set selected item as item_edited   
            row.select (true);
            //this.ItemSelect (item);
        },

        async DataOutput () {
            this.loading = true; // JK: Loading ON

            let source = 'dbi/' + this.entity;
            
            let dbi = await axios.get (source);          
            this.items.coins = Object.values (dbi.data);

            this.loading = false; // JK: Loading OFF
        }

        // JK: Parent-Child-Communication 
        /*Receiver () { // JK: Process properties given by parent (in most cases Child Dialog Component)
                console.log ('prop_item {key: '+this.prop_item.key+', id: '+this.prop_item.id+'}');
            let selected = this.items.find ( (item) => item.id === this.prop_item.id );
                console.log ('selected: '+this.prop_item.id);
                console.log ('selected: '+selected);
            this.ItemSelect (selected);
        },

        ChildDialog (input) { // JK: toggle child component
            this.child_dialog_data = {
                component:  !input ? null   :   input.component, 
                key:        !input ? null   :   input.key,
                id:         !input ? null   :   input.id, 
            };
            this.child_dialog_active = true;
                console.log ('Child Dialog: {component: '+this.child_dialog_data.component+', key: '+this.child_dialog_data.key+', id:'+this.child_dialog_data.id+', active: '+this.child_dialog_active+'}');
        },

        ChildReceive (emit) { // JK: receive emitted Input from child Dialog
                //console.log (Object.entries(emit));
            this.item_edited [emit.key] = emit.id;
            this.$store.commit ('showSnackbar', {message: 'SUCCESS: Assigned ID: '+emit.id+' from '+emit.component.charAt(0).toUpperCase()+ emit.component.slice(1)+' to form. Change not saved, yet', duration: 10000}); // JK: Response to snackbar
            this.ChildDialog (null);
        },
        
        // JK: Template Components
        ItemsTemplateReceive (emit) { // JK: receive emitted Input from child component
                console.log (CompLabelP+': received emit from Items Template (ID: '+emit.id+')')
            this.item_edited = emit;
            this.$emit('ChildEmit', {component: entity, id: emit.id} );
        },

        InputTemplateReceive (emit) { // JK: receive emitted Input from Input Template
                console.log (CompLabelP+': received emit from Input Template (refresh: '+this.items_refresh+')')

            if (emit.refresh) {
                this.items_refresh = null; 
                this.items_refresh = emit.item ? (emit.item.id ? emit.item.id : -1) : -1
            };

            if (emit.item) {
                this.item_edited = emit.item;
            } else {
                this.ItemDefault ();
            }
        },
        
        ItemDefault () { // JK: Set edited item to default
            var self = this;
            let item = {};

            Object.keys (self.attributes) .forEach (function (key) { 
                item [key] = self.attributes[key].default
            });

            self.item_edited = item;
        },*/
    }
}

</script>