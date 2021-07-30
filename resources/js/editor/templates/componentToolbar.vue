<template>
<div :class="background + (' component-toolbar' + (dialog ? ' component-toolbar-dialog' : ''))">
    <div
        :class="classes"
        :style="'width: 100%; height:' + this.height + 'px;'"
    >
        <slot />
    </div>
</div>
</template>


<script>

export default {

    props: {
        dialog:     { type: Boolean, default: false },
        noFlex:     { type: Boolean, default: false },
        height:     { type: Number, default: 50 },
        background: { type: String, default: 'header_bg' },
    },

    computed: {
        classes () {
            return this.noFlex ? '' : 'd-flex align-center'
        }
    }
}

</script>
