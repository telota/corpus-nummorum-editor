<template>
<div>
    <component-toolbar>
        TEST
    </component-toolbar>

    <!--<component-content>
        <div class="ma-10" style="width: 500px; height:400px">
            <adv-canvas

            />
        </div>
    </component-content>-->
    <component-content>
        <!--<v-text-field v-model="src" />-->

        <div class="ma-10 d-flex justify-center">
            <div style="width: 850px;">
                <img-transformer :src="src" />
        </div>
        </div>
    </component-content>
</div>
</template>

<script>
import advCanvas from './../../modules/advCanvas'
import imgTransformer from './../../modules/imgTransformer'

export default {
    components: {
        advCanvas,
        imgTransformer
    },

    data () {
        return {
            src: '41'
        }
    }
}
</script>
