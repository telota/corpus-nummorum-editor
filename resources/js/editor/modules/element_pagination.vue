<template>
<div class="d-flex" :style="'width:' + (dense ? 254 : 324) + 'px'">
    <adv-btn
        icon="first_page"
        :medium="dense"
        :disabled="loading || offset < 1"
        :color-hover="colorHover"
        @click="$emit('offset', 0)"
    />

    <adv-btn
        icon="navigate_before"
        :medium="dense"
        :disabled="loading || offset < 1"
        :color-hover="colorHover"
        @click="$emit('offset', offset - limit)"
    />

    <v-menu
        tile
        offset-y
        :close-on-content-click="false"
        transition="expand-transition"
        :disabled="loading || count < limit"
    >
        <template v-slot:activator="{ on: menu }">
            <v-tooltip bottom :disabled="true /*loading || count < limit*/">>
                <template v-slot:activator="{ on: tooltip }">
                    <v-hover v-slot="{ hover }">
                        <div
                            v-on="{ ...tooltip, ...menu }"
                            class="d-flex align-center justify-center"
                            :class="hover && !(loading || count < limit) ? colorHover : color"
                            :style="'height:' + (dense ? 40 : 50) + 'px;width:' + (dense ? 94 : 124) + 'px;' + (loading || count < limit ? 'cursor: default' : 'cursor: pointer')"
                        >
                            <v-progress-linear
                                v-if="loading"
                                indeterminate
                                :height="1"
                            />
                            <div v-else class="text-center" :style="'font-size:' + (dense ? 11 : 12) + 'px; line-height:' + (dense ? 11 : 12) + 'px;'">
                                <div v-html="counted" class="font-weight-bold ma-2" />
                                <div v-html="page.text.current + '&nbsp;/&nbsp;' + page.text.total" class="ma-2" />
                            </div>
                        </div>
                    </v-hover>
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
        :medium="dense"
        :disabled="loading || offset >= count - limit"
        :color-hover="colorHover"
        @click="$emit('offset', offset + limit)"
    />

    <adv-btn
        icon="last_page"
        :medium="dense"
        :disabled="loading || offset >= count - limit"
        :color-hover="colorHover"
        @click="$emit('offset', (Math.ceil(count / limit) - 1) * limit)"
    />
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
                if (parsed > this.page.int.total) return 'Max is ' + this.page.int.total
                return true
            }
        }
    },

    props: {
        offset:     { type: [String, Number], default: 0 },
        limit:      { type: [String, Number], default: 12 },
        count:      { type: [String, Number], default: 0 },
        color:      { type: [String], default: 'transparent' },
        colorHover: { type: [String], default: 'grey_prim' },
        loading:    { type: Boolean, default: false },
        dense:      { type: Boolean, default: false }
    },

    watch: {
        pageSelector () {
            if (this.pageSelector) {
                const parsed = parseInt(this.pageSelector)
                if (parsed && parsed !== this.page.int.current && parsed <= this.page.int.total) {
                    this.$emit('offset', (parsed - 1) * this.limit)
                }
            }
        }
    },

    computed: {
        page () {
            const page = {
                int: { current: 0, total: 0},
                text: { current: 0, total: 0}
            }

            if (this.count > 0) {
                page.int.current    = Math.ceil(this.offset / this.limit) + 1
                page.int.total      = Math.ceil(this.count / this.limit)
                page.text.current   = this.$editor_format.number(this.$root.language, page.int.current)
                page.text.total     = this.$editor_format.number(this.$root.language, page.int.total)
            }

            return page
        },

        counted () {
            return this.$editor_format.number(this.$root.language, this.count)
        }
    }

}
</script>
