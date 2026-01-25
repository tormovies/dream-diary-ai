<?php

namespace App\Helpers;

use App\Models\DreamInterpretation;
use App\Helpers\SeoHelper;
use Illuminate\Support\Str;

class InterpretationLinkHelper
{
    /**
     * Получить похожие толкования для перелинковки
     * 
     * @param DreamInterpretation $interpretation Текущее толкование
     * @param int $limit Количество похожих толкований (по умолчанию 5)
     * @return \Illuminate\Support\Collection
     */
    public static function getSimilarInterpretations(DreamInterpretation $interpretation, ?int $limit = null)
    {
        // Получаем лимит из настроек, если не передан
        if ($limit === null) {
            $limit = (int)\App\Models\Setting::getValue('sitemap.linking_links_count', 5);
        }
        
        // Получаем SEO данные текущего толкования
        $currentSeo = SeoHelper::forDreamAnalyzerResult($interpretation);
        
        // Извлекаем ключевые слова из title, description, h1
        $keywords = self::extractKeywords([
            $currentSeo['title'] ?? '',
            $currentSeo['description'] ?? '',
            $currentSeo['h1'] ?? '',
            $currentSeo['h1_description'] ?? '',
        ]);
        
        if (empty($keywords)) {
            // Если нет ключевых слов, возвращаем последние готовые толкования
            return self::getLatestInterpretations($interpretation->id, $limit);
        }
        
        // Ищем похожие толкования по ключевым словам
        $similar = self::findByKeywords($keywords, $interpretation->id, $limit);
        
        // Если нашли меньше нужного количества, дополняем последними
        if ($similar->count() < $limit) {
            $latest = self::getLatestInterpretations($interpretation->id, $limit - $similar->count());
            $similar = $similar->merge($latest)->unique('id');
        }
        
        // Исключаем дубликаты по meta title (полностью одинаковые заголовки)
        $similar = self::removeDuplicateTitles($similar, $currentSeo['title'] ?? '');
        
        return $similar->take($limit);
    }
    
    /**
     * Получить последние готовые толкования
     * 
     * @param int $excludeId ID толкования для исключения (0 = не исключать)
     * @param int $limit Количество
     * @return \Illuminate\Support\Collection
     */
    public static function getLatestInterpretations(int $excludeId = 0, ?int $limit = null)
    {
        // Получаем лимит из настроек, если не передан
        if ($limit === null) {
            $limit = (int)\App\Models\Setting::getValue('sitemap.linking_links_count', 5);
        }
        
        $minDate = \Carbon\Carbon::create(2026, 1, 16, 0, 0, 0);
        
        $query = DreamInterpretation::where('processing_status', 'completed')
            ->whereNull('api_error')
            ->whereHas('result')
            ->where('created_at', '>=', $minDate);
        
        if ($excludeId > 0) {
            $query->where('id', '!=', $excludeId);
        }
        
        $interpretations = $query
            ->with('result')
            ->orderBy('created_at', 'desc')
            ->limit($limit * 3) // Берем больше, чтобы отфильтровать по SEO и дубликатам
            ->get()
            ->filter(function($interpretation) {
                // Проверяем, что у толкования есть валидные SEO данные
                try {
                    $seo = SeoHelper::forDreamAnalyzerResult($interpretation);
                    $title = $seo['title'] ?? '';
                    
                    // Проверяем, что title не пустой и не дефолтный
                    if (empty($title)) {
                        return false;
                    }
                    
                    $defaultTitlePattern = 'Толкование сна - Анализ сна';
                    if (str_contains($title, $defaultTitlePattern)) {
                        return false;
                    }
                    
                    // Проверяем длину title (должен быть разумным)
                    if (mb_strlen($title) < 20 || mb_strlen($title) > 200) {
                        return false;
                    }
                    
                    return true;
                } catch (\Exception $e) {
                    return false;
                }
            });
        
        // Исключаем дубликаты по meta title (полностью одинаковые заголовки)
        $interpretations = self::removeDuplicateTitles($interpretations);
        
        return $interpretations->take($limit);
    }
    
    /**
     * Извлечь ключевые слова из текста
     * 
     * @param array $texts Массив текстов
     * @return array Массив ключевых слов
     */
    private static function extractKeywords(array $texts): array
    {
        $stopWords = [
            'толкование', 'сна', 'снов', 'сон', 'анализ', 'расшифровка',
            'для', 'что', 'как', 'где', 'когда', 'который', 'которая', 'которое',
            'это', 'этот', 'эта', 'это', 'быть', 'был', 'была', 'было',
            'и', 'или', 'но', 'а', 'в', 'на', 'с', 'по', 'от', 'до', 'из',
            'о', 'об', 'со', 'к', 'у', 'за', 'над', 'под', 'при', 'про',
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
        ];
        
        $allText = implode(' ', $texts);
        
        // Удаляем HTML теги и специальные символы
        $allText = strip_tags($allText);
        $allText = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $allText);
        
        // Разбиваем на слова
        $words = preg_split('/\s+/', mb_strtolower($allText));
        
