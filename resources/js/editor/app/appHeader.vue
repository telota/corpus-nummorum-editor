<template>
<div id="app-header">

    <!-- Header -->
    <div>
        <!-- Snack -->
        <v-fade-transition>
            <div
                v-if="snackMessage"
                class="d-flex align-center justify-center"
                :style="[
                    'position: fixed',
                    'pointer-events: none',
                    'cursor: default',
                    'left: 50%',
                    'margin-left: -250px',
                    'z-index: 102',
                    'height: 40px',
                    'width: 500px',
                    'background:' + snackBg
                ].join(';\n')"
            >
                <div
                    :class="snackColor"
                    class="pl-2 pr-2 caption font-weight-medium text-truncate text-center"
                    v-html="snackMessage"
                    style="width: 300px; pointer-events: none;"
                />
            </div>
        </v-fade-transition>

        <div class="header_bg d-flex align-center" style="height: 40px; width: 100%;">

            <!-- App Name -->
            <a href="/editor#/dashboard" class="invert--text">
                <v-hover v-slot="{ hover }">
                    <div
                        class="d-flex align-center justify-center"
                        :class="hover ? 'header_hover' : ''"
                        style="height: 40px; width: 50px; cursor: pointer;"
                    >
                        <div
                            class="font-weight-bold light-blue--text text--darken-3"
                            style="font-size: 24px; line-height: 24px;" v-text="'CN'"
                        />
                    </div>
                </v-hover>
            </a>

            <!-- Navigation -->
            <app-navigation />

            <v-spacer />

            <!-- Controls -->
            <adv-btn
                :text="$root.language.toUpperCase()"
                :tooltip="$root.language === 'de' ? 'Switch to English' : 'Zu Deutsch wechseln'"
                medium
                color-hover="header_hover"
                @click="$root.changeSettings('language', $root.language === 'de' ? 'en' : 'de')"
            />
            <adv-btn
                :icon="$vuetify.theme.dark ? 'invert_colors' : 'invert_colors_off'"
                :tooltip="$vuetify.theme.dark ? 'Switch to light Theme' : 'Switch to dark Theme'"
                medium
                color-hover="header_hover"
                @click="$root.changeSettings('color_theme', $vuetify.theme.dark === true ? 0 : 1)"
            />
            <adv-btn
                :icon="nightMode ? 'brightness_5' : 'brightness_4'"
                :tooltip="(nightMode ? 'Disabled' : 'Enabled') + ' night mode'"
                medium
                color-hover="header_hover"
                @click="$root.changeSettings('night', nightMode ? 0 : 1)"
            />
            <!-- Logout -->
            <adv-btn
                icon="power_settings_new"
                tooltip="Logout"
                medium
                color-hover="header_hover"
                @click="$root.logout()"
                class="mr-n4"
            />

        </div>

        <!-- Loading -->
        <v-progress-linear
            :indeterminate="$root.loading"
            height="1"
            color="light-blue darken-2"
            background-color="grey"
            :background-opacity="0.3"
        />
    </div>


</div>
</template>


<script>

import appNavigation from './appNavigation.vue'

export default {
    components: {
        appNavigation
    },

    computed: {
        snackMessage () {
            return this.$store.state.snack?.message ?? null
        },
        snackColor () {
            return (this.$store.state.snack?.color ? this.$store.state.snack.color : 'invert') + '--text'
        },
        snackBg () {
            const color = this.$vuetify.theme.dark ? '54,54,54' : '230,230,230'
            return 'linear-gradient(' +
                'to right,' +
                'rgba(0,0,0,0) 0%,' +
                'rgba(' + color + ',0.75) 20%,' +
                'rgba(' + color + ',0.75) 80%,' +
                'rgba(0,0,0,0) 100%)'
        },
        nightMode () {
            return this.$root.settings?.night ? true : false
        }
    }
}

</script>

<style scoped>
    #app-header {
        position: fixed;
        width: 100%;
        top: 0;
        z-index: 300;
        height: 41px;
    }
</style>
