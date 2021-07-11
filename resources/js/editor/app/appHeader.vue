<template>
<div>
    <v-card
        id="app-header"
        tile
        dark
    >
        <v-row no-gutters>

            <!-- App Name -->
            <v-col cols=4>
                <div class="d-flex align-center" style="height: 40px">
                    <div
                        class="pl-2 pb-1 title font-weight-thin text-uppercase text-truncate"
                        style="cursor: pointer"
                        v-text="$root.appName"
                        @click="$store.commit('setAbout', true)"
                    />
                </div>
            </v-col>

            <!-- Snack -->
            <v-col cols=4>
                <div class="d-flex justify-center align-center" style="height: 40px">
                    <v-fade-transition>
                        <div
                            v-if="snackMessage"
                            class="pl-2 pr-2 caption font-weight-medium text-truncate"
                            v-text="snackMessage"
                            :class="snackColor"
                        />
                    </v-fade-transition>
                </div>
            </v-col>

            <!-- Options -->
            <v-col cols=4>
                <div class="d-flex justify-end align-center" style="height: 40px">
                    <!-- Admin -->
                    <advbtn
                        v-if="user.level > 30 && $route.name !== 'admin'"
                        icon="font_download"
                        tooltip="Administration"
                        dark
                        medium
                        v-on:click="$router.push({ name: 'admin' })"
                    />

                    <!-- Browser -->
                    <advbtn
                        v-if="$route.name !== 'storage'"
                        icon="web"
                        tooltip="Browser"
                        dark
                        medium
                        v-on:click="$router.push({ name: 'storage' })"
                    />

                    <!-- Help -->
                    <advbtn
                        icon="help_center"
                        tooltip="Hilfe"
                        dark
                        medium
                        v-on:click="openInNewTab('/wiki')"
                    />

                    <!-- Account -->
                    <v-menu
                        v-model="showAccount"
                        offset-y
                        absolute
                        :position-x="0"
                        :position-y="40"
                        tile
                    >
                        <template v-slot:activator="{ on, attrs }">
                            <advbtn
                                :icon="showAccount ? 'indeterminate_check_box' : 'account_box'"
                                dark
                                medium
                                v-bind="attrs"
                                v-on="on"
                            />
                        </template>
                        <v-card
                            light
                            tile
                            :max-width="400"
                            :min-width="200"
                        >
                            <v-hover
                                v-for="(item, i) in accountMenu"
                                :key="'option' + i"
                                :disabled="item.disabled"
                                v-slot="{ hover }"
                            >
                                <div
                                    class="d-flex align-center pa-2"
                                    :class="hover ? 'grey lighten-3' : (item.disabled ? 'grey--text' : '')"
                                    :style="'cursor:' + (hover ? 'pointer' : 'default')"
                                    @click="item.action"
                                >
                                    <v-icon class="mr-2" v-text="item.icon" />
                                    <div v-text="item.text" />
                                </div>
                            </v-hover>
                        </v-card>
                    </v-menu>
                </div>
            </v-col>

        </v-row>
    </v-card>

    <v-card tile style="position: fixed; top: 0; width: 100%; height: 110px;" />
</div>
</template>


<script>

export default {
    data () {
        return {
            showAccount: false
        }
    },
    computed: {
        user () { return this.$root.user },
        snackMessage () { return this.$store.state.snack?.message ?? null },
        snackColor () { return this.$store.state.snack?.color ? (this.$store.state.snack.color + '--text') : '' },
        accountMenu () {
            return [
                { icon: 'info', text: 'About ' + this.$root.appName, action: () => { this.$store.commit('setAbout', true) } },
                { icon: 'logout', text: 'Logout', action: () => { this.$root.logout } }
            ]
        }
    },
    methods: {
        openInNewTab (link) {
            if (link) { window.open(link) }
        }
    }
}

</script>

<style scoped>
    #app-header {
        position: fixed;
        width: 100%;
        top: 0;
        z-index: 100;
        height: 40px;
    }
</style>
