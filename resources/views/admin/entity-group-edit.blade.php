<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Группа: {{ $entity_group->name }}</h2>
            <a href="{{ route('admin.entities.groups.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">К списку групп</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <p class="mb-4 text-sm text-green-700 bg-green-100 px-4 py-2 rounded">{{ session('success') }}</p>
            @endif
            @if(session('error'))
                <p class="mb-4 text-sm text-red-700 bg-red-100 px-4 py-2 rounded">{{ session('error') }}</p>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-3">Название группы</h3>
                    <form action="{{ route('admin.entities.groups.update', $entity_group) }}" method="post" class="flex gap-2">
                        @csrf
                        @method('patch')
                        <input type="text" name="name" value="{{ old('name', $entity_group->name) }}" class="flex-1 rounded-md border-2 border-gray-300 px-3 py-2">
                        <button type="submit" class="bg-teal-500 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded-md">Сохранить</button>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-3">Добавить сущность</h3>
                    <form action="{{ route('admin.entities.groups.mappings.store', $entity_group) }}" method="post" class="flex gap-2">
                        @csrf
                        <input type="text" name="entity" placeholder="название или slug" class="flex-1 rounded-md border-2 border-gray-300 px-3 py-2" required>
                        <button type="submit" class="bg-teal-500 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded-md">Добавить</button>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Сущности в группе ({{ $entity_group->mappings->count() }})</h3>
                    <ul class="divide-y divide-gray-200">
                        @forelse($entity_group->mappings as $mapping)
                            <li class="py-2 flex justify-between items-center">
                                <span class="text-gray-900">{{ $mapping->entity_name ?? ($slugToName[$mapping->entity_slug] ?? $mapping->entity_slug) }}</span>
                                <form action="{{ route('admin.entities.groups.mappings.destroy', $mapping) }}" method="post" onsubmit="return confirm('Удалить сущность из группы?');">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="text-red-600 hover:underline text-sm">Удалить</button>
                                </form>
                            </li>
                        @empty
                            <li class="py-4 text-gray-500">В группе пока нет сущностей.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
