<template>
<v-overlay
    :value="show"
    :z-index="500"
>
    <v-card light tile :max-width="500">
        <h1 class="text-center pl-5 pr-5 pt-5 font-weight-thin text-uppercase">
            {{ $store.state.appName }}
        </h1>

        <v-card-text class="text-center">
            <p>
                This is an Instance of the "META FUNDUS" App.
            </p>
            <p>
                "META FUNDUS" is open-sourced software licensed under the<br/><a href="https://www.apache.org/licenses/LICENSE-2.0" target="_blank">APACHE LICENSE, VERSION 2.0</a><br/>
                created and maintained by <a href="https://orcid.org/0000-0003-2713-5207" target="_blank">Jan Köster</a>.
            </p>
            <p v-html="'&copy; 2021' + (year > 2021 ? ('&ndash;' + year) : '')" />
            <div v-if="git" v-html="git" />
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
                'VERSION:',
                '<a href="' + git[0] + '" target="_blank">' + git[1] + '</a>',
                '(committed by ' + git[2] + ' ' + git[3] + ')'
            ].join('\n');
        }
    }
}

</script>
