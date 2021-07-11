<template>
</template>


<script>

export default {
    props: {
        data: { type: Object, required: true },
    },

    created () {
        this.getSettings()
    },

    mounted () {
        this.greetUser()
    },

    methods: {
        getSettings () {
            if (!this.data) alert('Error: App didn\'t receive any backend data!')
            else {
                const data = this.data
                if (data.appName) this.$root.appName = data.appName

                console.log('New App instance of ' + this.$root.appName + ' created:', data)

                this.$root.baseURL  = data.baseURL
                this.$root.sparql   = data.sparql
                this.$root.language = Object.keys(this.$localization).includes(data.language) ? data.language : 'en'

                this.$root.user     = data.user
                this.$root.settings = data.settings
                this.$root.log      = data.log
                this.$root.system   = data.system

                this.$store.commit('setAppName', this.$root.appName)
                this.$store.commit('setBaseURL', this.$root.baseURL)
                this.$store.commit('setSparql', this.$root.sparql)
                this.$store.commit('setLanguage', this.$root.language)

                this.$store.commit('setUser', this.$root.user)
                this.$store.commit('setSettings', this.$root.settings)
                this.$store.commit('setLog', this.$root.log)
                this.$store.commit('setSystem', this.$root.system)
            }
        },

        greetUser () {
            let message = null

            const currentdate = new Date()
            const hour        = currentdate.getHours()

            if      ( hour > 4 && hour < 12)    { message = 'Good Morning!' }
            else if ( hour > 11 && hour < 17)   { message = 'Good Afternoon!' }
            else if ( hour > 16 && hour < 23)   { message = 'Good Evening!' }
            else                                { message = 'Pretty late, isn\'t it?'}

            if (message) this.$store.dispatch('showSnack', { message })
        }
    }
}

</script>
