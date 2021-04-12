<template>
<div class="d-flex flex-wrap justify-end">

    <div 
        v-if="link_off"
        class="d-flex component-wrap"
    >
        <advbtn
            icon="link_off"
            :tooltip="'Unlink cn ' + entity.slice(0, -1) + ' ' + id"
            color_main="red darken-4"
            color_hover="red darken-1"
            :small="small"
            v-on:click="$emit('unlink')"
        ></advbtn>
        <advbtn
            v-if="$route.name === 'coins-edit'"
            icon="sync"
            :tooltip="'Set cn ' + entity.slice(0, -1) + ' ' + id + ' as inheriting Type.'"
            color_main="blue_prim"
            color_hover="blue_sec"
            :small="small"
            v-on:click="$emit('inherit')"
        ></advbtn>
        <advbtn
            v-if="$route.name === 'types-edit'"
            icon="camera_alt"
            :tooltip="'Set cn ' + entity.slice(0, -1) + ' ' + id + ' as representing Coin.'"
            color_main="blue_prim"
            color_hover="blue_sec"
            :small="small"
            v-on:click="$emit('represent')"
        ></advbtn>
        <v-divider vertical></v-divider>
    </div>

    <advbtn
        v-else-if="publisher"
        :icon="public_state === 1 ? 'get_app' : 'publish'"
        :color_main="public_state === 1 ? 'red darken-4' : 'blue_prim'"
        :color_hover="public_state === 1 ? 'red darken-1' : 'blue_sec'"
        :tooltip="(public_state === 1 ? 'Unpublish' : 'Publish') + ' cn ' + entity.slice(0, -1) + ' ' + id"
        :disabled="public_state === 3"
        :small="small"
        v-on:click="$emit('publish')"
    ></advbtn>


    <!--<advbtn
        v-else
        :icon="is_publisher && publisher ? (public_state === 1 ? 'get_app' : 'publish') : (public_state === 1 ? 'public' : 'public_off')"
        :color_main="!publisher ? null : (public_state === 1 ? 'red' : 'blue_prim' )"
        :color_hover="!publisher ? null : (public_state === 1 ? 'red accent-2' : 'blue_sec')"
        :tooltip="is_publisher ? (
            (publisher ? ((public_state === 1 ? 'Unpublish' : 'Publish') + ' ' + labels[entity.slice(0, -1)]) : (public_state === 1 ? 'Check on public CN Website' : ('Unpublished' + ' ' + labels[entity.slice(0, -1)])))) : 
            ('You are not permitted to publish ' + entity)
        "
        :disabled="!publisher && public_state != 1"
        :key="publisher"
        :small="small"
        v-on:click="() => { if (!publisher && public_state === 1) { showcn() } else { $emit('publish') } }"
    ></advbtn>-->

    <v-spacer></v-spacer>
    
    <div class="d-flex component-wrap">
        <advbtn v-if="!disable_details"
            icon="preview"
            :tooltip="'Show Details of cn ' + entity.slice(0, -1) + ' ' + id"
            :small="small"
            v-on:click="$emit('details')"
        ></advbtn>

        <a v-else :href="'/editor#/' + entity + '/search'">
            <advbtn
                icon="search"
                :tooltip="'Search ' + labels[entity]"
                :small="small"
            ></advbtn>
        </a>

        <a :href="this.$route.name != entity + '-edit' ? ('/editor#/' + entity + '/edit/' + id) : ('/editor#/' + entity + '/search')">
            <advbtn
                :icon="this.$route.name != entity + '-edit' ? 'edit' : 'search'"
                :tooltip="(this.$route.name != entity + '-edit' ? ('Edit cn ' + entity.slice(0, -1) + ' ' + id) : ('Search ' + labels[entity.slice(0, -1)]))"
                :small="small"
            ></advbtn>
        </a>
        <v-divider vertical></v-divider>
    </div>    

    <div class="d-flex component-wrap">
        <a :href="'/editor#/types/copy/' + entity + '/' + id">
            <advbtn
                icon="add_circle_outline"
                :tooltip="'Copy cn ' + entity.slice(0, -1) + ' ' + id + ' as new ' + labels['type']"
                :small="small"
            ></advbtn>
        </a>

        <a :href="'/editor#/coins/copy/' + entity + '/' + id">
            <advbtn
                icon="add_circle"
                :tooltip="'Copy cn ' + entity.slice(0, -1) + ' ' + id + ' as new ' + labels['coin']"
                :small="small"
            ></advbtn>
        </a>
    </div>

</div>
</template>


<script>

export default {
    components: { },

    data () 
    {
        return {
            copy_set: [
                {}
            ]
        }
    },

    props: {

        entity:         { type: String },
        id:             { type: (String, Number) },
        publisher:      { type: Boolean, default: false },
        public_state:   { type: Number, default: 0 },
        disable_details:{ type: Boolean, default: false },
        link_off:       { type: Boolean, default: false },
        small:          { type: Boolean, default: false }
    },

    computed: {

        // Localization
        l () { return this.$root.language },
        labels () { return this.$root.labels },

        is_publisher () { return this.$root.user.level > 12 ? true : false }
    },

    created () {},

    methods: {

        showcn () {
            window.open('https://www.corpus-nummorum.eu/' + (this.entity === 'coins' ? 'coins?id=' : 'types/') + this.id, '_blank')
        }
    }
}

</script>