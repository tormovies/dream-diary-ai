<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для формирования унифицированных запросов к DeepSeek API
 * 
 * Использует шаблон из config/api_request_template.php
 * и данные традиций из config/traditions.php
 */
class DreamAnalysisRequestBuilder
{
    private array $template;
    private array $traditions;

    public function __construct()
    {
        $this->template = config('api_request_template', []);
        $this->traditions = config('traditions', []);
    }

    /**
     * Построить запрос для single tradition (одна традиция)
     */
    public function buildSingleRequest(array $params): array
    {
        $traditionKey = $params['tradition'] ?? 'complex_analysis';
        $dreamText = $params['dream_text'];
        $context = $params['context'] ?? null;
        $userId = $params['user_id'] ?? null;
        $userProfile = $params['user_profile'] ?? [];
        $analysisRequest = $params['analysis_request'] ?? [];

        // Получаем данные традиции
        $tradition = $this->traditions[$traditionKey] ?? null;
        if (!$tradition) {
            throw new \InvalidArgumentException("Традиция '{$traditionKey}' не найдена");
        }

        // Формируем блок tradition для analysis_config
        $traditionBlock = [
            'name' => $tradition['key'],
            'display_name' => $tradition['name_full'],
            'tradition_specific_clarification' => $tradition['tradition_specific_clarification'] ?? [],
            'analysis_parameters' => $tradition['default_analysis_parameters'] ?? [],
            'requested_aspects' => $tradition['available_aspects'] ?? [],
        ];

        // Формируем analysis_config
        $analysisConfig = [
            'mode' => 'single_tradition',
            'tradition' => $traditionBlock,
            'output_format' => 'unified_schema_v1.1',
            'response_language' => 'ru',
        ];

        // Формируем unified_schema_request
        $unifiedSchemaRequest = $this->template['unified_schema_request'] ?? [];
        $unifiedSchemaRequest['analysis_mode'] = 'single';
        $unifiedSchemaRequest['traditions_to_compare'] = [];

        // Получаем user_context_rules из традиции
        $userContextRules = $tradition['user_context_rules'] ?? [];
        
        // Формируем user_context
        // Используем значения из традиции, если они не переданы в userProfile
        $userProfileData = array_merge(
            $this->template['user_context'] ?? [],
            [
                'user_id' => $userId,
                'experience_level' => $userProfile['experience_level'] ?? $userContextRules['recommended_level'] ?? 'практик',
                'primary_goals' => $userProfile['primary_goals'] ?? $userContextRules['compatible_goals'] ?? [],
                'current_practices' => $userProfile['current_practices'] ?? $userContextRules['useful_practices'] ?? [],
            ],
            $userProfile // Переданные значения имеют приоритет
        );

        // Формируем dream_data
        $dreamData = array_merge(
            $this->template['dream_data'] ?? [],
            [
                'raw_text' => $dreamText,
                'recall_clarity' => $params['recall_clarity'] ?? 0.9,
            ]
        );

        // Формируем context_summary
        $contextSummary = $context ?? ($this->template['context_summary'] ?? '');

        // Собираем весь запрос
        $requestContent = [
            'request_metadata' => [
                'analysis_version' => '2.0',
                'request_type' => 'dream_analysis',
                'request_id' => Str::uuid()->toString(),
                'client_platform' => 'web',
                'analysis_depth' => 'глубокий',
            ],
            'analysis_config' => $analysisConfig,
            'user_context' => $userProfileData,
            'context_summary' => $contextSummary,
            'dream_data' => $dreamData,
            'unified_schema_request' => $unifiedSchemaRequest,
        ];

        // Добавляем analysis_request если есть
        if (!empty($analysisRequest)) {
            $requestContent['analysis_request'] = $analysisRequest;
        }

        // Формируем финальный запрос к API
        $apiRequest = [
            'model' => 'deepseek-chat',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->template['system_prompt'] ?? '',
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($requestContent, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                ],
            ],
            'temperature' => 0.7,
            'max_tokens' => 8000,
            'stream' => false,
        ];

