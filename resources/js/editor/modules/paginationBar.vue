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
    <div class="d-flex" style="width: 324px">
        <adv-btn
            icon="first_page"
            :disabled="offset < 1"
            :color-hover="colorHover"
            @click="$emit('offset', 0)"
        />

        <adv-btn
            icon="navigate_before"
            :disabled="offset < 1"
            :color-hover="colorHover"
            @click="$emit('offset', offset - limit)"
        />

        <v-menu
            tile
            offset-y
            :close-on-content-click="false"
            transition="expand-transition"
        >
            <template v-slot:activator="{ on: menu }">
                <v-tooltip bottom>
                    <template v-slot:activator="{ on: tooltip }">
                        <v-btn
                            v-on="{ ...tooltip, ...menu }"
                            class="transparent"
                            depressed
                            :disabled="count < limit"
                            :text="count < limit"
                            :tile="count >= limit"
                            height="50px"
                            width="124px"
                        >
                            <div class="text-center" style="font-size: 12px; line-height: 12px;">
                                <div v-html="counted" class="font-weight-bold ma-2" />
                                <div v-html="page.current + '&nbsp;/&nbsp;' + page.total" class="ma-2" />
                            </div>
                        </v-btn>
                    </template>
                    <span>Go to specific page</span>
                </v-tooltip>
            </template>

            <v-card
                tile
                :width="124"
                class="pa-2"
                :class="color"
                raised
            >
                <div class="caption text-center font-weight-bold pb-2" v-text="'Page Number'" />
                <v-text-field
                    dense autofocus
                    v-model="pageSelector"
                    :rules="[checkSelectedPage]"
                />
                <div class="caption text-center mt-1" v-text="'CLEAR'" style="cursor:pointer" @click="pageSelector = null" />
            </v-card>
        </v-menu>

        <adv-btn
            icon="navigate_next"
            :disabled="offset >= count - limit"
            :color-hover="colorHover"
            @click="$emit('offset', offset + limit)"
        />

        <adv-btn
            icon="last_page"
            :disabled="offset >= count - limit"
            :color-hover="colorHover"
            @click="$emit('offset', (Math.ceil(count / limit) - 1) * limit)"
        />
    </div>

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
            pageSelector:          null,
            checkSelectedPage:     (v) => {
                if (!v) return true
                const parsed = parseInt(v)
                if (!parsed) return 'No valid Number'
                if (parsed > this.page.total) return 'Max is ' + this.page.total
                return true
            }
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

    watch: {
        pageSelector () {
            if (this.pageSelector) {
                const parsed = parseInt(this.pageSelector)
                if (parsed && parsed !== this.page.current && parsed <= this.page.total) {
                    this.$emit('offset', (parsed - 1) * this.limit)
                }
            }
        }
    },

    computed: {
        page () {
            const page = { current: 0, total: 0}

            if (this.count > 0) {
                page.current = this.$editor_format.number(this.$root.language, Math.ceil(this.offset / this.limit) + 1)
                page.total = this.$editor_format.number(this.$root.language, Math.ceil(this.count / this.limit))
            }

            return page
        },

        counted () {
            return this.$editor_format.number(this.$root.language, this.count)
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
