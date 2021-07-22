<template>
<div class="d-flex justify-space-between align-center" style="width: 100%; height: 50px">
    <!-- Left -->
    <div class="d-flex" style="width: 200px">

        <!-- Reload -->
        <adv-btn
            icon="sync"
            :tooltip="'Reload Search'"
            :color-hover="colorHover"
            @click="$emit('reload')"
        />

        <!-- Sort by -->
        <v-menu tile absolute :position-x="50" :position-y="90">
            <template v-slot:activator="{ on }">
                <adv-btn
                    v-on="on"
                    icon="sort_by_alpha"
                    :tooltip="'Sorted by'"
                    :color-hover="colorHover"
                />
            </template>
            <v-card tile>
                <v-hover v-slot="{ hover }" v-for="(sorter, i) in sorters" :key="i">
                    <div
                        class="d-flex pa-3 header_bg"
                        :class="hover ? 'header_hover' : ''"
                        style="cursor: pointer; min-width: 100px"
                        @click="$emit('sortby', sorter.value)"
                    >
                        <v-icon v-text="sorted.value === sorter.value ? (sorted.desc ? 'keyboard_arrow_up' : 'keyboard_arrow_down') : 'keyboard_arrow_up'" class="mr-2" />
                        <div v-text="sorter.text" :class="sorted.value === sorter.value ? 'font-weight-black' : ''" />
                    </div>
                </v-hover>
            </v-card>
        </v-menu>

        <!-- Limit -->
        <v-menu tile absolute :position-x="100" :position-y="90">
            <template v-slot:activator="{ on }">
                <adv-btn
                    v-on="on"
                    :text="'' + limit"
                    :tooltip="'Items per page'"
                    :color-hover="colorHover"
                />
            </template>
            <v-card tile>
                <v-hover v-slot="{ hover }" v-for="(ipp, i) in $store.state.ItemsPerPage" :key="i" :disabled="limit === ipp">
                    <div
                        class="d-flex pa-3 header_bg"
                        :class="hover ? 'header_hover' : ''"
                        style="cursor: pointer; min-width: 50px"
                        @click="$emit('limit', ipp)"
                    >
                        <div v-text="ipp" :class="limit === ipp ? 'font-weight-black' : ''" />
                    </div>
                </v-hover>
            </v-card>
        </v-menu>

        <!-- Layout -->
        <v-menu v-if="layout !== null" tile absolute :position-x="150" :position-y="90">
            <template v-slot:activator="{ on }">
                <adv-btn
                    v-on="on"
                    :icon="layouts[layout].icon"
                    tooltip="Select Layout"
                    :color-hover="colorHover"
                />
            </template>
            <v-card tile>
                <v-hover v-slot="{ hover }" v-for="(l, i) in layouts" :key="i" :disabled="layout === l.value">
                    <div
                        class="d-flex pa-3 header_bg"
                        :class="hover ? 'header_hover' : ''"
                        style="cursor: pointer; min-width: 100px"
                        @click="$emit('layout', l.value)"
                    >
                        <v-icon v-text="l.icon" class="mr-2" />
                        <div v-text="l.text" :class="layout === l.value ? 'font-weight-black' : ''" />
                    </div>
                </v-hover>
            </v-card>
        </v-menu>
    </div>

    <!-- Pagination -->
    <pagination
        :offset="offset"
        :limit="limit"
        :count="count"
        :color-hover="colorHover"
        color="header_bg"
        :loading="loading"
        v-on:offset="(emit) => { this.$emit('offset', emit) }"
    />

    <!-- Right -->
    <div class="d-flex justify-end" style="width: 200px">
        <slot name="right">
            <v-btn
                tile
                depressed
                :height="50"
                :width="200"
                class="header_bg"
                @click="$emit('add')"
            >
                <v-icon v-text="'add'" />
                <div v-text="'New Item'" class="ml-2" style="font-size: 20px; line-height: 20px;" />
            </v-btn>
        </slot>
    </div>

</div>
</template>


<script>
export default {
    data () {
        return {
        }
    },

    props: {
        sortby:     { type: [String, Number], default: 'id' },
        sorters:    { type: Array, default: () => { return [] } },
        layout:     { type: [String, Number], default: null },
        layouts:    { type: Array, default: () => { return [] } },

        offset:     { type: [String, Number], default: 0 },
        limit:      { type: [String, Number], default: 10 },
        count:      { type: [String, Number], default: 0 },
        color:      { type: [String], default: 'header_bg' },
        colorHover: { type: [String], default: 'header_hover' },
    },

    computed: {
        loading () {
            return this.$root.loading
        },

        sorted () {
            const split = this.sortby.trim().split(/s+/)
            return {
                value: split[0],
                desc: split[1] && split[1]?.toLowerCase() === 'desc'
            }
        }
    }
}
</script>
