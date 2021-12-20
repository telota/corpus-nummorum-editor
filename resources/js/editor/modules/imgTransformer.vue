<template>
<div class="pa-2">
    <div class="d-flex" style="max-width: 1200px">
        <div style="width: 50%">
            <v-responsive
                id="img-container"
                aspect-ratio="1"
                style="position: relative; width: 100%; outline: 1px solid black;"
                :style="'background-color: rgb(' + background + ')'"
            >
                <v-progress-circular
                    v-if="loading"
                    color="red"
                    width="2"
                    indeterminate
                    :size="Math.floor(containerWidth - 16)"
                    style="margin: 8px;"
                >
                    <b class="title">updating ...</b>
                </v-progress-circular>

                <template v-else>
                    <img
                        id="img-content"
                        :src="SRC"
                        :style="imgStyles"
                        @load="setImageRatio"
                    />
                    <template v-if="cross">
                        <div style="position: absolute; opacity: 0.5; border-bottom: 2px dashed red; width: 100%; height: calc(50% - 1px);" />
                        <div style="position: absolute; opacity: 0.5; border-right: 2px dashed red; height: 100%; width: calc(50% - 1px);" />
                    </template>
                    <div
                        v-if="circle"
                        style="
                            position: absolute;
                            opacity: 0.5;
                            top: 8px;
                            right: 8px;
                            bottom: 8px;
                            left: 8px;
                            border-radius: 50%;
                            outline: 2px dashed red
                        "
                    />
                </template>
            </v-responsive>
        </div>

        <div class="pl-5" style="width: 50%;">
            <div class="pl-3 pr-3 pb-3" style="outline: 1px solid grey">
                <div class="pl-2 pr-1 mb-2 d-flex justify-center">
                    <v-checkbox v-model="circle" label="Show Circle" class="mr-4" />
                    <v-checkbox v-model="cross" label="Show Cross" class="ml-4" />
                </div>

                <div class="pl-2 pr-1 d-flex align-center">
                    <b>Rotation</b>
                    <v-spacer />
                    <div v-text="rotate + '°'" />
                    <div class="ml-3" style="cursor: pointer" v-html="'&#9587;'" @click="rotate = 0" />
                </div>
                <v-slider
                    v-model="rotate"
                    :max="180"
                    :min="-180"
                />

                <div class="pl-2 pr-2 d-flex align-center">
                    <b>Scale</b>
                    <v-spacer />
                    <div v-text="Scale + '%'" />
                    <div class="ml-3" style="cursor: pointer" v-html="'&#9587;'" @click="scale = 100" />
                </div>
                <v-slider
                    v-model="scale"
                    :max="200"
                    :min="0"
                />

                <div class="pl-2 pr-2 d-flex align-center">
                    <b>Offset X</b>
                    <v-spacer />
                    <div v-text="offsetX + '%'" />
                    <div class="ml-3" style="cursor: pointer" v-html="'&#9587;'" @click="offsetX= 0" />
                </div>
                <v-slider
                    v-model="offsetX"
                    :max="100"
                    :min="-100"
                />

                <div class="pl-2 pr-2 d-flex align-center">
                    <b>Offset Y</b>
                    <v-spacer />
                    <div v-text="offsetY + '%'" />
                    <div class="ml-3" style="cursor: pointer" v-html="'&#9587;'" @click="offsetY = 0" />
                </div>
                <v-slider
                    v-model="offsetY"
                    :max="100"
                    :min="-100"
                />

                <v-btn tile block v-text="'save'" :disabled="true || loading" @click="save" />
            </div>
        </div>
    </div>


</div>
</template>

<script>
export default {
    data () {
        return {
            loading: true,

            rotate: 0,
            scale: 100,
            offsetX: 0,
            offsetY: 0,
            background: '128,128,128',

            circle: true,
            cross: true,

            containerWidth: 0,
            containerHeight: 0,
            ratio: 1
        }
    },

    props: {
        src: { type: String, default: null }
    },

    computed: {
        SRC () {
            //return '/storage/coins/' + this.src + '/lg.jpeg'
            return 'https://digilib.bbaw.de/digitallibrary/servlet/Scaler?fn=silo10/thrakien/CNT_39683o_PC.tif&dw=750&dh=750'
        },

        imgStyles () {
            const width = Math.round((this.containerWidth * this.Scale * (this.ratio < 1 ? this.ratio : 1)) / 100)
            const height = Math.round((this.containerHeight * this.Scale * (this.ratio > 1 ? this.ratio : 1)) / 100)
            const left = Math.floor((this.containerWidth - width) / 2)
            const top = Math.floor((this.containerHeight - height) / 2)

            return [
                'position: absolute',
                'transform: rotate(' + this.rotate + 'deg);',
                'left: calc(' + left + 'px + ' + this.offsetX + '%)',
                'top: calc(' + top + 'px + ' + this.offsetY + '%)',
                'width:' + width + 'px',
                'height:' + height + 'px',
            ].join(';')
        },

        Scale () {
            if (this.scale === 100) return this.scale
            return Math.round(Math.pow(Math.exp((this.scale / 100) - 1), 1.1) * 100)
        }
    },

    methods: {
        setContainerSize () {
            const el = document.getElementById('img-container')

            if (el) {
                this.containerWidth = el.clientWidth
                this.containerHeight = el.clientHeight
            }
            else console.error('setContainerSize: element "img-container" not found')
        },

        setImageRatio () {
            if (!this.loading) {
                const el = document.getElementById('img-content')

                if (el) this.ratio = Math.round(el.naturalWidth / el.naturalHeight * 100) / 100
                else console.error('setImageRatio: element "img-content" not found')
            }
        },

        async save () {
            this.loading = true

            await axios.post('/storage-api/action/edit',
                Object.assign ({}, {
                    path: this.src,
                    transformation: {
                        offsetX: this.offsetX,
                        offsetY: this.offsetY,
                        rotate:  this.rotate,
                        scale: this.Scale
                    }
                }))
                .then(async (response) => {
                    console.log(response)
                })
                .catch((error) => {
                    console.error(error.message)
                    this.$root.AXIOS_ERROR_HANDLER(error)
                })

            this.loading = false
        }
    },

    async mounted () {
        this.loading = true

        this.setContainerSize()
        window.addEventListener('resize', this.setContainerSize, { passive: true })

        let t = await axios.get('/storage/coins/41/transformation.json')
        if (t?.data) {
            t = t.data
            this.rotate = t.rotate
            this.scale = t.scale
            this.offsetX = t.offsetX
            this.offsetY = t.offsetY
            this.background = t.background
        }

        this.loading = false
    }
}
</script>
