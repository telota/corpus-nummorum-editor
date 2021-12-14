<template>
<v-text-field dense outlined filled clearable
    v-model="value"
    :label="'cn ' + entity.slice(0, -1) + ' ID'"
    :prepend-icon="icon"
    :hint="'Click on Icon to open ' + entity + ' dialog'"
    :rules="[rule]"
    @input="select"
    @keyup.enter="$emit('enter', false)"
    @keydown.enter.shift="$emit('enter', true)"
>
    <template v-slot:prepend>
        <!-- Botton -->
        <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
                <v-hover v-slot="{ hover }">
                    <v-icon
                        v-bind="attrs"
                        v-on="on"
                        :class="hover ? 'blue_sec--text' : 'blue_prim--text'"
                        v-text="icon"
                        @click="dialog = true"
                    />
                </v-hover>
            </template>
            <span v-text="'Click to open ' + entity.slice(0, -1) + ' dialog.'"></span>
        </v-tooltip>

        <component
            v-if="dialog"
            :is="entity"
            dialog
            select
            :selected="selected"
            v-on:select="select"
            v-on:close="dialog = false"
        />
    </template>
</v-text-field>
</template>

<script>
export default {
    data () {
        return {
            value:  this.selected,
            dialog: false,
        }
    },

    props: {
        entity:     { type: String, required: true },
        selected:   { type: Number, default: null },
        noConfirm:  { type: Boolean, default: false }
    },

    computed: {
        icon () { return this.entity === 'coins' ? 'copyright' : 'blur_circular' }
    },

    watch: {
        selected: function () {
            this.value = this.selected
        }
    },

    methods: {
        select (value) {
            if (['number', 'string'].includes(typeof value)) value = parseInt(value)
            if (!value) value = null
            //console.log(value)

            this.$emit('select', value)

            if (this.noConfirm) this.dialog = false
            else if (this.dialog && value) {
                if (confirm('ID ' + value + ' selected! Close Dialog?')) this.dialog = false
            }
        },

        rule (v) {
            if (!v && v !== 0 && v !== '0') return true
            const pattern = /^(null|[1-9][0-9]*)$/
            return pattern.test(v) || 'ID must be numeric.'
        }
    }
}
</script>
