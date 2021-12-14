<template>
<div>
    <component-toolbar>
        TEST
    </component-toolbar>

    <component-content>
        <div class="ma-10" style="width: 500px; height:400px">
            <adv-canvas

            />
        </div>
    </component-content>
</div>
</template>

<script>
import advCanvas from './../../modules/advCanvas'

export default {
    components: {
        advCanvas
    }
}
</script>
