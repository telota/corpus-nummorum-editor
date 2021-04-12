<template>
<div>

<ItemInputTemplate
    :entity="entity"
    :loading="loading"
>
    <template v-slot:content>
        <!-- New Item Options (no ID given) --------------------------------------------- -->
        <div class="mt-n2" style="width: 100%">
            <v-row>
                <!-- new empty -->
                <v-col 
                    cols="12" 
                    sm="6" 
                    md="3"
                >
                    <v-card 
                        tile 
                        class="blue_prim pa-5 text-center" 
                        min-height="300" 
                        @click="newItem()"
                    >
                        <v-icon 
                            x-large 
                            class="mt-10 mb-5" 
                            v-text="'add'"
                        ></v-icon>
                        <div 
                            class="title" 
                            v-text="'New empty ' + $root.label(entity.slice(0, -1))"
                        ></div>
                    </v-card>
                </v-col>
                <!-- copy existing -->
                <v-col  
                    v-for="(record) in ['types', 'coins']" 
                    :key="record"
                    cols="12" 
                    sm="6" 
                    md="3"
                >
                    <v-card 
                        tile 
                        class="blue_prim pa-5 text-center" 
                        min-height="300" 
                        @click="$router.push('/' + record + '/search')"
                    >
                        <v-icon 
                            x-large 
                            class="mt-10 mb-5" 
                            v-text="record === 'types' ? 'add_circle_outline' : 'add_circle'"
                        ></v-icon>
                        <div 
                            class="title" 
                            v-text="'Copy existing ' + $root.label(record.slice(0, -1)) + ' as ' + $root.label(entity.slice(0, -1))"
                        ></div>
                        <div 
                            class="mt-2 body-2" 
                            v-text="'You will be redirected to the search form.' + 
                            ' Look for the ' + $root.label(record.slice(0, -1)) + ' you would like to use as a template for a new ' + $root.label(entity.slice(0, -1)) +
                            ' and click on the icon shown above.'"
                        ></div>
                    </v-card>
                </v-col>
                <!-- Import external -->
                <v-col 
                    cols="12" 
                    sm="6" 
                    md="3"
                >
                    <v-card 
                        tile 
                        class="blue_sec pa-5 text-center" 
                        min-height="300" 
                        @click="$router.push('/' + entity + '/import')"
                    >
                        <v-icon 
                            x-large 
                            class="mt-10 mb-5" 
                            v-text="'arrow_circle_down'"
                        ></v-icon>
                        <div 
                            class="title" 
                            v-text="'Import external ' + $root.label(entity.slice(0, -1))"
                        ></div>
                        <div 
                            class="mt-2 body-2" 
                            v-text="'Import external ' + $root.label(entity)"
                        ></div>
                    </v-card>
                </v-col>
            </v-row>
        </div>
    </template>
</ItemInputTemplate>

</div>
</template>



<script>

export default {
    data () {
        return {
            loading: false,
        }
    },

    props: {
        entity: { type: String, required: true },
    },

    computed: {
        // Localization
        l () { return this.$root.language },
        labels () { return this.$root.labels },
    },

    watch: {        
        entity: function() { this.set_crumb() }
    },

    created () {
        this.set_crumb()
    },
    
    methods: { 
        set_crumb () {
            this.$store.commit('setBreadcrumbs', [
                { label: this.entity, to:'' },
                { label: 'new record', to:'' }
            ])
        },

        async newItem () {
            this.loading = true
            const response = await this.$root.DBI_INPUT_POST(this.entity, 'input', {id: 'new'})
            if (response.success) {
                this.$root.snackbar(response.success, 'success')
                this.$router.push('/' + this.entity + '/edit/' + response.id)
            }
            this.loading = false
        }
    }
}

</script>