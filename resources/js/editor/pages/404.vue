<template>
<div>
    <component-toolbar>
        <template v-slot:toolbar>
            <div
                class="d-flex align-center justify-center"
                style="height: 50px; width: 100%"
            >
                <div class="headline font-weight-bold" v-text="'ERROR ' + 404" />
            </div>
        </template>
    </component-toolbar>

    <div class="d-flex align-center justify-center" style="width: 100vw; height: calc(100vh - 90px); top: 90px; position: absolute">
        <v-card tile>
            <v-card-text class="pt-4 text-center">
                <div class="headline mb-5" v-text="'404 | Unknown Route'" />

                <p>
                    Sorry, <i><b v-text="'/editor#' + path" /></i> does not match any registered CN Editor Route.<br />
                    Please check the URL for typos or try to remove path fragments.
                </p>
                You might want to return to the <a href="/editor#/dashboard" v-text="'Dashboard'" />.
            </v-card-text>
        </v-card>
    </div>

</div>
</template>

<script>
export default {
    props: {
        path: { type: String, default: 'unknown' }
    },

    created () {
        this.$root.setTitle('404')
    },
}
</script>
