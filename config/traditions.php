<?php

/**
 * Полная конфигурация традиций анализа сновидений
 * 
 * Объединяет базовую информацию и детальные уточнения для формирования
 * запросов к DeepSeek API.
 */

return [
    'freudian' => [
        'enabled' => true,
        'key' => 'freudian',
        'name_short' => 'Фрейдистская',
        'name_full' => 'Фрейдистский анализ',
        'deepseek_description' => 'фрейдистской',
        'icon' => 'fa-couch',
        'emoji_icon' => '🛌',
        'difficulty_level' => 'advanced',
        
        'category' => 'психоаналитическая',
        'tradition_specific_clarification' => [
            'definition_source' => 'классический_психоанализ_Фрейда',
            'key_concepts' => [
                'Эго_Ид_Суперэго',
                'вытеснение',
                'либидо',
                'Эдипов_комплекс',
                'символизм_сексуальный',
            ],
            'core_terminology_ru' => [
                'цензура_сна',
                'явное_скрытое_содержание',
                'детские_травмы',
                'сублимация',
            ],
            'interpretation_style' => 'редуктивный_символический',
            'avoid_modern_mixes' => [
                'неофрейдизм',
                'лакановский_анализ',
                'поп_психология',
            ],
        ],
        
        'available_aspects' => [
            'скрытое_содержание',
            'детские_травмы',
            'сексуальный_символизм',
            'вытесненные_желания',
            'цензура_сна',
            'эдиповы_мотивы',
            'сублимация',
        ],
        
        'default_analysis_parameters' => [
            'depth' => 'deep',
            'include_childhood_associations' => true,
            'include_sexual_symbolism' => true,
            'include_dream_work' => true,
            'focus_on_latent_content' => true,
            'approach' => 'reductive_interpretive',
        ],
    ],

    'jungian' => [
        'enabled' => true,
        'key' => 'jungian',
        'name_short' => 'Юнгианская',
        'name_full' => 'Юнгианский анализ',
        'deepseek_description' => 'юнгианской',
        'icon' => 'fa-yin-yang',
        'emoji_icon' => '🧠',
        'difficulty_level' => 'intermediate',
        
        'category' => 'аналитическая_психология',
        'tradition_specific_clarification' => [
            'definition_source' => 'аналитическая_психология_Юнга',
            'key_concepts' => [
                'архетипы',
                'коллективное_бессознательное',
                'индивидуация',
                'Тень',
                'Анима_Анимус',
                'Самость',
            ],
            'core_terminology_ru' => [
                'синхрония',
                'персона',
                'великая_мать',
                'старый_мудрец',
            ],
            'interpretation_style' => 'амплификация_символов',
            'avoid_simplification' => [
                'только_типизация',
                'поп_архетипы',
            ],
        ],
        
        'available_aspects' => [
            'архетипы',
            'тень',
            'индивидуация',
            'анима_анимус',
            'символика_мандалы',
            'коллективное_бессознательное',
            'синхрония',
            'путешествие_героя',
        ],
        
        'default_analysis_parameters' => [
            'depth' => 'deep',
            'include_amplification' => true,
            'include_active_imagination' => true,
            'archetype_analysis_level' => 'detailed',
            'symbol_interpretation_method' => 'contextual_amplification',
            'include_individuation_process' => true,
        ],
    ],

    'cognitive' => [
        'enabled' => true,
        'key' => 'cognitive',
        'name_short' => 'Когнитивная',
        'name_full' => 'Когнитивная психология сна',
        'deepseek_description' => 'когнитивной',
        'icon' => 'fa-brain',
        'emoji_icon' => '🤔',
        'difficulty_level' => 'beginner',
        
        'category' => 'когнитивно_поведенческая',
        'tradition_specific_clarification' => [
            'definition_source' => 'когнитивно_поведенческая_терапия_сновидений',
            'key_concepts' => [
                'схемы_мышления',
                'эмоциональная_обработка',
                'когнитивные_искажения',
                'паттерны',
            ],
            'core_terminology_ru' => [
                'мыслеформы',
                'эмоциональные_метки',
                'адаптация',
                'реструктуризация',
            ],
            'approach' => 'практический_решение_проблем',
            'avoid_psychodynamic' => [
                'глубинный_анализ',
                'детские_травмы',
            ],
        ],
        
        'available_aspects' => [
            'эмоциональная_обработка',
            'паттерны_мышления',
            'дневные_остатки',
            'когнитивные_искажения',
            'схемы_поведения',
            'проблемное_мышление',
            'адаптационные_механизмы',
        ],
        
        'default_analysis_parameters' => [
            'depth' => 'medium',
            'include_cognitive_restructuring' => true,
            'include_emotion_processing' => true,
            'focus_on_daily_residues' => true,
            'include_practical_exercises' => true,
            'problem_solving_approach' => true,
        ],
    ],

    'symbolic' => [
        'enabled' => true,
        'key' => 'symbolic',
        'name_short' => 'Символическая',
        'name_full' => 'Символическая трактовка',
        'deepseek_description' => 'символической',
        'icon' => 'fa-key',
        'emoji_icon' => '🔮',
        'difficulty_level' => 'intermediate',
        
        'category' => 'универсальный_символизм',
        'tradition_specific_clarification' => [
            'definition_source' => 'универсальный_символизм',
            'key_concepts' => [
                'универсальные_символы',
                'культурные_архетипы',
                'мифологические_мотивы',
            ],
            'sources' => [
                'Элиаде',
                'Кэмпбелл',
                'мировая_мифология',
            ],
            'core_terminology_ru' => [
                'символические_ряды',
                'мифологемы',
                'посвящение',
                'путешествие',
            ],
            'interpretation_method' => 'контекстуальный_многоуровневый',
            'avoid_literalism' => [
                'буквальные_интерпретации',
                'однозначные_значения',
            ],
        ],
        
        'available_aspects' => [
            'универсальные_символы',
            'мифологические_мотивы',
            'архетипические_сюжеты',
            'культурные_коды',
            'символические_ряды',
            'инициация',
            'трансформация',
        ],
        
        'default_analysis_parameters' => [
            'depth' => 'deep',
            'include_mythological_parallels' => true,
            'include_cultural_context' => true,
            'symbol_interpretation_method' => 'multilayered',
            'include_universal_patterns' => true,
            'cross_cultural_analysis' => true,
        ],
    ],

    'shamanic' => [
        'enabled' => true,
        'key' => 'shamanic',
        'name_short' => 'Шаманская',
        'name_full' => 'Шаманистическая трактовка',
        'deepseek_description' => 'шаманистической',
        'icon' => 'fa-feather',
        'emoji_icon' => '🥁',
        'difficulty_level' => 'advanced',
        
        'category' => 'традиционные_практики',
        'tradition_specific_clarification' => [
            'definition_source' => 'традиционный_шаманизм',
            'key_concepts' => [
                'путешествие_в_миры',
                'духи_помощники',
                'животные_силы',
                'целительство',
            ],
            'cultural_context' => [
                'сибирский_шаманизм',
                'амазонские_традиции',
                'кельтские_практики',
            ],
            'core_terminology_ru' => [
                'нижний_средний_верхний_миры',
                'бубен',
                'камлание',
                'навь',
            ],
            'avoid_new_age' => [
                'неошаманизм',
                'коммерческие_курсы',
            ],
        ],
        
        'available_aspects' => [
            'духовные_гиды',
            'животные_силы',
            'путешествие_в_миры',
            'целительские_послания',
            'энергетические_блоки',
            'духи_помощники',
            'шаманское_видение',
        ],
        
        'default_analysis_parameters' => [
            'depth' => 'deep',
            'include_spirit_guides' => true,
            'include_power_animals' => true,
            'include_world_travel_mapping' => true,
            'include_healing_messages' => true,
            'energy_work_focus' => true,
        ],
    ],

    'gestalt' => [
        'enabled' => true,
        'key' => 'gestalt',
        'name_short' => 'Гештальт',
        'name_full' => 'Гештальт-подход',
        'deepseek_description' => 'гештальт',
        'icon' => 'fa-puzzle-piece',
        'emoji_icon' => '🌀',
        'difficulty_level' => 'intermediate',
        
        'category' => 'терапевтическая',
        'tradition_specific_clarification' => [
            'definition_source' => 'гештальт_терапия_Перлза',
            'key_concepts' => [
                'здесь_и_сейчас',
                'проекции',
                'внутренний_диалог',
                'незавершенные_ситуации',
            ],
            'techniques' => [
                'диалог_с_персонажами',
                'отождествление',
                'преувеличение',
            ],
            'core_terminology_ru' => [
                'фигура_фон',
                'контакт_граница',
                'осознавание',
                'ответственность',
            ],
            'approach' => 'экспериментальный_процессный',
            'avoid_interpretation' => [
                'анализ_прошлого',
                'символические_толкования',
            ],
        ],
        
        'available_aspects' => [
            'проекции',
            'внутренний_диалог',
            'незавершенные_ситуации',
            'фигура_фон',
            'контактные_границы',
            'осознавание',
            'ответственность',
        ],
        
        'default_analysis_parameters' => [
            'depth' => 'medium',
            'include_dialogue_experiments' => true,
            'include_projection_work' => true,
            'focus_on_present_moment' => true,
            'include_unfinished_business' => true,
            'experiential_approach' => true,
        ],
    ],

    'lucid_centered' => [
        'enabled' => false,
        'key' => 'lucid_centered',
        'name_short' => 'Практика ОС',
        'name_full' => 'Анализ осознанности',
        'deepseek_description' => 'практики осознанных сновидений',
        'icon' => 'fa-eye',
        'emoji_icon' => '👁️',
        'difficulty_level' => 'beginner',
        
        'category' => 'осознанные_сновидения',
        'tradition_specific_clarification' => [
            'definition_source' => 'lucid_centered_approach',
            'key_concepts' => [
                'осознание_во_сне',
                'мета_когниция',
                'наблюдающее_эго',
                'присутствие',
            ],
            'core_terminology_ru' => [
                'осознанное_сновидение',
                'мета_осознание',
                'свидетель',
                'присутствие',
            ],
            'techniques' => [
                'медитация_осознанности',
                'вопрос_кто_спит',
                'удержание_внимания',
            ],
            'avoid_interpretive' => [
                'символические_толкования',
                'проекции',
            ],
        ],
        
        'available_aspects' => [
            'моменты_люцидности',
            'мета_когниция',
            'свидетельствование',
            'удержание_внимания',
            'осознанное_присутствие',
            'переходы_сознания',
            'мета_осознание',
        ],
        
        'default_analysis_parameters' => [
            'depth' => 'deep',
            'include_mindfulness_analysis' => true,
            'include_awareness_moments' => true,
            'include_meta_cognition_tracking' => true,
            'focus_on_present_moment' => true,
            'non_interpretive_approach' => true,
        ],
    ],

    'dream_hackers' => [
        'enabled' => true,
        'key' => 'dream_hackers',
        'name_short' => 'Хакеры снов',
        'name_full' => 'Хакеры сновидений',
        'deepseek_description' => 'хакер сновидений из русскоязычной субкультуры (форумы, Реутов, практики ОС, глюки матрицы, точки входа, стабилизация фазы/сновидения, локации), не просто интерпретируешь сны — ты даёшь инструменты для практики ОС, не используешь термины хакеров программистов.',
        'icon' => 'fa-infinity',
        'emoji_icon' => '👁️‍🗨️',
        'difficulty_level' => 'intermediate',
        
        'category' => 'практики_ОС',
        'tradition_specific_clarification' => [
            'definition_source' => 'субкультура_практиков_ОС',
            'key_sources' => [
                'Реутов_Фаза',
                'форум_ОС',
                'сообщество_astraldynamics',
            ],
            'core_terminology' => [
                'глюки_матрицы',
                'стабилизация_фазы',
                'ПР',
                'точка_входа',
                'ФВО',
                'люцидность',
            ],
            'style' => 'практичный_технический_сленг',
            'avoid_IT_terms' => [
                'код',
                'дебаггинг',
                'хакерство',
                'программирование',
            ],
        ],
        
        'available_aspects' => [
            'глюки_матрицы',
            'стабилизация_фазы',
            'точки_входа_выхода',
            'эффективность_ПР',
            'люцидные_моменты',
            'энергетические_состояния',
            'техники_стабилизации',
            'фаза_внетелесных_ощущений',
        ],
        
        'default_analysis_parameters' => [
            'depth' => 'practical',
            'include_glitch_analysis' => true,
            'include_stabilization_techniques' => true,
            'include_reality_check_analysis' => true,
            'include_step_by_step_guides' => true,
            'focus_on_practical_application' => true,
            'calculate_lucidity_index' => true,
        ],
    ],

    'complex_analysis' => [
        'enabled' => true,
        'key' => 'complex_analysis',
        'name_short' => 'Комплексная',
        'name_full' => 'Комплексный анализ',
        'deepseek_description' => 'комплексной',
        'icon' => 'fa-layer-group',
        'emoji_icon' => '🧩',
        'difficulty_level' => 'advanced',
        
        'category' => 'интегративный',
        'tradition_specific_clarification' => [
            'definition_source' => 'интегративный_подход',
            'key_concepts' => [
                'многоуровневость',
                'интеграция_методов',
                'системность',
                'контекстуальность',
            ],
            'methodology' => [
                'комбинирование_традиций',
                'адаптация_к_клиенту',
                'гибкость',
            ],
            'core_terminology_ru' => [
                'мета_анализ',
                'интеграция',
                'контекст',
                'многоуровневость',
            ],
            'approach' => 'холистический_интегративный',
            'avoid_eclecticism' => [
                'бессистемное_смешение',
                'противоречивые_методы',
            ],
        ],
        
        'available_aspects' => [
            'мультиперспективность',
            'системный_анализ',
            'интегративный_подход',
            'контекстуальность',
            'многоуровневость',
            'синтез_методов',
            'холистическое_видение',
        ],
        
        'default_analysis_parameters' => [
            'depth' => 'deep',
            'include_multiple_perspectives' => true,
            'include_systemic_analysis' => true,
            'integrate_contradictions' => true,
            'holistic_approach' => true,
            'context_sensitive' => true,
        ],
    ],
];
