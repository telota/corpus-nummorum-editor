<template>
    <div id="ChildDialog">  

        <!-- JK: Child Dialog-->
        <v-dialog scrollable persistent
            v-model="active"
            :fullscreen="$root.child_dialog.fullscreen || !$vuetify.breakpoint.mdAndUp" 
            :max-width="$root.child_dialog.width"
        >
            <v-card tile>

                <!-- Dialog Header -->
                <v-system-bar color="sysbar" class="pt-5 pb-4 pl-4">
                    <v-icon class="mr-2 mb-1">launch</v-icon> <span>{{ name.p }}</span>
                    <v-spacer></v-spacer>
                    <v-icon @click="$root.child_dialog.fullscreen = !$root.child_dialog.fullscreen;" class="mr-3" v-if="$vuetify.breakpoint.mdAndUp">{{ !$root.child_dialog.fullscreen ? 'fullscreen_exit' : 'fullscreen' }}</v-icon>
                    <v-icon @click="close ()">close</v-icon>
                </v-system-bar>
                    
                <!-- Child component -->
                <v-card-text>
                    <component 
                        :is="component"
                        :prop_item="prop_item"
                        v-on:ChildEmit="receive"
                    ></component>
                </v-card-text>

                <!-- Dialog Footer -->
                <v-card-actions class="ma-0 pa-0">
                    <v-card color="appbar" width="100%" tile>
                        <div class="component-wrap d-flex">

                            <v-btn @click="close ()" tile depressed>
                                Close
                            </v-btn>

                            <v-spacer></v-spacer>

                            <span class="mt-1">{{ assign_condition.text }}</span>

                            <span class="ml-5">
                                <v-tooltip bottom>
                                    <template v-slot:activator="{ on }">
                                        <v-btn v-on="on" depressed
                                            @click="assign (false)"
                                            :disabled="assign_condition.disabled"
                                            :text="assign_condition.disabled"
                                            :tile="!assign_condition.disabled"
                                        ><v-icon>send</v-icon></v-btn>
                                    </template>
                                    <span>Assign selected {{ name.s }} to form and keep {{ name.p }} open.<br />Please note: you have to save your assignment.</span>
                                </v-tooltip>

                                <v-tooltip bottom>
                                    <template v-slot:activator="{ on }">
                                        <v-btn v-on="on" depressed
                                            @click="assign (true)"
                                            :disabled="assign_condition.disabled"
                                            :text="assign_condition.disabled"
                                            :tile="!assign_condition.disabled"
                                        ><v-icon>cancel_schedule_send</v-icon></v-btn>
                                    </template>
                                    <span>Assign selected {{ name.s }} to form and close {{ name.p }}.<br />Please note: you have to save your assignment.</span>
                                </v-tooltip>
                            </span>

                        </div>
                    </v-card>
                </v-card-actions>

            </v-card>
        </v-dialog>

    </div>
</template>



<script>

export default
{    
    components: {},

    data ()  // JK: define VueJS Variables
    {
        return {
            active:             this.prop_active,
            component:          this.prop_component,

            received:            {}
        }
    },

    props:
    {
        prop_active:        { type: Boolean },    // JK: Toggle Dialog
        prop_component:     { type: String },     // JK: requested child
        prop_item:          { type: Object },     // JK: ID-key and -value in parent
    },

    computed: 
    {  
        name () {
            let uc = this.component.charAt(0).toUpperCase () + this.component.slice (1);

            return {
                p: uc,
                s: uc.slice(-1) == 's' ? uc.substring(0, uc.length - 1) : uc
            }
        },

        assign_condition () {
            if ( !this.prop_item.id && !this.received.id) 
            {
                return {text: 'No '+this.name.s+' selected', disabled: true};
            } 
            else if (this.received.id == this.prop_item.id) 
            {
                return {text: this.name.s+' '+this.prop_item.id+' has already been assigned', disabled: true};
            } 
            else 
            {
                return {text: 'Assign '+this.name.s+' '+this.received.id, disabled: false};
            }
        }
    },

    created () 
    {
    },
    
    methods: 
    {
        receive (emit) {
            this.received = emit; 
        },

        assign (close) {
            this.$emit('assignment', {component: this.received.component, key: this.prop_item.key, id: this.received.id} );
            if (close) {this.close ();}
        },

        close () {
            this.active = false;
            this.$emit('close', true );
        },      
    }
}

</script>