        // Фильтруем: убираем стоп-слова, короткие слова (менее 3 символов), числа
        $keywords = array_filter($words, function($word) use ($stopWords) {
            $word = trim($word);
            return mb_strlen($word) >= 3 
                && !in_array($word, $stopWords)
                && !is_numeric($word)
                && !empty($word);
        });
        
        // Подсчитываем частоту и берем топ-10 самых частых
        $frequency = array_count_values($keywords);
        arsort($frequency);
        
        return array_slice(array_keys($frequency), 0, 10);
    }
    
    /**
     * Найти толкования по ключевым словам
     * 
     * @param array $keywords Ключевые слова
     * @param int $excludeId ID толкования для исключения
     * @param int $limit Количество
     * @return \Illuminate\Support\Collection
     */
    private static function findByKeywords(array $keywords, int $excludeId, int $limit)
    {
        if (empty($keywords)) {
            return collect();
        }
        
        $minDate = \Carbon\Carbon::create(2026, 1, 16, 0, 0, 0);
        
        // Получаем все готовые толкования
        $interpretations = DreamInterpretation::where('processing_status', 'completed')
            ->whereNull('api_error')
            ->whereHas('result')
            ->where('created_at', '>=', $minDate)
            ->where('id', '!=', $excludeId)
            ->with('result')
            ->orderBy('created_at', 'desc')
            ->limit(100) // Ограничиваем для производительности
            ->get();
        
        // Для каждого толкования вычисляем релевантность
        $scored = $interpretations->map(function($interpretation) use ($keywords) {
            try {
                $seo = SeoHelper::forDreamAnalyzerResult($interpretation);
                
                // Проверяем валидность SEO
                $title = $seo['title'] ?? '';
                if (empty($title)) {
                    return null;
                }
                
                $defaultTitlePattern = 'Толкование сна - Анализ сна';
                if (str_contains($title, $defaultTitlePattern)) {
                    return null;
                }
                
                if (mb_strlen($title) < 20 || mb_strlen($title) > 200) {
                    return null;
                }
                
                // Собираем весь текст для поиска
                $searchText = mb_strtolower(implode(' ', [
                    $seo['title'] ?? '',
                    $seo['description'] ?? '',
                    $seo['h1'] ?? '',
                    $seo['h1_description'] ?? '',
                ]));
                
                // Подсчитываем совпадения ключевых слов
                $score = 0;
                foreach ($keywords as $keyword) {
                    if (mb_strpos($searchText, mb_strtolower($keyword)) !== false) {
                        $score++;
                    }
                }
                
                // Бонус за близость по дате (если создано в том же месяце)
                $dateBonus = 0;
                // Можно добавить логику для бонуса по дате
                
                return [
                    'interpretation' => $interpretation,
                    'score' => $score,
                    'seo' => $seo,
                ];
            } catch (\Exception $e) {
                return null;
            }
        })
        ->filter()
        ->sortByDesc('score')
        ->pluck('interpretation');
        
        // Исключаем дубликаты по meta title (полностью одинаковые заголовки)
        $scored = self::removeDuplicateTitles($scored);
        
        return $scored->take($limit);
    }
    
    /**
     * Удалить дубликаты по meta title (полностью одинаковые заголовки)
     * Оставляет только первое вхождение каждого уникального title
     * 
     * @param \Illuminate\Support\Collection $interpretations Коллекция толкований
     * @param string|null $excludeTitle Заголовок для исключения (например, текущего толкования)
     * @return \Illuminate\Support\Collection
     */
    private static function removeDuplicateTitles($interpretations, ?string $excludeTitle = null): \Illuminate\Support\Collection
    {
        $seenTitles = [];
        $excludeTitleNormalized = $excludeTitle ? self::normalizeTitle($excludeTitle) : null;
        
        return $interpretations->filter(function($interpretation) use (&$seenTitles, $excludeTitleNormalized) {
            try {
                $seo = SeoHelper::forDreamAnalyzerResult($interpretation);
                $title = $seo['title'] ?? '';
                
                if (empty($title)) {
                    return false;
                }
                
                // Нормализуем title для сравнения (убираем лишние пробелы, приводим к нижнему регистру)
                $normalizedTitle = self::normalizeTitle($title);
                
                // Исключаем, если это заголовок текущего толкования
                if ($excludeTitleNormalized && $normalizedTitle === $excludeTitleNormalized) {
                    return false;
                }
                
                // Если такой title уже встречался, исключаем дубликат
                if (isset($seenTitles[$normalizedTitle])) {
                    return false;
                }
                
                // Запоминаем этот title
                $seenTitles[$normalizedTitle] = true;
                
                return true;
            } catch (\Exception $e) {
                return false;
            }
        });
    }
    
    /**
     * Нормализовать заголовок для сравнения
     * Убирает лишние пробелы, приводит к нижнему регистру, удаляет знаки препинания на концах
     * 
     * @param string $title
     * @return string
     */
    private static function normalizeTitle(string $title): string
    {
        // Приводим к нижнему регистру
        $title = mb_strtolower($title);
        
        // Убираем лишние пробелы (множественные пробелы заменяем на один)
        $title = preg_replace('/\s+/', ' ', $title);
        
        // Убираем пробелы в начале и конце
        $title = trim($title);
        
        return $title;
    }
}
