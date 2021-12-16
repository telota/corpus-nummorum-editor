<template>
<diV>
    <div v-if="loading" v-text="'loading'" />
    <div v-else>
        <canvas
            id="adv-canvas"
            :width="width"
            :height="height"
            style="background-color: grey;"
        />
        <v-text-field v-model="rotation" />
        <v-btn tile v-text="'rotate'" @click="rotate" />
    </div>
</diV>
</template>

<script>
export default {
    data () {
        return {
            loading: true,
            width: 300,
            height: 300,
            rotation: 0,
            img: {}
        }
    },

    props: {
        src:        { type: String, default: 'https://digilib.bbaw.de/digitallibrary/servlet/Scaler?fn=silo10/thrakien/CNT_39683o_PC.tif&dw=500&dh=500' },
        contain:    { type: Boolean, default: true }
    },

    mounted () {
        const img = new Image()
        img.src = this.src
        img.onload = () => {
            this.img = img
            this.loading = false
            setTimeout(() => { this.drawImage() }, 10)
        }

        /*var canvas = document.getElementById('adv-canvas');
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

                let imgHeight = Math.ceil(originalHeight * factor)
                let imgWidth =  Math.ceil(originalWidth * factor)

                if (this.contain && (imgHeight > this.height || imgWidth > this.width)) {
                    let corect = 1
                    if (imgHeight > this.height) {
                        corect = this.height / imgHeight
                        imgHeight = Math.floor(imgHeight * corect)
                        imgWidth = Math.floor(imgWidth * corect)
                    }
                    if (imgWidth > this.width) {
                        corect = this.width / imgWidth
                        imgHeight = Math.floor(imgHeight * corect)
                        imgWidth = Math.floor(imgWidth * corect)
                    }
                }

                const offsetX = Math.round((this.width - imgWidth) / 2)
                const offsetY = Math.round((this.height - imgHeight) / 2)

                console.log(imgWidth, offsetX, imgHeight, offsetY)


                ctx.save()
                ctx.rotate(5*Math.PI/180)
                ctx.drawImage(img, offsetX, offsetY, imgWidth, imgHeight)
                ctx.restore()
                console.log(img)

                //const pixelColor = ctx.getImageData(offsetX, offsetY, 1, 1).data
                //console.log(pixelColor)

                //canvas.style['background-color'] = 'rgb(100, 0, 0)'
            }
            console.log('finish')
        }*/
    },

    methods: {
        getContext () {
            const canvas = document.getElementById('adv-canvas')
            return canvas.getContext ? canvas.getContext("2d") : null
        },

        drawImage () {
            const img = this.img
            const ctx = this.getContext()
            if (ctx) {
                const originalHeight = img.naturalHeight
                const originalWidth = img.naturalWidth
                const ratio = Math.round(originalHeight / originalWidth * 100) / 100
                const factor = Math.round(this.width / originalWidth * 100) / 100

                let imgHeight = Math.ceil(originalHeight * factor)
                let imgWidth =  Math.ceil(originalWidth * factor)

                if (this.contain && (imgHeight > this.height || imgWidth > this.width)) {
                    let corect = 1
                    if (imgHeight > this.height) {
                        corect = this.height / imgHeight
                        imgHeight = Math.floor(imgHeight * corect)
                        imgWidth = Math.floor(imgWidth * corect)
                    }
                    if (imgWidth > this.width) {
                        corect = this.width / imgWidth
                        imgHeight = Math.floor(imgHeight * corect)
                        imgWidth = Math.floor(imgWidth * corect)
                    }
                }

                const offsetX = Math.round((this.width - imgWidth) / 2)
                const offsetY = Math.round((this.height - imgHeight) / 2)

                ctx.drawImage(img, offsetX, offsetY, imgWidth, imgHeight)
            }
        },

        rotate () {
            const rotation = parseInt(this.rotation)
            if (rotation > 0) {
                const canvas = document.getElementById('adv-canvas')
                const ctx = canvas.getContext("2d")

                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.save()
                ctx.rotate(rotation*Math.PI/180)
                this.drawImage()
                ctx.restore()
            }
        }
    }
}
</script>
