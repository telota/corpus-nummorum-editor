<template>
    <small-dialog
        :show="show"
        icon="publish"
        text="Publisher"
        v-on:close="$emit('close', true)"
    >
        <div>
            TEST
        </div>
    </small-dialog>
</template>

<script>
export default {
    props: {
        show: { type: Boolean, default: false },
        entity: { type: String, default: null },
        items: { type: Array, default: () => [] }
    },

    methods: {
        async Publish (input, mode) {
            let items = input.filter(item => { return item.state === true || item.state === undefined })

            if (items[0]) {
                const response = await this.$root.DBI_INPUT_POST(
                    'publish',
                    'input',
                    {
                        entity: this.entity,
                        items: items.map((item) => { return item.id }),
                        mode: mode
                    }
                );

                if (response.success) {
                    this.$store.dispatch('showSnack', { color: 'success', message: response.success })
                    this.$emit('refresh', true)
                }
                else this.$store.dispatch('showSnack', { color: 'error', message: 'ERROR' })
            }
            else {
                this.$store.dispatch('showSnack', { color: 'error', message: 'No items selected!' })
                this.$emit('refresh', true)
            }
        }
    }
}
</script>
