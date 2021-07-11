<template>
<div v-if="$root.loading" class="loader loader-half-transparent">
    <div class="loader-background">
        <div class="loader-text"><b v-text="$root.appName" /></div>
        <div class="loader-spinner"></div>
    </div>
</div>
</template>


<script>

export default {}

</script>
