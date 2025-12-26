@props(['user', 'actionText' => 'Добавить сон', 'actionRoute' => 'reports.create', 'description' => null])

<div class="gradient-primary rounded-2xl p-6 text-white card-shadow">
    <h3 class="text-xl font-bold mb-2">Добро пожаловать, {{ $user->nickname }}!</h3>
    <p class="text-purple-100 mb-4 text-sm">
        {{ $description ?? 'Записывайте и анализируйте свои сновидения' }}
    </p>
    <a href="{{ route($actionRoute) }}" class="inline-block bg-white text-purple-600 px-4 py-2 rounded-lg font-semibold hover:bg-purple-50 transition-colors text-sm">
        <i class="fas fa-plus mr-2"></i>{{ $actionText }}
    </a>
</div>
















