<template>
<div id="ScreenKeys">

    <v-menu offset-y
        :close-on-content-click="false"
        :nudge-bottom="5"
        transition="scale-transition"
        :close-on-click="false"
    >
        <!-- Button -->
        <template v-slot:activator="{ on: menu }">
            <v-tooltip bottom>
            <template v-slot:activator="{ on: tooltip }">
                <v-btn icon v-on="{ ...tooltip, ...menu }" @click="active = !active"><v-icon>{{ !active ? 'keyboard' : 'keyboard_hide' }}</v-icon></v-btn>
            </template>
            <span>On-screen keyboard</span>
            </v-tooltip>
        </template>

        <!-- Dropdown -->
        <v-card tile :width="card_width">
            <v-card-text class="pl-n3 pr-n3 text-center">

                <!-- Array Keys -->
                <div>

                    <v-tooltip bottom v-for="key in keys" v-bind:key="key.value">
                        <template v-slot:activator="{ on }"> 
                            <v-btn icon v-on="on" @click="Input (key.value)">{{ key.value }}</v-btn>
                        </template>
                        <span>{{ key.text }}</span>
                    </v-tooltip>

                </div>

                <!-- Function keys -->
                <div class="mt-3">

                    <v-tooltip bottom>
                        <template v-slot:activator="{ on }"> 
                            <v-btn icon v-on="on" @click="Input (' ')">&blank;</v-btn>
                        </template>
                        <span>Blank Space</span>
                    </v-tooltip>
                    
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on }"> 
                            <v-btn icon v-on="on" @click="Input ('DELETE_LAST')">&larr;
                
                </v-btn>
                        </template>
                        <span>Delete last sign</span>
                    </v-tooltip>

                    <v-tooltip bottom>
                        <template v-slot:activator="{ on }"> 
                            <v-btn icon v-on="on" @click="Input ('DELETE_ALL')">&Cross;</v-btn>
                        </template>
                        <span>Delete all signs</span>
                    </v-tooltip>

                </div>

            </v-card-text>
        </v-card>

    </v-menu>

</div>
</template>


<script>


export default
{
    data () 
    {
        return {
            card_width: 180,
            active:     false,

            el_uc: this.$store.state.screenkeys.el_uc,
            el_uc_adv: this.$store.state.screenkeys.el_uc_adv,
            la_uc: this.$store.state.screenkeys.la_uc
        }
    },

    props: 
    {
        //sk_props: {type: Object} // JK: 'layout', 'var', 'name' expected
        prop_layout:    {type: String},
        prop_var:       {type: [String, Number]},
        prop_key:       {type: [String, Number]},
    },
    
    computed: 
    {
        keys () {
            return this [this.prop_layout ? this.prop_layout : 'el_uc'];
        }
    },

    methods: 
    {
        Input (value) { // will emit klicked key to parent component
                //console.log ('Screeneys emit: {var: '+this.prop_var+', key: '+this.prop_key+', val: '+value+'}')

            this.$emit('KeyClicked', {
                var:    this.prop_var   ?   this.prop_var : 'item_edited', 
                key:    this.prop_key   ?   this.prop_key : 'sk', 
                val:    value                
            });
        }
    }
}

</script>