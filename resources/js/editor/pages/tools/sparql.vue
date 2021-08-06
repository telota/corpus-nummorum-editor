<template>
<div>
    <component-toolbar />
    <component-content>
        <sheet-template>
            <div>
                <v-select
                    filled outlined dense clearable
                    :items="presets"
                    v-model="selected"
                    label="Predefined Queries"
                    @input="selectPreset"
                    style="width: 50%"
                />

                <v-textarea
                    filled outlined dense clearable
                    v-model="query"
                    label="SPARQL Query"
                    :rows="6"
                />

                <v-btn
                    tile
                    block
                    large
                    :dark="query ? true : false"
                    :disabled="!query"
                    class="blue_prim"
                    v-text="'Run Query'"
                    @click="runQuery()"
                />
            </div>

            <v-progress-linear
                :indeterminate="loading"
                :height="1"
                class="mt-5 mb-5"
            />

            <template v-if="!loading && results !== 0">
                <div v-if="error.status">
                    <div class="red--text font-weight-bold pb-3" v-text="'Error ' + error.response" />
                    <div  v-html="error.data.replaceAll('\n', '<br/>')" />
                </div>

                <div v-else-if="results" style="position: relative">
                    <div class="mb-2 body-1 d-flex justify-space-between">
                        <a :href="download" target="_blank" v-text="'Open result in new Tab'" class="font-weight-bold blue_sec--text" />
                        <v-icon v-text="'clear'" @click="results = 0" />
                    </div>
                    <pre v-html="syntaxHighlight(results)" />
                </div>

                <div v-else v-text="'No Results'" />
            </template>

        </sheet-template>
    </component-content>

</div>
</template>

<script>

export default {
    data () {
        return {
            endpoint: this.$root.sparql,
            loading: false,
            query: null,

            error: {
                status: false,
                response: null,
                data: null
            },

            results: 0,
            download: null,

            selected: null,
            presets: [
                { text: 'Available Classes', value: 'SELECT DISTINCT ?class\nWHERE {\n\t?s a ?class .\n}' }
            ]
        }
    },

    created () {
        this.$root.setTitle('SPARQL')
    },

    methods: {

        async runQuery () {
            this.loading = true
            this.error.status = false
            this.results = this.download = null

            const src = this.endpoint + '?query=' + encodeURI(this.query.trim())

            await axios.get(src)
                .then((response) => {
                    this.results = response.data
                    this.download = src
                })
                .catch((error) => {
                    this.error = {
                        status: true,
                        response: error?.response?.status ? (error?.response?.status + ': ' + error?.response?.statusText) : '',
                        data: error?.response?.data ?? 'An unknown Error occurred'
                    }
                })

            this.loading = false
        },

        selectPreset (input) {
            this.query = input
            this.$nextTick(() => { this.selected = null })
        },

        syntaxHighlight (json) {
            if (typeof json != 'string') json = JSON.stringify(json, undefined, 2)

            /*json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            json = json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
                var cls = 'number';
                if (/^"/.test(match)) {
                    if (/:$/.test(match)) cls = 'key';
                    else cls = 'string';
                }
                else if (/true|false/.test(match)) cls = 'boolean';
                else if (/null/.test(match)) cls = 'null';
                return '<span class="' + cls + '">' + match + '</span>';
            });*/

            return json
        }
    }
}
</script>
