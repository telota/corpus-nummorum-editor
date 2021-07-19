<template>
<drawer
    :collapse="collapse"
    :hover-width="200"
>
    <template v-slot:content>
        <v-hover v-slot="{ hover }" v-for="(tab, t) in tabs" :key="'tab' + t">
            <div
                class="d-flex align-center body-2"
                :class="tab.key === section ? ' light-blue--text text--darken-3' : (hover ? 'header_hover' : '')"
                style="height: 40px; width: 300px;"
                :style="'cursor:' + (tab.key === section ? 'default' : 'pointer')"
                @click="pushRoute(tab.key, tab.route)"
            >
                <div class="d-flex justify-center align-center" style="height: 40px; width: 40px;">
                    <div class="font-weight-bold body-1" v-text="tab.abb" />
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

            navigationElements: {
                types: [
                    'coins',
                    'production',
                    'basics',
                    'obverse',
                    'reverse',
                    'references',
                    'individuals',
                    'miscellaneous'
                ],
                coins: [
                    'images',
                    'types',
                    'production',
                    'basics',
                    'obverse',
                    'reverse',
                    'references',
                    'individuals',
                    'miscellaneous'
                ]
            }
        }
    },

    props: {
        entity: { type: String, default: 'coins' },
        id: { type: Number, default: null },
        section: { type: String, default: null },
    },

    computed: {
        select () {
            return this.section ?? this.navigationElements[this.entity][0]
        },

        tabs () {
            return this.navigationElements[this.entity].map((key) => { return {
                key,
                route: '/' + [this.entity, 'edit', this.id, key].join('/'),
                label: this.$root.label(key),
                abb: this.$root.label(key).slice(0, 2)
            }})
        }
    },

    methods: {
        pushRoute (key, route) {
            if (key !== this.section) this.$router.push(route)
            setTimeout(() => { ++this.collapse }, 0)
        }
    }
}
</script>
