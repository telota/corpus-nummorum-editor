<template>
<drawer
    :collapse="collapse"
    :hover-width="200"
>
    <template v-slot:content>
        <v-hover v-slot="{ hover }" v-for="(tab, t) in tabs" :key="'tab' + t">
            <div
                class="d-flex align-center body-2"
                :class="tab.key === selected ? ' light-blue--text text--darken-3' : (hover ? 'header_hover' : '')"
                style="height: 40px; width: 300px;"
                :style="'cursor:' + (tab.key === selected ? 'default' : 'pointer')"
                @click="pushRoute(tab.key, tab.route)"
            >
                <div class="d-flex justify-center align-center" style="height: 40px; width: 40px;">
                    <v-icon v-text="tab.icon" :color="tab.key === selected ? 'light-blue darken-3' : ''" />
                </div>

                <div class="pl-2 text-truncate" v-text="tab.label" />
            </div>
        </v-hover>
    </template>
</drawer>
</template>

<script>
export default {
    data () {
        return {
            collapse: 0,
        }
    },

    props: {
        entity:     { type: String, default: 'coins' },
        id:         { type: Number, default: null },
        section:    { type: String, default: null },
    },

    computed: {
        selected () {
            return this.section ?? this.tabs[0]?.key
        },

        tabs () {
            const tabs = [
                this.entity === 'coins' ? { key: 'types', icon: 'blur_circular' } : { key: 'coins', icon: 'copyright' },
                { key: 'production',    icon: 'zoom_out_map' },
                { key: 'basics',        icon: 'category' },
                { key: 'obverse',       icon: 'flip_to_front' },
                { key: 'reverse',       icon: 'flip_to_back' },
                { key: 'references',    icon: 'import_contacts' },
                { key: 'individuals',   icon: 'emoji_people' },
                { key: 'miscellaneous', icon: 'style' }
            ]

            if (this.entity === 'coins') tabs.unshift({ key: 'images', icon: 'camera_alt' })


            return tabs.map((el) => { return {
                key: el.key,
                route: '/' + [this.entity, 'edit', this.id, el.key].join('/'),
                label: this.$root.label(el.key),
                icon: el.icon
            }})
        },

        title () {
            return 'edit cn ' + this.entity.slice(0, -1) + ' ' + this.id + ' (' + this.selected + ')'
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
        }
    }
}
</script>
