<?php

/**
 * Примеры конфигурации analysis_config для разных типов анализа
 * 
 * Эти примеры показывают, как должен формироваться блок analysis_config
 * в зависимости от выбранных традиций и типа обработки.
 */

return [
    /**
     * 1. SINGLE (одна традиция)
     * 
     * Используется когда пользователь выбирает только одну традицию.
     * Самый простой вариант анализа.
     */
    'single' => [
        'primary_tradition' => 'freudian', // Выбранная традиция
        
        'multitradition_config' => [
            'enabled' => false,
            'mode' => 'single_tradition',
            'additional_traditions' => [],
            'synthesis_approach' => null,
        ],
        
        'output_format' => 'unified_json_v2_single_tradition',
        'response_language' => 'ru',
    ],

    /**
     * 2. SYNTHETIC COMPARATIVE (сравнительный анализ)
     * 
     * Каждая традиция анализирует сон отдельно, затем результаты сравниваются.
     * Подчеркиваются различия и сходства между традициями.
     * 
     * Пример: Как фрейдистская, юнгианская и когнитивная традиции
     * по-разному интерпретируют один и тот же символ.
     */
    'synthetic_comparative' => [
        'primary_tradition' => 'freudian', // Основная традиция (первая в списке)
        
        'multitradition_config' => [
            'enabled' => true,
            'mode' => 'synthetic_comparative',
            
            'additional_traditions' => [
                [
                    'name' => 'jungian',
                    'weight' => 0.33, // Равный вес для всех традиций
                    'requested_aspects' => ['архетипы', 'тень', 'индивидуация'],
                ],
                [
                    'name' => 'cognitive',
                    'weight' => 0.33,
                    'requested_aspects' => ['эмоциональная_обработка', 'паттерны_мышления'],
                ],
            ],
            
            'synthesis_approach' => [
                'find_common_insights' => true, // Искать общие темы
                'highlight_contrasts' => true, // Подчеркивать различия
                'integrate_conflicting_views' => false, // НЕ примирять противоречия
                'create_unified_perspective' => false, // НЕ создавать единый вывод
            ],
        ],
        
        'output_format' => 'unified_json_v2_multitradition_comparative',
        'response_language' => 'ru',
    ],

    /**
     * 3. PARALLEL INSIGHTS (параллельные инсайты)
     * 
     * Каждая традиция даёт свой независимый анализ.
     * Результаты представлены параллельно, без сравнения или синтеза.
     * 
     * Пример: Три отдельных толкования сна — фрейдистское, юнгианское, 
     * когнитивное — каждое в своём блоке.
     */
    'parallel_insights' => [
        'primary_tradition' => 'freudian', // Основная традиция (первая)
        
        'multitradition_config' => [
            'enabled' => true,
            'mode' => 'parallel_insights',
            
            'additional_traditions' => [
                [
                    'name' => 'jungian',
                    'weight' => 0.5, // Равный вес
                    'requested_aspects' => ['архетипы', 'коллективное_бессознательное'],
                ],
                [
                    'name' => 'gestalt',
                    'weight' => 0.5,
                    'requested_aspects' => ['здесь_и_сейчас', 'проекции', 'внутренний_диалог'],
                ],
            ],
            
            'synthesis_approach' => [
                'find_common_insights' => false, // Не искать общие темы
                'highlight_contrasts' => false, // Не сравнивать
                'integrate_conflicting_views' => false, // Не интегрировать
                'create_unified_perspective' => false, // Не создавать синтез
            ],
        ],
        
        'output_format' => 'unified_json_v2_multitradition_parallel',
        'response_language' => 'ru',
    ],

    /**
     * 4. INTEGRATED (интегрированный подход)
     * 
     * Все традиции работают вместе, создавая единый комплексный анализ.
     * Противоречия примиряются, создаётся целостная перспектива.
     * 
     * Пример: Анализ использует символизм Фрейда, архетипы Юнга
     * и практические техники когнитивной терапии в едином ключе.
     */
    'integrated' => [
        'primary_tradition' => 'freudian', // Основная традиция
        
        'multitradition_config' => [
            'enabled' => true,
            'mode' => 'integrated',
            
            'additional_traditions' => [
                [
                    'name' => 'jungian',
                    'weight' => 0.4, // Можно задавать разные веса
                    'requested_aspects' => ['архетипы', 'индивидуация'],
                ],
                [
                    'name' => 'cognitive',
                    'weight' => 0.3,
                    'requested_aspects' => ['паттерны_мышления', 'практические_решения'],
                ],
                [
                    'name' => 'gestalt',
                    'weight' => 0.3,
                    'requested_aspects' => ['внутренний_диалог', 'осознавание'],
                ],
            ],
            
            'synthesis_approach' => [
                'find_common_insights' => true, // Искать общие темы
                'highlight_contrasts' => true, // Показать различия
                'integrate_conflicting_views' => true, // Примирить противоречия
                'create_unified_perspective' => true, // Создать единый синтез
            ],
        ],
        
        'output_format' => 'unified_json_v2_multitradition_integrated',
        'response_language' => 'ru',
    ],

    /**
     * 5. SERIES INTEGRATED (серия снов)
     * 
     * Анализ нескольких снов сразу с выявлением связей между ними.
     * Используется одна или несколько традиций для анализа серии.
     */
    'series_integrated' => [
        'primary_tradition' => 'jungian', // Основная традиция
        
        'multitradition_config' => [
            'enabled' => false, // Можно включить для мульти-традиционного анализа серии
            'mode' => 'series_integrated',
            'additional_traditions' => [],
            'synthesis_approach' => null,
        ],
        
        'output_format' => 'unified_json_v2_series',
        'response_language' => 'ru',
    ],

    /**
     * 6. SERIES INTEGRATED WITH MULTIPLE TRADITIONS
     * 
     * Серия снов, анализируемая с использованием нескольких традиций.
     */
    'series_integrated_multi' => [
        'primary_tradition' => 'jungian',
        
        'multitradition_config' => [
            'enabled' => true,
            'mode' => 'series_integrated',
            
            'additional_traditions' => [
                [
                    'name' => 'freudian',
                    'weight' => 0.5,
                    'requested_aspects' => ['символизм', 'вытеснение'],
                ],
            ],
            
            'synthesis_approach' => [
                'find_common_insights' => true,
                'highlight_contrasts' => false,
                'integrate_conflicting_views' => true,
                'create_unified_perspective' => true,
            ],
        ],
        
        'output_format' => 'unified_json_v2_series_multitradition',
        'response_language' => 'ru',
    ],
];






