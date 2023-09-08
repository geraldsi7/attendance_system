<script>
    export default {
    props: {
        src: String
    }, 
}
</script>

<template>
    
    <img :src="src"  class="h-32 w-32 object-cover rounded-full">
</template>
