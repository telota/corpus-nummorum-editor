<template><div></div>
</template>


<script>
export default 
{
    props:
    {
        data:       {type: Object}
    },

    created ()
    {
        if (!this.data.user.id) 
        {
            alert ('Error: Did not receive any data about current user! Please contact the CN IT Team!')
        }
        else
        {
            this.$root.InitializeSession (this.data);
        }
    }

}
</script>