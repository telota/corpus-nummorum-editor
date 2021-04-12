<template>
<div>

    <v-card class="grey_trip" tile>        
        <detailscontent
            :entity="entity"
            :id="id"
            :key="entity + id"
            show_header
            no_dialog
        ></detailscontent>
    </v-card>

</div>
</template>


<script>

import detailscontent from './modules/detailsContent.vue'

export default {

    components: {
        detailscontent
    },

    data () {
        return {}        
    },

    props: {
        entity: { type: String },
        id: { type: Number }
    }
}

</script>