<template>
<div>
    <!-- Toolbar -->
    <component-toolbar>
        <a :href="'/editor#/' + (entity === 'coins' ? 'types' : 'coins') + '/edit'">
            <v-btn tile depressed :height="50" class="header_bg">
                <v-icon v-text="entity === 'coins' ? 'add_circle_outline' : 'add_circle'" />
                <div v-text="'Add ' + (entity === 'coins' ? 'Type' : 'Coin')" class="ml-2" />
            </v-btn>
        </a>
        <a href="/editor#/storage">
            <v-btn tile depressed :height="50" class="header_bg">
                <v-icon v-text="'folder'" />
                <div v-text="'File Manager'" class="ml-2" />
            </v-btn>
        </a>
        <a href="/wiki" target="_blank">
            <v-btn tile depressed :height="50" class="header_bg">
                <v-icon v-text="'help_outline'" />
                <div v-text="'Wiki'" class="ml-2" />
            </v-btn>
        </a>
        <a href="https://www.corpus-nummorum.eu/" target="_blank">
            <v-btn tile depressed :height="50" class="header_bg">
                <v-icon v-text="'public'" />
                <div v-text="'CN Website'" class="ml-2" />
            </v-btn>
        </a>
    </component-toolbar>

    <!-- Content -->
    <component-content>
        <v-row class="pa-0 ma-0 mt-2">

            <!-- new empty -->
            <v-col
                cols="12"
                sm="6"
                md="3"
            >
                <v-card
                    tile
                    dark
                    class="blue_prim pa-5 text-center"
                    min-height="300"
                    @click="newItem()"
                >
                    <v-icon
                        x-large
                        class="mt-10 mb-5"
                        v-text="'add'"
                    />
                    <div
                        class="title"
                        v-text="'New empty ' + $root.label(entity.slice(0, -1))"
                    />
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
                    dark
                    class="blue_prim pa-5 text-center"
                    min-height="300"
                    @click="$router.push('/' + record + '/search')"
                >
                    <v-icon
                        x-large
                        class="mt-10 mb-5"
                        v-text="record === 'types' ? 'add_circle_outline' : 'add_circle'"
                    />
                    <div
                        class="title"
                        v-text="'Copy existing ' + $root.label(record.slice(0, -1)) + ' as ' + $root.label(entity.slice(0, -1))"
                    />
                    <div
                        class="mt-2 body-2"
                        v-text="'You will be redirected to the search form.' +
                        ' Look for the ' + $root.label(record.slice(0, -1)) + ' you would like to use as a template for a new ' + $root.label(entity.slice(0, -1)) +
                        ' and click on the icon shown above.'"
                    />
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
                    dark
                    class="blue_sec pa-5 text-center"
                    min-height="300"
                    @click="$router.push('/' + entity + '/import')"
                >
                    <v-icon
                        x-large
                        class="mt-10 mb-5"
                        v-text="'arrow_circle_down'"
                    />
                    <div
                        class="title"
                        v-text="'Import external ' + $root.label(entity.slice(0, -1))"
                    />
                    <div
                        class="mt-2 body-2"
                        v-text="'Import external ' + $root.label(entity)"
                    />
                </v-card>
            </v-col>
        </v-row>
    </component-content>

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

    methods: {

        async newItem () {
            this.loading = this.$root.loading = true

            const response = await this.$root.DBI_INPUT_POST(this.entity, 'input', { id: 'new' })

            if (response.success) {
                this.$store.dispatch('showSnack', { color: 'success', message: response.success })
                this.$router.push('/' + this.entity + '/edit/' + response.id)
            }

            this.loading = this.$root.loading = false
        }
    }
}

</script>
