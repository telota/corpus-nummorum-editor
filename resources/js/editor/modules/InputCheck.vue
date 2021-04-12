<template>
<div>

    <v-dialog v-model="$store.state.InputCheckDialog" persistent max-width="400px">
        <v-card tile>

            <v-system-bar color="sysbar" class="pt-5 pb-4 pl-6">
                <v-icon class="mr-2 mb-1">warning</v-icon> <span>Input Check</span>
                <v-spacer></v-spacer>
                <v-icon @click="$store.commit ('SET_INPUTCHECK_DIALOG', null)">close</v-icon>
            </v-system-bar>

            <v-card-text class="mt-5">
                <p>Sorry, the server didn't accept your input.</p>
                <p><strong>{{ $store.state.InputCheckMessage }}</strong></p>
                <p>Did you encounter a bug? Please do not hesitate to report it to our IT team.</p>
            </v-card-text>
            <v-card-actions class="mt-n5">
                <v-spacer></v-spacer>
                <v-btn @click="$store.commit ('SET_INPUTCHECK_DIALOG', null)" text>
                    OK
                </v-btn>
                <v-spacer></v-spacer>
            </v-card-actions>
        </v-card>
    </v-dialog>

</div>
</template>


<script>

// Import VueX-Stuff
//import { createNamespacedHelpers } from 'vuex';     // vuex helper function to map additional modules
//import { mapState } from 'vuex';                    // vuex helper function to map the variables in state (store) on this instance


export default
{
    data () 
    {
        return { }
    },
    
    computed: 
    {        
        /*...mapState ({ // Map VueX Store State
            dialog:     state => state.InputCheckDialog,
            feedback:   state => state.InputCheckMessage,            
        })*/
    },

    methods: 
    {
        /*DialogClose () {
            this.$store.commit ('SET_INPUTCHECK_DIALOG', null);
        },*/
    }
}

</script>