<template>
    <div>

        <!-- Info Bar -->
        <v-card tile class="appbar d-flex component-wrap align-center">
            <div class="font-weight-bold ml-4" v-text="(this.item.id ? (this.entity === 'coins' ? 
                    this.$editor_format.coinName(this.item.id, this.item.mint.text.de) :
                    this.$editor_format.typeName(this.item.id, this.item.mint.link)
                ) : 'loading ...')"
            ></div>
            <v-spacer></v-spacer>
            <v-btn text disabled>
                <v-icon v-text="entity === 'types' ? 'blur_circular' : 'copyright'"></v-icon>
            </v-btn>
        </v-card>

    </div>
</template>



<script>

export default
{ 
    data ()
    {
        return {
        }
    },

    props: 
    {
        entity: { type: String },
        item:   { type: Object }
    },

    computed: 
    {
    },

    watch:
    {
    },

    created () 
    {
    },
    
    methods: 
    {
        
    }
}

</script>