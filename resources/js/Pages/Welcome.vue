<script setup>
import { Head, Link } from '@inertiajs/inertia-vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <Head title="Welcome" />

    <div class="relative flex flex-col items-top sm:justify-center min-h-screen bg-gray-100 items-center sm:pt-0">
        <div class="my-1">
            <Link href="/">
                <ApplicationLogo class="w-20 h-20 fill-current text-gray-500" />
            </Link>           
        </div>
        <div class="mb-8">
            <p class="font-bold text-xl md:text-3xl">Student Attendance System</p>
        </div>
        
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-9">
                
                <div class="bg-white shadow-sm rounded-md p-6">
                    <div class="flex justify-center">
                        <span class="material-symbols-outlined text-6xl"> school </span>
                    </div>
                    <div class="text-center">
                        <p class="font-bold text-lg mt-5">Students</p>
                        <p>Enter this section to access the students' portal.</p>
                    </div>
                    <div class="flex justify-center mt-5">
                        <Link :href="route('student.show.login')"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        go
                        </Link>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-md p-6">
                    <div class="flex justify-center">
                        <span class="material-symbols-outlined text-6xl">
                            person
                        </span>
                    </div>
                    <div class="text-center">
                        <p class="font-bold text-lg mt-5">Lecturers</p>
                        <p>Enter this section to access the lecturers' portal.</p>
                    </div>
                    <div class="flex justify-center mt-5">
                        <Link :href="route('lecturer.show.login')"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        go
                        </Link>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-md p-6">
                    <div class="flex justify-center">
                        <span class="material-symbols-outlined text-6xl"> supervisor_account </span>
                    </div>
                    <div class="text-center">
                        <p class="font-bold text-lg mt-5">Administators</p>
                        <p>Enter this section to access the administators' portal.</p>
                    </div>
                    <div class="flex justify-center mt-5">
                        <Link :href="route('admin.show.login')"
                            class="nline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        go
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
