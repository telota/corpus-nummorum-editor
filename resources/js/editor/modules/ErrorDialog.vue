<template>
<div>

    <v-dialog v-model="dialog" persistent scrollable max-width="600px">
        <v-card tile>

            <v-system-bar color="sysbar" class="pt-5 pb-4 pl-5">
                <v-icon class="mr-3">warning</v-icon> <span>Warning: {{ error.validation ? 'Validation Issue' : 'System Error'}}</span>
                <v-spacer></v-spacer>
                <v-icon @click="$emit ('close')">close</v-icon>
            </v-system-bar>

            <v-card-text class="mt-5">
                <!-- Validation Issue -->
                <div v-if="error.validation">
                    <p class="mb-5">Sorry, a validation issue occured.<br />Please revise your data and try again. Thank you!</p>
                    
                    <p style="white-space: pre-line"><b>Message:&ensp;</b>{{ error.validation }}</p>
                </div>

                <!-- Error -->
                <div v-else>
                    <p class="mb-7">Sorry, a System Error occured.<br />Please provide the information given below to our IT Team. Thank you!</p>
                    
                    <p><b>Resource:&ensp;</b>{{ error.resource ? error.resource : '--' }}</p>
                    <p><b>Exception:&ensp;</b>{{ error.exception ? error.exception : '--' }}</p>
                    <p><b>Message:&ensp;</b>{{ error.message ? error.message : '--' }}</p>
                    <p><b>Parameters:&ensp;</b><span class="caption">{{ error.params ? error.params : '--' }}</span></p>
                </div>                
            </v-card-text>

            <v-card-actions class="mt-n5">
                <v-spacer></v-spacer>
                <v-btn @click="$emit ('close')" text>
                    OK
                </v-btn>
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
            dialog:     this.error.active ? this.error.active : false,
        }
    },

    props:
    {
        //active:     {type: Boolean},
        //message:    {type: String},
        error:      {type: Object}     
    },
    
    computed: 
    {
        
    },

    created () 
    {
        if (!this.error.validation) {axios.post ('dbi/errors/input/insert', Object.assign ({}, this.error));}
    },

    methods: 
    {
        
    }
}

</script>