@extends('layouts.base')

@section('title', 'Страница удалена (410)')

@section('content')
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="max-w-2xl w-full">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 md:p-12 border border-gray-200 dark:border-gray-700">
                <div class="flex justify-center mb-6">
                    <div class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                        <i class="fas fa-ban text-4xl text-gray-500 dark:text-gray-400" aria-hidden="true"></i>
                    </div>
                </div>

                <h1 class="text-3xl font-bold text-center text-gray-900 dark:text-white mb-4">
                    Страница удалена
                </h1>

                <div class="text-center mb-8">
                    <p class="text-lg text-gray-700 dark:text-gray-300 mb-4">
                        Этот адрес больше не используется (код ответа 410 Gone для поисковых систем).
                    </p>
                    <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center flex-wrap">
                        <a href="{{ route('home') }}"
                           class="inline-flex items-center justify-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors">
                            <i class="fas fa-home mr-2" aria-hidden="true"></i>
                            На главную
                        </a>
                        <a href="{{ route('dream-analyzer.create') }}"
                           class="inline-flex items-center justify-center px-6 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold rounded-lg transition-colors">
                            <i class="fas fa-magic mr-2" aria-hidden="true"></i>
                            Толкование снов
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
