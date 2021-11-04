<template>
<div>
    <div class="d-flex">
        <adv-btn
            :icon="checkedState ? 'radio_button_unchecked' : 'radio_button_checked'"
            :tooltip="(checkedState ? 'Deselect' : 'Select') + ' all ' + label"
            :disabled="items[0] ? false : true"
            color-main="blue_prim"
            color-hover="blue_sec"
            color-text="white"
            @click="$emit('checkall', true)"
        />

        <adv-btn
            icon="publish"
            :tooltip="'Publish selected ' + label"
            :disabled="!Items[0]"
            color-main="blue_prim"
            color-hover="blue_sec"
            color-text="white"
            @click="++run"
        />
    </div>

    <item-publisher-dialog
        group
        :entity="entity"
        :items="Items"
        :run="run"
        @loading="(emit) => { $emit('loading', emit) }"
        @refresh="$emit('refresh')"
    />
</div>
</template>

<script>
import itemPublisherDialog     from './itemPublisherDialog.vue'

export default {
    components: {
        itemPublisherDialog
    },

    data () {
        return {
            run: 0
        }
    },

    props: {
        entity:         { type: String, default: null },
        checkedState:   { type: Boolean, default: false },
        items:          { type: Array, default: () => [] }
    },

    computed: {
        label () {
            return this.$root.label(this.entity)
        },

        Items () {
            return this.items.filter((item) => item.state === true)
        }
    }
}
</script>
