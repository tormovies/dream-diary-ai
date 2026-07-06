<p {{ $attributes->merge(['class' => 'text-sm text-gray-500 dark:text-gray-400']) }}>
    <i class="fas fa-robot mr-1" aria-hidden="true"></i>
    Текст отправляется на обработку в сервис искусственного интеллекта для формирования анализа.
    Передача может осуществляться за пределы Российской Федерации.
    <a href="{{ route('legal.personal-data') }}" class="text-purple-600 underline hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300">Подробнее в политике ПДн</a>.
</p>
