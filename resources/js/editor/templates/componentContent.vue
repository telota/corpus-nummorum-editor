<template>
<div :class="'component-content' + (dialog ? ' component-content-dialog' : '')">
    <slot />
</div>
</template>


<script>

export default {
    props: {
        dialog: { type: Boolean, default: false }
    }
}

</script>
