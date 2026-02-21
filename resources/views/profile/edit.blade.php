<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <p class="text-gray-600 dark:text-gray-400">
                    Welcome to your AWAS Profile, {{ auth()->user()->name }}!
                </p>
            </div>
        </div>
    </div>
</x-app-layout>