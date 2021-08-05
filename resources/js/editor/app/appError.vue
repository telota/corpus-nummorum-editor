<template>
<small-dialog
    :show="error.active"
    icon="warning"
    :text="'Warning: ' + (error.validation ? 'Validation Issue' : 'System Error')"
    background="tile_bg"
    v-on:close="$root.error.active = false"
>
    <!-- Validation Issue -->
    <div v-if="error.validation">
        <p class="mb-5 text-center">
            Sorry, a validation issue occured.<br />
            Please revise your data and try again.<br/>
            Thank you!
        </p>
        <p style="white-space: pre-line">
            <b>Message:</b><br/>
            <span v-html="printValidation(error.validation)" />
        </p>
    </div>

    <!-- Error -->
    <div v-else>
        <p class="mb-5 text-center">
            Sorry, a System Error occured.<br />
            Please provide the information given below to our IT Team.<br/>
            Thank you!
        </p>

        <p><b>Status:&ensp;</b>{{ error.status }}</p>
        <p><b>Resource:&ensp;</b>{{ error.resource ? error.resource : '--' }}</p>
        <p><b>Exception:&ensp;</b>{{ error.exception ? error.exception : '--' }}</p>
        <p><b>Message:&ensp;</b>{{ error.message ? error.message : '--' }}</p>
        <p><b>Parameters:&ensp;</b><span class="caption">{{ error.params ? error.params : '--' }}</span></p>
    </div>

    <v-btn text block v-text="'Close'" @click="$root.error.active = false" />
</small-dialog>
</template>


<script>
export default {
    computed: {
        language () { return this.$root.language },
        error () { return this.$root.error }
    },
    methods: {
        printValidation (validation) {
            if (!validation) return 'unknown issue - might be a system Error. Please contact IT'
            if (typeof validation === 'string') return validation
            if (validation[this.language]) return validation[this.language]
            if (validation.en) return validation.en
            return JSON.stringify(validation)
        }
    }
}
</script>
