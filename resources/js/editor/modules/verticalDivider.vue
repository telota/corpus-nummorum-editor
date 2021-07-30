<template>
    <div :class="color + ' fill-height width-1px'">test</div>
</template>

<script>
export default {
    props: {
        color: { type: String, default: 'header_bg' }
    }
}
</script>
