<x-layouts.app :title="__('Create User')">

    <div class=" max-w-3xl mx-auto bg-[#BFE0E0] dark:bg-[#354A54] p-6 text-center text-xl font-bold my-2">
        Create User
    </div>


    <div class="max-w-3xl mx-auto p-6  bg-[#F3F4F6] dark:bg-[#1A1D23] rounded-xl shadow">
        <h1 class="text-xl font-semibold mb-4">Create User</h1>

        <form action="{{ route('users.store') }}" method="POST" class="space-y-6">
            @csrf
            @include('users._form', ['user' => null])
        </form>
    </div>
</x-layouts.app>
