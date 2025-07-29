<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Users List') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class=" dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-hidden shadow-xl sm:rounded-lg">
                    @if ($users->isEmpty())
                        <div class="text-center text-gray-600 dark:text-gray-300 py-10">
                            <p class="text-lg">{{ __('No users available at the moment.') }}</p>
                        </div>
                    @else
                        <x-table>
                            <x-slot name="thead">
                                <th class="px-6 py-3">Id</th>
                                <th class="px-6 py-3">Name</th>
                                <th class="px-6 py-3">Email</th>
                                <th class="px-6 py-3">Role</th>
                                <th class="px-6 py-3">Update Imobiliare Profile</th>
                                <th class="px-6 py-3">Change Role</th>
                            </x-slot>

                            @foreach ($users as $user)
                                <tr class="border-b hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4">{{ $user->id }}</td>
                                    <td class="px-6 py-4">{{ $user->name }}</td>
                                    <td class="px-6 py-4">{{ $user->email }}</td>
                                    <td class="px-6 py-4">
                                        <x-badge :color="$user->getRoleNames()[0] === 'Admin' ? 'red' : ($user->getRoleNames()[0] === 'Agent' ? 'yellow' : ($user->getRoleNames()[0] === 'Lead' ? 'blue' : 'white'))">
                                            {{ ucfirst($user->getRoleNames()[0]) }}
                                        </x-badge>
                                    </td>
                                    <td class="px-6 py-4">
                                        <form action="{{ route('imobiliare.agent.update', $user->id) }}" method="POST">
                                            @csrf
                                            <x-primary-button>  {{ __('Update Agent Profile(Imobiliare)') }}</x-primary-button>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4">
                                        <form action="{{ route('users.update-role', $user->id) }}" method="POST">
                                            @csrf
                                            <x-select onchange="this.form.submit()" name="role" label="Role" :options="['Admin' => 'Admin', 'Agent' => 'Agent', 'Lead' => 'Lead']" />
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </x-table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
