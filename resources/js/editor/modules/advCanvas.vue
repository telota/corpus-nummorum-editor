<template>
    <canvas
        id="adv-canvas"
        :width="width"
        :height="height"
        style="background-color: grey;"
    />
</template>

<script>
export default {
    data () {
        return {
            width: 300,
            height: 300
        }
    },

    props: {
        src:        { type: String, default: 'https://digilib.bbaw.de/digitallibrary/servlet/Scaler?fn=silo10/thrakien/CNT_39683o_PC.tif&dw=500&dh=500' },
        contain:    { type: Boolean, default: false }
    },

    mounted () {

        var canvas = document.getElementById('adv-canvas');
        if (canvas.getContext) {
            const ctx = canvas.getContext("2d")

            //ctx.fillStyle = "rgb(255,255,255)"
            //ctx.fillRect(0, 0, 200, 200);

            const img = new Image()
            img.src = this.src
            img.onload = () => {
                const originalHeight = img.naturalHeight
                const originalWidth = img.naturalWidth
                const ratio = Math.round(originalHeight / originalWidth * 100) / 100
                const factor = Math.round(this.width / originalWidth * 100) / 100

                let imgHeight = originalHeight * factor
                let imgWidth =  originalWidth * factor

                if (this.contain && (imgHeight > this.height || imgWidth > this.width)) {

                }

                const offsetX = Math.round((this.width - imgWidth) / 2)
                const offsetY = Math.round((this.height - imgHeight) / 2)

                console.log(imgWidth, offsetX, imgHeight, offsetY)

                ctx.drawImage(img, offsetX, offsetY, imgWidth, imgHeight)
                canvas.style['background-color'] = 'rgb(100, 0, 0)'
            }
        }
    }
}
</script>
