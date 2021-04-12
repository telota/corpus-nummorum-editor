<template>
<div>
    
    <div class="d-flex component-wrap align-center mb-2">
        <subheader label="data_imported"></subheader>
        <div 
            v-if="data"
            class="caption ml-4"
            style="white-space: nowrap"
            v-html="to_string.simple_text(data.info.import) + ', ' + $handlers.format.date(l, data.info.date, true)"
        ></div>
    </div>

    <div v-if="!data" v-text="'--'"></div>

    <div v-else>
        <!-- Content -->
        <v-row>
            <!-- Description -->
            <v-col cols="12" sm="12" md="2" v-for="(language) in ['de', 'en']" :key="language">
                <div 
                    class="font-weight-bold text-uppercase mb-2" 
                    v-html="labels['description'] + '&ensp;(&nbsp;' + language.toUpperCase() + '&nbsp;)'"
                ></div>
                <div 
                    class="caption" 
                    v-html="to_string.simple_text(data.description[language])"
                ></div>
            </v-col>

            <!-- Obverse / Reverse -->
            <v-col cols="12" sm="12" md="2" v-for="(section) in ['obverse', 'reverse']" :key="section">
                <div
                    class="font-weight-bold text-uppercase mb-2" 
                    v-text="labels[section]"
                ></div>

                <!-- Legend -->
                <div v-if="!data[section].legend.string && !data[section].legend.description" v-text="'--'"></div>
                <div v-else>
                    <div 
                        v-if="data[section].legend.string" 
                        class="caption mt-1" 
                        v-html="to_string.simple_text(data[section].legend.string)"
                    ></div>
                    <div 
                        v-if="data[section].legend.description" 
                        class="caption mt-1" 
                        v-html="to_string.simple_text(data[section].legend.description)"
                    ></div>
                </div>

                <!-- Design -->
                <div 
                    class="caption mt-1" 
                    v-html="to_string.simple_text(data[section].design)"
                ></div>
            </v-col>

            <!-- Citations / Literatur -->
            <v-col cols="12" sm="12" md="2" v-for="(section) in ['citations', 'literature']" :key="section">
                <div 
                    class="font-weight-bold text-uppercase mb-2" 
                    v-html="labels[section]"
                ></div>
                <div 
                    class="caption" 
                    v-html="to_string.simple_text(data.references[section])"
                ></div>
            </v-col>
        </v-row>
    </div>

</div>
</template>

<script>

export default {
    data () {
        return {
            // CSS
            td_header:  'pa-1 caption d-flex align-start font-weight-thin text-uppercase pr-3',
            td_value:   'pa-1 caption',
            no_wrap:    'white-space: nowrap;',
            collapse:   'border-collapse: collapse; border-spacing: 0;'
        }        
    },

    props: {
        data: { type: Object }
    },

    computed: {
        // Localization
        l () { return this.$root.language },
        labels () { return this.$root.labels },

        to_string () { return this.$handlers.format.stringify_data }
    },

    created () {},

    methods: {}       
}

</script>