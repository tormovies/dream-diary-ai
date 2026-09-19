@if(\App\Support\MaintenanceMode::enabled())
    @if(auth()->check() && auth()->user()->isAdmin())
        <div class="bg-red-600 text-white text-center text-sm font-semibold py-2 px-4">
            <i class="fas fa-tools mr-2"></i>Режим сервисных работ включён — изменение данных для пользователей заблокировано.
            <a href="{{ route('admin.settings') }}" class="underline ml-1">Управление</a>
        </div>
    @else
        <div class="bg-yellow-100 dark:bg-yellow-900 border-b border-yellow-300 dark:border-yellow-700 text-yellow-800 dark:text-yellow-200 text-center text-sm py-2 px-4">
            <i class="fas fa-info-circle mr-2"></i>На сайте идут сервисные работы: чтение доступно, сохранение данных временно отключено.
        </div>
    @endif
@endif
