<x-app-layout>
    <x-slot name="header">
        <h2>Register Student</h2>
    </x-slot>

    <div class="p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
        <form method="POST" action="{{ route('admin.students.store') }}">
            @csrf
            <input type="text" name="name" placeholder="Name" class="border p-2 w-full mb-2">
            <input type="email" name="email" placeholder="Email" class="border p-2 w-full mb-2">
            <input type="password" name="password" placeholder="Password" class="border p-2 w-full mb-2">
            <input type="password" name="password_confirmation" placeholder="Confirm Password" class="border p-2 w-full mb-2">
            <input type="number" name="grade_level" placeholder="Grade Level" class="border p-2 w-full mb-2">
            <input type="text" name="section" placeholder="Section" class="border p-2 w-full mb-2">
            <button type="submit" class="bg-blue-600 text-white p-2 rounded">Register</button>
        </form>
    </div>
</x-app-layout>
