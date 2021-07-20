<template>
<component-toolbar>

    <template v-slot:toolbar>
        <!-- Name -->
        <div
            class="ml-3 headline"
            :class="item.public === 3 ? ' text-decoration-line-through' : ''"
            v-html="name.replaceAll(' ', '&nbsp;')"
        />
        <a v-if="item.public === 1" :href="'https://www.corpus-nummorum.eu/' + entity + '/' + id" target="_blank">
            <div class="ml-2 headline" v-text="'*'" />
        </a>

        <v-spacer></v-spacer>

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

        <div class="header_hover fill-height" style="width: 1px;" />

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

        <div class="header_hover fill-height" style="width: 1px;" />

        <adv-btn
            icon="delete"
            tooltip="Request Deletion"
            color-hover="header_hover"
            :disabled="item.public === 3 || item.public === 1"
            @click="$emit('erase', true)"
        />

        <div class="header_hover fill-height" style="width: 1px;" />

        <adv-btn
            icon="preview"
            tooltip="Details page Preview"
            color-hover="header_hover"
            @click="$emit('details', { entity, id, public: item.public })"
        />

        <div>
            <a v-if="item.public === 1" :href="'https://www.corpus-nummorum.eu/' + entity + '/' + id" target="_blank">
                <adv-btn
                    icon="public"
                    :tooltip="'Show ' + name + ' on public website'"
                    color-hover="header_hover"
                />
            </a>

            <adv-btn
                v-else
                :icon="item.public === 3 ? 'hide_source' : (item.public === 2 ? 'hourglass_empty' : 'publish')"
                tooltip="Request publishing"
                color-hover="header_hover"
                color-text="blue_sec"
                :disabled="item.public !== 0"
                @click="$emit('mark', true)"
            />
        </div>

        <!-- Save -->
        <v-btn
            tile
            depressed
            dark
            class="headline"
            color="blue_prim"
            :width="200"
            :height="50"
            :disabled="item.public === 3 || (item.public === 1 && $root.user.level < 12)"
            v-text="'save'"
            @click="$emit('save', true)"
        ></v-btn>

    </template>

</component-toolbar>
</template>


<script>

export default {
    data () {
        return {
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
        }
    }
}
</script>
