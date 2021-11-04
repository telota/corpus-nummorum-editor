<template>
<div class="d-flex flex-wrap">

    <div v-if="linking" class="d-flex">
        <adv-btn
            icon="link_off"
            :tooltip="'Unlink ' + name"
            color-main="red darken-4"
            color-hover="red darken-1"
            color-text="white"
            :small="small"
            :medium="!small"
            @click="$emit('unlink')"
        />
        <adv-btn
            v-if="$route.name === 'coins-types-edit'"
            :icon="entity === 'types' ? 'sync' : 'camera_alt'"
            :tooltip="'Set ' + name + ' as ' + (entity === 'types' ? 'inheriting Type.' : 'representing Coin.')"
            color-main="blue_prim"
            color-hover="blue_sec"
            color-text="white"
            :small="small"
            :medium="!small"
            @click="entity === 'types' ? $emit('inherit') : $emit('represent')"
        />
        <div :class="divider" />
    </div>

    <!--<adv-btn
        v-else-if="publisher"
        :icon="status === 1 ? 'get_app' : 'publish'"
        :color-main="status === 1 ? 'red darken-4' : 'blue_prim'"
        :color-hover="status === 1 ? 'red darken-1' : 'blue_sec'"
        color-text="white"
        :tooltip="(status === 1 ? 'Unpublish' : 'Publish') + ' cn ' + entity.slice(0, -1) + ' ' + id"
        :disabled="!is_publisher || status === 3"
        :small="small"
        :medium="!small"
        v-on:click="$emit('publish')"
    />-->

    <item-publisher-single
        v-else-if="publisher"
        commandbar
        :entity="entity"
        :item="{
            id: id,
            dbi: {
                public: status,
                creator: null
            }
        }"
        :small="small"
        :medium="!small"
        @refresh="$emit('refresh')"
    />

    <adv-btn
        v-else-if="select"
        icon="touch_app"
        :tooltip="'Select ' + name"
        color-main="blue_prim"
        color-hover="blue_sec"
        color-text="white"
        :small="small"
        :medium="!small"
        :disabled="selected == id"
        v-on:click="$emit('select', id)"
    />

    <v-spacer />

    <div class="d-flex">
        <adv-btn
            icon="preview"
            :tooltip="'Show Details of ' + name"
            :small="small"
            :medium="!small"
            :color-hover="colorHover"
            @click="noDetailsDialog ? $emit('details') : $store.commit('setDetailsDialog', { entity: entity, id: id })"
        />
        <a :href="'/editor#/' + entity + '/edit/' + id">
            <adv-btn
                icon="edit"
                :tooltip="'edit ' + name"
                :small="small"
                :medium="!small"
                :color-hover="colorHover"
            />
        </a>

        <div :class="divider" />
    </div>

    <div class="d-flex">
        <a :href="'/editor#/types/copy/' + entity + '/' + id">
            <adv-btn
                icon="add_circle_outline"
                :tooltip="'Copy ' + name + ' as new ' + $root.label('type')"
                :small="small"
                :medium="!small"
                :color-hover="colorHover"
            />
        </a>
        <a :href="'/editor#/coins/copy/' + entity + '/' + id">
            <adv-btn
                icon="add_circle"
                :tooltip="'Copy ' + name + ' as new ' + $root.label('coin')"
                :small="small"
                :medium="!small"
                :color-hover="colorHover"
            />
        </a>
    </div>

</div>
</template>


<script>
import itemPublisherSingle from './itemPublisherSingle.vue'

export default {
    components: {
        itemPublisherSingle
    },

    data () {
        return {
            divider: 'header_hover fill-height width-1px'
        }
    },

    props: {
        entity:         { type: String, required: true },
        id:             { type: [String, Number], default: null },
        publisher:      { type: Boolean, default: false },
        status:         { type: Number, default: 0 },
        linking:        { type: Boolean, default: false },
        small:          { type: Boolean, default: false },
        select:         { type: Boolean, default: false },
        selected:       { type: [String, Number], default: null },
        colorHover:     { type: String, default: 'header_hover' },
        noDetailsDialog:{ type: Boolean, default: false }
    },

    computed: {
        name () {
            return 'cn ' + this.entity.slice(0, -1) + ' ' + this.id
        },

        /*is_publisher () {
            return this.$root.user.level > 17 ? true : false
        }*/
    }
}

</script>