        return $apiRequest;
    }

    /**
     * Построить запрос для multitradition (comparative/parallel/integrated)
     */
    public function buildMultiTraditionRequest(array $params): array
    {
        $primaryTradition = $params['primary_tradition'];
        $secondaryTraditions = $params['secondary_traditions'] ?? [];
        $mode = $params['mode'] ?? 'comparative'; // comparative | parallel | integrated
        $dreamText = $params['dream_text'];
        $context = $params['context'] ?? null;
        $userId = $params['user_id'] ?? null;
        $userProfile = $params['user_profile'] ?? [];
        $analysisRequest = $params['analysis_request'] ?? [];

        // Получаем данные всех традиций
        $allTraditionKeys = array_merge([$primaryTradition], $secondaryTraditions);
        $traditionBlocks = [];
        $primaryTraditionData = null; // Сохраняем данные primary традиции для user_context_rules

        foreach ($allTraditionKeys as $traditionKey) {
            $tradition = $this->traditions[$traditionKey] ?? null;
            if (!$tradition) {
                throw new \InvalidArgumentException("Традиция '{$traditionKey}' не найдена");
            }

            // Сохраняем данные primary традиции
            if ($traditionKey === $primaryTradition) {
                $primaryTraditionData = $tradition;
            }

            $traditionBlocks[] = [
                'name' => $tradition['key'],
                'display_name' => $tradition['name_full'],
                'tradition_specific_clarification' => $tradition['tradition_specific_clarification'] ?? [],
                'analysis_parameters' => $tradition['default_analysis_parameters'] ?? [],
                'requested_aspects' => $tradition['available_aspects'] ?? [],
            ];
        }

        // Первая традиция - primary, остальные - secondary
        $primaryBlock = $traditionBlocks[0];
        $secondaryBlocks = array_slice($traditionBlocks, 1);

        // Формируем analysis_config для multitradition
        $analysisConfig = [
            'mode' => 'multitradition',
            'multitradition' => [
                'primary' => $primaryBlock,
                'secondary' => $secondaryBlocks,
                'synthesis_approach' => $this->buildSynthesisApproach($mode),
            ],
            'output_format' => 'unified_schema_v1.1',
            'response_language' => 'ru',
        ];

        // Формируем unified_schema_request
        $unifiedSchemaRequest = $this->template['unified_schema_request'] ?? [];
        $unifiedSchemaRequest['analysis_mode'] = $mode;
        $unifiedSchemaRequest['traditions_to_compare'] = $allTraditionKeys;
        $unifiedSchemaRequest['comparison_depth'] = $params['comparison_depth'] ?? 'medium';

        // Получаем user_context_rules из primary традиции
        $userContextRules = $primaryTraditionData['user_context_rules'] ?? [];
        
        // Формируем user_context
        // Используем значения из primary традиции, если они не переданы в userProfile
        $userProfileData = array_merge(
            $this->template['user_context'] ?? [],
            [
                'user_id' => $userId,
                'experience_level' => $userProfile['experience_level'] ?? $userContextRules['recommended_level'] ?? 'практик',
                'primary_goals' => $userProfile['primary_goals'] ?? $userContextRules['compatible_goals'] ?? [],
                'current_practices' => $userProfile['current_practices'] ?? $userContextRules['useful_practices'] ?? [],
            ],
            $userProfile // Переданные значения имеют приоритет
        );

        // Формируем dream_data
        $dreamData = array_merge(
            $this->template['dream_data'] ?? [],
            [
                'raw_text' => $dreamText,
                'recall_clarity' => $params['recall_clarity'] ?? 0.9,
            ]
        );

        // Формируем context_summary
        $contextSummary = $context ?? ($this->template['context_summary'] ?? '');

        // Собираем весь запрос
        $requestContent = [
            'request_metadata' => [
                'analysis_version' => '2.0',
                'request_type' => 'dream_analysis',
                'request_id' => Str::uuid()->toString(),
                'client_platform' => 'web',
                'analysis_depth' => 'глубокий',
            ],
            'analysis_config' => $analysisConfig,
            'user_context' => $userProfileData,
            'context_summary' => $contextSummary,
            'dream_data' => $dreamData,
            'unified_schema_request' => $unifiedSchemaRequest,
        ];

        // Добавляем analysis_request если есть
        if (!empty($analysisRequest)) {
            $requestContent['analysis_request'] = $analysisRequest;
        }

        // Формируем финальный запрос к API
        $apiRequest = [
            'model' => 'deepseek-chat',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->template['system_prompt'] ?? '',
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($requestContent, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                ],
            ],
            'temperature' => 0.7,
            'max_tokens' => 8000,
            'stream' => false,
        ];

        return $apiRequest;
    }

    /**
     * Построить synthesis_approach для multitradition
     */
    private function buildSynthesisApproach(string $mode): array
    {
        switch ($mode) {
            case 'comparative':
                return [
                    'mode' => 'synthetic_comparative',
                    'style' => 'balanced',
                    'comparison_settings' => [
                        'find_common_insights' => true,
                        'highlight_contrasts' => true,
                        'highlight_synergies' => true,
                        'identify_conflicts' => true,
                        'depth_of_comparison' => 'detailed',
                    ],
                    'integration_settings' => [
                        'integrate_conflicting_views' => false,
                        'create_unified_perspective' => false,
                        'resolution_strategy' => 'keep_differences',
                        'allow_conceptual_hybrids' => false,
                        'create_new_terminology' => false,
                    ],
                ];

            case 'parallel':
                return [
                    'mode' => 'parallel_insights',
                    'style' => 'balanced',
                    'comparison_settings' => [
                        'find_common_insights' => false,
                        'highlight_contrasts' => false,
                        'highlight_synergies' => false,
                        'identify_conflicts' => false,
                        'depth_of_comparison' => 'light',
                    ],
                    'integration_settings' => [
                        'integrate_conflicting_views' => false,
                        'create_unified_perspective' => false,
                        'resolution_strategy' => 'keep_differences',
                        'allow_conceptual_hybrids' => false,
                        'create_new_terminology' => false,
                    ],
                ];

            case 'integrated':
                return [
                    'mode' => 'integrated',
                    'style' => 'balanced',
                    'comparison_settings' => [
                        'find_common_insights' => true,
                        'highlight_contrasts' => true,
                        'highlight_synergies' => true,
                        'identify_conflicts' => true,
                        'depth_of_comparison' => 'detailed',
                    ],
                    'integration_settings' => [
                        'integrate_conflicting_views' => true,
                        'create_unified_perspective' => true,
                        'resolution_strategy' => 'transpersonal_integration',
                        'allow_conceptual_hybrids' => true,
                        'create_new_terminology' => false,
                    ],
                ];

            default:
                throw new \InvalidArgumentException("Неизвестный режим: {$mode}");
        }
    }
}






