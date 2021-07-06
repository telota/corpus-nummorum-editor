<template>
<div
    class="d-flex align-center flex-grow-1"
    :class="boxed ? (boxClass + ' breadcrumbs-box') : ''"
    style="cursor: default; overflow-x: auto;"
    :style="height ? ('height:' + height + 'px;') : ''"
>

    <template v-for="(item, i) in items">
        <!-- Crumb -->
        <div
            :key="'crumb' + i"
            :style="!disabled && item.value !== false ? 'cursor: pointer' : ''"
            @click="setPath(item.value)"
        >
            <div
                v-if="item.text"
                v-text="item.text"
                class="text-truncate"
                :style="'max-width:' + getNodeWidth(i) + 'px'"
            />
            <v-icon
                v-else
                :class="boxed ? 'ml-2' : ''"
                v-text="item.icon"
            />
        </div>

        <!-- Divider -->
        <v-menu
            :key="'divider' + i"
            v-if="i < items.length - 1 || (!disabled && Object.keys(item.nodes)[0])"
            tile
            offset-y
            nudge-bottom="4"
            nudge-right="12"
        >
            <!-- Activator -->
            <template v-slot:activator="{ on, attrs }">
                <v-btn
                    small
                    icon
                    class="ml-1 mr-1"
                    v-bind="attrs"
                    v-on="on"
                    :disabled="disabled || !Object.keys(item.nodes)[0]"
                >
                    <v-icon v-text="dividerBtn" class="mr-n1" />
                </v-btn>
            </template>

            <v-card tile>
                <v-hover
                    v-for="(node, nodepath) in item.nodes"
                    :key="nodepath"
                    v-slot="{ hover }"
                >
                    <div
                        class="pa-2 caption"
                        :class="hover ? 'grey lighten-3' : (item.disabled ? 'grey--text' : '')"
                        :style="'cursor:' + (hover ? 'pointer' : 'default')"
                        v-text="node"
                        @click="setPath(nodepath)"
                    />
                </v-hover>
            </v-card>
        </v-menu>

    </template>
</div>
</template>

<script>

export default {
    data () {
        return {
            boxClass: 'pa-2 innerShadow'
        }
    },

    props: {
        directory:  { type: [String, Boolean], default: null },
        disabled:   { type: Boolean, default: false },
        routing:    { type: Boolean, default: false },
        boxed:      { type: Boolean, default: false },
        height:     { type: Number, default: 0 },
        hideHome:   { type: Boolean, default: false },
        homeBtn:    { type: String, default: 'home' },
        dividerBtn: { type: String, default: 'navigate_next' }
    },

    computed: {
        directories () { return this.$store.state.storage.directory.items },
        items () {
            const items = []

            if (!this.hideHome) {
                items.push({
                    value: this.$route.path === '/storage' ? false : null,
                    icon: this.homeBtn,
                    nodes: this.getNodes()
                })
            }
            if (this.directory) {
                const split = this.directory.trim('/').split('/')
                const length = split.length

                split.forEach((item, i) => {
                    const array = [...split]
                    const value = i < length - 1 ? array.splice(0, i + 1).join('/') : false
                    const nodes = this.getNodes(value ? value : this.directory)

                    items.push({
                        value,
                        text: item,
                        nodes
                    })
                })
            }

            return items
        }
    },

    methods: {
        setPath (dir) {
            if (dir !== false) {
                if (this.disabled) console.error('Breadcrumbs: setPath disabled!')
                else {
                    /*if (this.routing) {
                        if (dir) dir = dir.replaceAll('/', ':')
                        const params = dir ? { directory: dir } : {}
                        if (this.$route.params?.directory != dir) this.$router.push({ name: 'storage', params: params })
                    }
                    else*/ this.$emit('setPath', dir)
                }
            }
        },

        getNodes (path) {
            const directories = this.directories
            const nodes = {}

            if (path) {
                directories.forEach((dir) => {
                    if (dir.startsWith(path + '/')) {
                        const node = dir.slice(path.length + 1).split('/')[0]
                        nodes[path + '/' + node] = node
                    }
                })
            }
            else {
                directories.filter((dir) => !dir.includes('/')).forEach((node) => {
                    nodes[node] = node
                })
            }

            return nodes
        },

        getNodeWidth (i) {
            if (this.items.length < 4) return 300
            if (i > this.items.length - 3) return 150
            if ((this.hideHome && i < 1) || (!this.hideHome && i < 2)) return 200
            return 100
        }
    }
}
</script>

<style scoped>
    .breadcrumbs-box {
        border: 1px solid black;
    }
</style>
