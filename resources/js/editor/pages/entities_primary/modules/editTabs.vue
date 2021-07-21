<template>
<drawer
    :collapse="collapse"
    :hover-width="220"
    v-on:expand="(emit) => { if (!emit) this.expanded = false }"
>
    <template v-slot:content>
        <template v-for="(tab, t) in tabs">
            <v-divider v-if="!tab" :key="'tab' + t"/>

            <v-hover v-else v-slot="{ hover }" :key="'tab' + t">
                <div
                    class="d-flex align-center body-2"
                    :class="tab.key === currentTab ? ' light-blue--text text--darken-3' : (hover ? 'header_hover' : '')"
                    style="height: 40px; width: 300px;"
                    :style="'cursor:' + (tab.key === currentTab ? 'default' : 'pointer')"
                    @click="tab.action"
                >
                    <div class="d-flex justify-center align-center" style="height: 40px; width: 40px;">
                        <v-icon v-text="tab.icon" :color="tab.key === currentTab ? 'light-blue darken-3' : ''" />
                    </div>

                    <div class="pl-2 text-truncate" v-text="tab.label" />
                </div>
            </v-hover>
        </template>

        <v-expand-transition>
            <div
                v-if="showInfo && expanded"
                style="padding-left: 40px; width: 300px;"
            >
                <div class="pl-2 caption" v-html="info" />
            </div>
        </v-expand-transition>

    </template>
</drawer>
</template>

<script>
export default {
    data () {
        return {
            collapse: 0,
            expanded: false,
            showInfo: false,
        }
    },

    props: {
        entity:     { type: String, default: 'coins' },
        id:         { type: Number, default: null },
        section:    { type: String, default: null },
        showImported:   { type: Boolean, default: false },
        item:       { type: Object, default: () => { return {
                created_at: null,
                updated_at: null,
                dbi: {
                    creator_name: null,
                    editor_name: null,
                    public: 0
                }
            }}
        }
    },

    computed: {
        title () {
            return ['Edit cn', this.entity.slice(0, -1), this.id, '(' + this.currentTab + ')'].join(' ')
        },

        tabs () {
            const routes = [
                this.entity === 'coins' ? { key: 'types', icon: 'blur_circular' } : { key: 'coins', icon: 'copyright' },
                { key: 'production',    icon: 'zoom_out_map' },
                { key: 'basics',        icon: 'category' },
                { key: 'obverse',       icon: 'flip_to_front' },
                { key: 'reverse',       icon: 'flip_to_back' },
                { key: 'references',    icon: 'import_contacts' },
                { key: 'individuals',   icon: 'emoji_people' },
                { key: 'miscellaneous', icon: 'style' }
            ]

            if (this.entity === 'coins') routes.unshift({ key: 'images', icon: 'camera_alt' })

            const tabs = routes.map((el) => { return {
                key: el.key,
                action: () => { this.pushRoute(el.key, '/' + [this.entity, 'edit', this.id, el.key].join('/')) },
                label: this.$root.label(el.key),
                icon: el.icon
            }})

            // Add Expander to Start
            tabs.unshift({
                key: 'collapse',
                action: () => { this.toggleExpansion() },
                label: 'Collapse Drawer',
                icon: this.expanded ? 'chevron_left' : 'chevron_right'
            }, null)

            tabs.push(
                null,
                {
                    key: 'import',
                    action: () => { this.showImport() },
                    label: (this.showImported ? 'Hide' : 'Show') + ' Import',
                    icon: this.showImported ? 'subtitles_off' : 'subtitles'
                },
                {
                    key: 'info',
                    action: () => { this.showInfos() },
                    label: (this.showInfo ? 'Hide' : 'Show') + ' Info',
                    icon: this.showInfo ? 'visibility_off' : 'visibility'
                },
            )

            return tabs
        },

        currentTab () {
            return this.section ?? this.tabs[2]?.key
        },

        info () {
            let state = this.item.dbi.public ?? 0
            if (state === 1) state = 'published'
            else if (state === 2) state = 'pending'
            else if (state === 3) state = 'deleted'
            else state = 'unpublished'

            return [
                '<div class="pt-1">status: ' + state + '</diV>',
                '<div class="pt-2">created</div>',
                '<div>' + this.$handlers.format.date(this.$root.language, this.item.created_at, true) + '</diV>',
                '<div>by ' + (this.item.dbi?.creator_name ?? '???') + '</diV>',
                '<div class="pt-2">updated</div>',
                '<div>' + this.$handlers.format.date(this.$root.language, this.item.updated_at, true) + '</diV>',
                '<div>by ' + (this.item.dbi?.editor_name ?? '???') + '</diV>'
            ].join('\n')
        }
    },

    watch: {
        title: function () {
            this.$root.setTitle(this.title)
        },
    },

    mounted () {
        this.$root.setTitle(this.title)
    },

    methods: {
        pushRoute (key, route) {
            if (key !== this.section) this.$router.push(route)
            setTimeout(() => { ++this.collapse }, 0)
        },

        showImport () {
            this.$emit('import')
            setTimeout(() => { ++this.collapse }, 0)
        },

        showInfos () {
            if (!this.showInfo && !this.expanded) this.expanded = true
            this.showInfo = !this.showInfo
        },

        toggleExpansion () {
            if (this.expanded) setTimeout(() => {
                ++this.collapse
                this.expanded = false
            }, 0)
            else this.expanded = true
        }
    }
}
</script>
