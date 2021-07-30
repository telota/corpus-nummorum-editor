<template>
<component-toolbar>

    <!-- Name -->
    <div
        class="ml-3 headline"
        :class="status === 3 ? 'text-decoration-line-through' : ''"
        v-html="name.replaceAll(' ', '&nbsp;')"
    />
    <a v-if="status === 1" :href="'https://www.corpus-nummorum.eu/' + entity + '/' + id" target="_blank">
        <div class="ml-2 headline" v-text="'*'" />
    </a>

    <v-spacer />

    <!-- Actions -->
    <a :href="'/editor#/' + entity + '/search'">
        <adv-btn
            icon="search"
            tooltip="Go to search"
            color-hover="header_hover"
        />
    </a>
    <a :href="'/editor#/' + entity + '/edit'">
        <adv-btn
            icon="add"
            tooltip="Create new Object"
            color-hover="header_hover"
        />
    </a>

    <div :class="divider" />

    <a :href="'/editor#/types/copy/' + entity + '/' + id">
        <adv-btn
            icon="add_circle_outline"
            :tooltip="'Copy ' + name + ' as new ' + $root.label('type')"
            color-hover="header_hover"
        />
    </a>

    <a :href="'/editor#/coins/copy/' + entity + '/' + id">
        <adv-btn
            icon="add_circle"
            :tooltip="'Copy ' + name + ' as new ' + $root.label('coin')"
            color-hover="header_hover"
        />
    </a>

    <div :class="divider" />

    <adv-btn
        icon="delete"
        tooltip="Request Deletion"
        color-hover="header_hover"
        :disabled="status === 3 || status === 1"
        @click="$emit('erase', true)"
    />

    <div :class="divider" />

    <adv-btn
        icon="preview"
        tooltip="Details page Preview"
        color-hover="header_hover"
        @click="$store.commit('setDetailsDialog', { entity, id })"
    />

    <div>
        <a v-if="status === 1" :href="'https://www.corpus-nummorum.eu/' + entity + '/' + id" target="_blank">
            <adv-btn
                icon="public"
                :tooltip="'Show ' + name + ' on public website'"
                color-hover="header_hover"
            />
        </a>

        <adv-btn
            v-else
            :icon="status === 3 ? 'hide_source' : (status === 2 ? 'hourglass_empty' : 'publish')"
            tooltip="Request publishing"
            color-hover="header_hover"
            color-text="blue_sec"
            :disabled="status !== 0"
            @click="$emit('mark', true)"
        />
    </div>

    <div :class="divider" />

    <!-- Save -->
    <v-hover v-slot="{ hover }">
        <div
            class="d-flex align-center justify-center headline font-weight-bold text-uppercase"
            :class="(saveDisabled ? 'grey--text' : 'light-blue--text text--darken-2') + (hover && !saveDisabled ? ' header_hover' : ' header_bg')"
            style="width: 200px; height: 50px;"
            :style="saveDisabled ? 'cursor: default:' : 'cursor: pointer'"
            @click="save()"
        >
            <v-icon v-text="'save'" class="mr-2" :class="saveDisabled ? 'grey--text' : 'light-blue--text text--darken-2'" />
            <div v-text="saveDisabled ? (status === 3 ? 'deleted' : 'forbidden') : 'save'" />
        </div>
    </v-hover>

</component-toolbar>
</template>


<script>

export default {
    data () {
        return {
            divider: 'header_hover fill-height width-1px'
        }
    },

    props: {
        entity: { type: String, default: 'coins' },
        item:   { type: Object, default: () => { return { id: '??', public: 0 }} }
    },

    computed: {
        id () { return this.item.id ?? null },

        name () {
            return ['cn', this.entity.slice(0, -1), this.item.id].join(' ')
        },

        status () {
            return this.item.public ?? 0
        },

        saveDisabled () {
            return this.item.public === 3 || (this.item.public === 1 && this.$root.user.level < 12)
        }
    },

    methods: {
        save () {
            if (!this.saveDisabled) this.$emit('save', true)
        }
    }
}
</script>
