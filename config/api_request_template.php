<?php

/**
 * Шаблон универсального запроса к DeepSeek API для анализа сновидений
 * 
 * Этот файл содержит базовую структуру запроса.
 * Динамически генерируемые части:
 * - analysis_config (зависит от выбранных традиций и типа анализа)
 * - user_context (данные пользователя)
 * - dream_data (описание сна и метаданные)
 * - context_summary (контекст пользователя)
 */

return [
    /**
     * Промпт для системы
     */
    'system_prompt' => 'Ты — универсальный аналитик сновидений. Проанализируй сон с учётом указанного контекста, используя мульти-традиционный подход. ВЕСЬ ОТВЕТ В ФОРМАТЕ JSON ГДЕ: 1) КЛЮЧИ (field names) — только на АНГЛИЙСКОМ в snake_case; 2) ЗНАЧЕНИЯ — только на РУССКОМ языке (кроме технических полей типа timestamp); 3) Не используй англицизмы, сокращения и аббревиатуры в значениях (вместо "reality check" → "проверка реальности", "lucidity" → "осознанность", "ПР" → "проверка реальности"). Пример правильного формата: {\"dream_title\": \"Моё сновидение\", \"emotional_tone\": \"тревожный\"}. НЕПРАВИЛЬНО: {\"название_сна\": \"Моё сновидение\", \"emotion\": \"тревожный\"}.  
    ',
    
    /**
     * Метаданные запроса
     */
    'request_metadata' => [
        'analysis_version' => '2.0',
        'request_type' => 'dream_analysis',
        'request_id' => '{request_uuid}', // Генерируется динамически
        'client_platform' => 'web', // web/mobile/app
        'analysis_depth' => 'глубокий', // поверхностный/средний/глубокий
    ],
    
    /**
     * Конфигурация анализа (ДИНАМИЧЕСКАЯ ЧАСТЬ)
     * 
     * Варианты:
     * 1. Single tradition (одна традиция)
     * 2. Synthetic comparative (несколько традиций - сравнение)
     * 3. Parallel insights (несколько традиций - параллельные инсайты)
     * 4. Integrated (несколько традиций - интегрированный подход)
     */
    'analysis_config' => [
        'primary_tradition' => '{tradition_name}', // Основная традиция
        
        'multitradition_config' => [
            'enabled' => false, // true для множественных традиций
            'mode' => 'single_tradition', // single_tradition | synthetic_comparative | parallel_insights | integrated
            
            'additional_traditions' => [], // Массив дополнительных традиций
            
            'synthesis_approach' => null, // Подход к синтезу (для множественных традиций)
        ],
        
        'output_format' => 'unified_schema_v1.1', // Формат вывода
        'response_language' => 'ru',
    ],
    
    /**
     * Профиль пользователя (опционально)
     */
    'user_context' => [
        'user_id' => '{userId}',
        'experience_level' => 'практик', // новичок/практик/эксперт
        'years_of_practice' => 0,
        'primary_goals' => [], // осознанность/терапия/исследование
        'current_practices' => [], // ведёт дневник/делает ПР/медитирует
        
        'tradition_background' => [
            'familiar_with' => [],
            'preferred_concepts' => [],
            'learning_goals' => [],
        ],
    ],
    

    
    /**
     * Контекст (краткое описание ситуации)
     */
    'context_summary' => '{user_context} | {dream_context} | {life_context} | {practice_context} | {analysis_goals}',
    
    /**
     * Данные сна
     */
    'dream_data' => [
        'raw_text' => '{dream_text}', // Описание сна
        'recall_clarity' => 0.9, // Ясность воспоминания (0-1)
        
        'sensory_details' => [
            'visual_vividness' => 0.8, // Яркость визуальных образов (0-1)
            'sound_presence' => true, // Наличие звуков
            'tactile_sensations' => true, // Тактильные ощущения
            'color_presence' => 'яркие', // яркие/приглушенные/чёрно-белые
            'unusual_perceptions' => [], // Необычные восприятия
        ],
        
        'narrative_annotations' => [
            'plot_coherence' => 0.9, // Связность сюжета (0-1)
            'character_consistency' => 0.8, // Постоянство персонажей (0-1)
            'temporal_logic' => 0.6, // Логика времени (0-1)
            'archetypal_density' => 0.7, // Плотность архетипов (0-1)
            'symbolic_layers_count' => 3, // Количество символических слоёв
        ],
    ],
    
    /**
     * Запрос унифицированной схемы ответа
     */
    'unified_schema_request' => [
        'analysis_mode' => 'single', // single | comparative | parallel
        'traditions_to_compare' => [], // Для comparative/parallel режимов
        'comparison_depth' => 'medium', // brief | medium | detailed
        
        'required_sections' => [
            'dream_metadata',
            'response_metadata',
            'core_analysis',
            'symbolic_elements',
            'practical_guidance',
            'recommendations',
            'context_summary',
        ],
        'optional_sections' => [
            'tradition_specific',
            'lucidity_analysis',
        ],
        
        'include_tags_and_categories' => true,
        'tag_types' => ['primary_tags', 'emotional_tags', 'theme_tags', 'skill_tags'],
        
        'sections_configuration' => [
            'dream_metadata' => [
                'required_fields' => [
                    'dream_title',
                    'dream_type',
                    'summary_insight',
                    'emotional_tone',
                    'raw_text',
                    'context_summary',
                ],
                'optional_fields' => [
                    'dream_detailed',
                    'dream_date',
                    'recall_quality',
                ],
                
                'field_guidelines' => [
                    'dream_title' => 'Метафоричное название на основе главного символа/механизма сна',
                    'dream_type' => 'Тип из: обычный, осознанный, кошмар, пограничный, исследовательский, повторяющийся, пророческий',
                    'summary_insight' => '1-2 фразы с ключевым инсайтом о механизме/функции сна',
                    'emotional_tone' => 'Одним словом - основной эмоциональный фон: нейтральный, тревожный, радостный, исследовательский и т.д.',
                    'dream_detailed' => '3-5 предложений нарративного анализа: начало-развитие-кульминация-значение',
                    'dream_date' => 'Дата в формате ГГГГ-ММ-ДД (если предоставлена)',
                    'recall_quality' => 'Оценка 0-1 точности воспроизведения (если предоставлена)',
                ],
                
                'dream_detailed_length' => 'long',
            ],
        ],
        
        'symbolic_elements_config' => [
            'include_objects' => true,
            'include_locations' => true,
            'include_characters' => true,
            'include_actions' => true,
            'categorize_by' => ['emotional_charge', 'symbolic_meaning'],
        ],
        
        'core_analysis_config' => [
            'include_emotional_breakdown' => true,
            'include_archetypal_patterns' => true,
            'include_life_context' => true,
        ],
        
        'practical_guidance_config' => [
            'include_os_scenarios' => true,
            'include_therapeutic_approaches' => true,
            'include_immediate_actions' => true,
        ],
        
        'response_structure' => 'v1.1',
        'language' => 'ru',
        'detail_level' => 'detailed',
    ],
    
    /**
     * Запрос на анализ (опционально)
     */
    'analysis_request' => [
        'specific_questions' => [], // Конкретные вопросы пользователя
        'focus_areas' => [], // Области фокуса
        'avoidance_areas' => [], // Области, которых следует избегать
        'preferred_insight_format' => [], // Предпочитаемый формат инсайтов
    ],
    
    /**
     * Предпочтения интеграции (опционально)
     */
    'integration_preferences' => [
        'learning_style' => [], // практический/теоретический/интуитивный/системный
        'action_tendency' => [], // экспериментатор/созерцатель/исследователь/интегратор
        'time_commitment_available' => '30_минут_в_день',
        'risk_tolerance' => 'умеренный', // низкий/умеренный/высокий
    ],
    
    /**
     * Обратная связь (опционально)
     */
    'feedback_loop' => [
        'previous_analysis_applications' => [],
        'preferred_analyst_style' => [], // поддерживающий/прямой/практичный
        'effectiveness_criteria' => [], // критерии эффективности
    ],
];

