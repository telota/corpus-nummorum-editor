<template>
<div>

<v-card tile class="appbar">
    <!-- Back to Main Menu -->
    <v-list class="transparent">
        <v-list-item-group class="mt-n2 mb-n2">
            <v-list-item
                style="white-space: nowrap;"
                @click="toMain()"
            >
                <span class="caption text-uppercase" v-text="$root.label('back_to_main_menu')"></span>                
            </v-list-item>
        </v-list-item-group>
    </v-list>

    <v-divider></v-divider>

    <!-- Tab Keys -->
    <v-list class="transparent">
        <v-list-item-group class="mt-n2 mb-n2">
            <v-list-item
                v-for="(item) in tabs" :key="item.value"
                :disabled="tab === item.value" 
                class="pr-5"
                :class="tab === item.value ? 'sysbar' : 'appbar'"
                v-text="item.text"
                style="white-space: nowrap;"
                @click="$emit('select', item.value)"
            ></v-list-item>
        </v-list-item-group>
    </v-list>

    <v-divider></v-divider>

    <!-- Import Data -->
    <v-list class="transparent">
        <v-list-item-group class="mt-n2 mb-n2">
            <v-list-item 
                class="pr-5"
                style="white-space: nowrap;"
                @click="$emit('show-imported', !show_imported)"
            >
                <span class="caption" v-text="$root.label('data_imported')"></span> 
                <v-icon class="ml-1" v-text="show_imported ? 'expand_less' : 'expand_more'"></v-icon>               
            </v-list-item>
        </v-list-item-group>
    </v-list>
</v-card>

</div>
</template>


<script>
export default {
    data () {
        return {
            tabs_indeces: {
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
        entity:         { type: String },
        tab:            { type: String },
        show_imported:  { type: Boolean, default: false }
    },

    computed: {
        // Localization
        l () { return this.$root.language },
        labels () { return this.$root.labels },

        tabs () {
            const self = this
            return this.tabs_indeces[this.entity].map((key) => { return { value: key, text: self.$root.label(key) }})
        }
    },

    methods: {
        toMain () {
            if (confirm('Are you sure you want to return to main menu? Unsaved data will be lost!')) {
                this.$router.push('/' + this.entity + '/edit')
            }
        }
    }
}
</script>