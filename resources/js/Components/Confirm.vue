<script>
    export default {
    emits: ['destroy','close'],
    props: {
        type: {
            type: String,
            default: 'submit',
        },
        dataLog: Number
    }, 
}
</script>

<template>
    <div class="flex justify-center">
            <span class="material-symbols-outlined text-9xl text-yellow-500">
                exclamation
            </span>
        </div>
            <slot /> 
         
        <div class="flex justify-center mt-6 gap-4">      
            <button @click="$emit('destroy')" class="px-6 py-2.5 bg-purple-600 text-white font-bold text-md leading-tight capitalize rounded shadow-md hover:bg-purple-700 hover:shadow-lg focus:bg-purple-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-purple-800 active:shadow-lg transition duration-150 ease-in-out">
                Yes
            </button>
            <button class="px-6 py-2.5 bg-gray-200 text-gray-700 font-bold text-md leading-tight capitalize rounded shadow-md hover:bg-gray-300 hover:shadow-lg focus:bg-gray-300 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-gray-400 active:shadow-lg transition duration-150 ease-in-out" @click="$emit('close')">
                No
            </button> 
        </div>    
</template>
