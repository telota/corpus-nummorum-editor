<template>
<v-overlay
    :value="show"
    :z-index="500"
>
    <v-card tile dark class="grey_sec" :max-width="750">
        <h1 class="text-center pl-5 pr-5 pt-5 font-weight-thin">
            {{ $store.state.appName }}
        </h1>

        <v-card-text class="text-center">
            <p>
                This is an Instance of the "CN Editor" App.
                <span v-if="git" v-html="git" />
            </p>
            <p>
                "CN Editor" is open-sourced software licensed under the <a href="https://www.gnu.org/licenses/gpl-3.0.en.html" target="_blank">GNU General Public License v3.0</a>,<br/>
                created and maintained by <a href="https://orcid.org/0000-0003-2713-5207" target="_blank">Jan Köster</a> and <a href="https://orcid.org/0000-0003-3371-6163" target="_blank">Claus Franke</a>.
            </p>
            <p>
                <a
                    href="https://www.bbaw.de/en/bbaw-digital/telota"
                    target="_blank"
                    class="text-truncate"
                    v-html="'TELOTA&nbsp;-&nbsp;IT/DH'"
                /><br/>
                <a
                    href="https://www.bbaw.de"
                    target="_blank"
                    class="text-truncate"
                    v-html="'Berlin-Brandenburg Academy of Science and Humanities, Germany'"
                /><br/>
                2020&ndash;{{ new Date().getFullYear() }}
            </p>
            <p>
                <a
                    href="/imprint"
                    target="_blank"
                    class="text-truncate mr-3"
                    v-text="'Imprint'"
                />
                <a
                    href="/licensing"
                    target="_blank"
                    class="text-truncate mr-3"
                    v-html="'Licensing'"
                />
                <a
                    href="/wiki"
                    target="_blank"
                    class="text-truncate"
                    v-html="'Wiki'"
                />
            </p>
        </v-card-text>

        <div class="pb-3 d-flex justify-center">
            <v-btn text v-text="'Close'" @click="$store.commit('setAbout', null)" />
        </div>
    </v-card>
</v-overlay>
</template>

<script>

export default {
    data () {
        return {
            year: new Date().getFullYear(),
        }
    },
    computed: {
        show () { return this.$store.state.showAbout },
        git () {
            if (!this.$root.system?.git?.[0]) return null
            const git = this.$root.system.git
            return [
                '<br/>VERSION:',
                '<a href="' + git[0] + '" target="_blank">' + (git[4] ?? git[1]) + '</a>,',
                'committed by ' + git[2] + ' (' + git[3] + ')'
            ].join('\n');
        }
    }
}

</script>
