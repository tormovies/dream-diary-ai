<?php

namespace App\View\Helpers;

/**
 * Универсальный помощник для отображения данных анализа снов
 * 
 * Обрабатывает различные структуры данных от разных традиций
 * и безопасно отображает их в шаблонах
 */
class DreamAnalysisViewHelper
{
    /**
     * Определить традицию из данных
     * 
     * @param \App\Models\DreamInterpretationResult $result
     * @param array $analysisData
     * @return string|null
     */
    public static function detectTradition($result, array $analysisData): ?string
    {
        // Приоритет: response_metadata.tradition_used
        if (isset($analysisData['response_metadata']['tradition_used'])) {
            return $analysisData['response_metadata']['tradition_used'];
        }
        
        // Затем tradition_name из БД
        if (!empty($result->tradition_name)) {
            return $result->tradition_name;
        }
        
        // Затем из tradition_specific
        if (isset($analysisData['tradition_specific']['tradition_name'])) {
            return $analysisData['tradition_specific']['tradition_name'];
        }
        
        return null;
    }

    /**
     * Безопасное отображение значения любого типа
     * 
     * @param mixed $value
     * @param string $fieldName Название поля для контекста
     * @return string
     */
    public static function safeDisplay($value, string $fieldName = ''): string
    {
        if (is_null($value)) {
            return '';
        }
        
        if (is_bool($value)) {
            return $value ? 'Да' : 'Нет';
        }
        
        if (is_numeric($value)) {
            return (string) $value;
        }
        
        if (is_string($value)) {
            return trim($value);
        }
        
        if (is_array($value)) {
            return self::renderArray($value, $fieldName);
        }
        
        if (is_object($value)) {
            return self::renderArray((array) $value, $fieldName);
        }
        
        return (string) $value;
    }

    /**
     * Рендеринг массива в читаемый формат
     * 
     * @param array $array
     * @param string $fieldName
     * @return string
     */
    private static function renderArray(array $array, string $fieldName = ''): string
    {
        if (empty($array)) {
            return '';
        }
        
        // Если это простой индексированный массив строк
        if (self::isSimpleStringArray($array)) {
            return implode(', ', array_map('trim', $array));
        }
        
        // Если это массив объектов/ассоциативных массивов
        if (self::isArrayOfObjects($array)) {
            return self::renderArrayOfObjects($array, $fieldName);
        }
        
        // Ассоциативный массив - форматируем как JSON для читаемости
        return self::formatAsReadableJson($array);
    }

    /**
     * Проверка, является ли массив простым массивом строк
     * 
     * @param array $array
     * @return bool
     */
    private static function isSimpleStringArray(array $array): bool
    {
        if (empty($array)) {
            return false;
        }
        
        // Проверяем, что все ключи числовые и все значения - строки
        $keys = array_keys($array);
        $isNumericKeys = array_reduce($keys, function($carry, $key) {
            return $carry && is_numeric($key);
        }, true);
        
        if (!$isNumericKeys) {
            return false;
        }
        
        $allStrings = array_reduce($array, function($carry, $value) {
            return $carry && is_string($value);
        }, true);
        
        return $allStrings;
    }

    /**
     * Проверка, является ли массив массивом объектов/ассоциативных массивов
     * 
     * @param array $array
     * @return bool
     */
    private static function isArrayOfObjects(array $array): bool
    {
        if (empty($array)) {
            return false;
        }
        
        // Проверяем, что первый элемент - массив или объект
        $firstElement = reset($array);
        return is_array($firstElement) && !self::isSimpleStringArray($firstElement);
    }

    /**
     * Рендеринг массива объектов
     * 
     * @param array $array
     * @param string $fieldName
     * @return string
     */
    private static function renderArrayOfObjects(array $array, string $fieldName = ''): string
    {
        $result = [];
        
        foreach ($array as $index => $item) {
            if (is_array($item)) {
                $itemText = self::formatObjectAsText($item);
                if (!empty($itemText)) {
                    $result[] = $itemText;
                }
            } else {
                $result[] = self::safeDisplay($item);
            }
        }
        
        return implode("\n\n", array_filter($result));
    }

    /**
     * Форматирование объекта как текст
     * 
     * @param array $object
     * @return string
     */
    private static function formatObjectAsText(array $object): string
    {
        $parts = [];
        
        foreach ($object as $key => $value) {
            $displayKey = self::formatKey($key);
            $displayValue = self::safeDisplay($value, $key);
            
            if (!empty($displayValue)) {
                if (is_string($value) && strlen($value) < 100) {
                    $parts[] = "{$displayKey}: {$displayValue}";
                } else {
                    $parts[] = "{$displayKey}:\n{$displayValue}";
                }
            }
        }
        
        return implode("\n", $parts);
    }

    /**
     * Форматирование ключа для отображения
     * 
     * @param string $key
     * @return string
     */
    private static function formatKey(string $key): string
    {
        // Заменяем подчеркивания на пробелы и делаем первую букву заглавной
        $formatted = str_replace('_', ' ', $key);
        return mb_ucfirst(mb_strtolower($formatted));
    }

    /**
     * Форматирование как читаемый JSON
     * 
     * @param array $data
     * @return string
     */
    private static function formatAsReadableJson(array $data): string
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        // Улучшаем читаемость для небольших структур
        if (strlen($json) < 500) {
            return $json;
        }
        
        // Для больших структур возвращаем компактный формат
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Получить значение из вложенной структуры по пути
     * 
     * @param array $data
     * @param string $path Путь вида "key.subkey.0.field"
     * @param mixed $default
     * @return mixed
     */
    public static function getNestedValue(array $data, string $path, $default = null)
    {
        $keys = explode('.', $path);
        $value = $data;
        
        foreach ($keys as $key) {
            if (is_numeric($key)) {
                $key = (int) $key;
            }
            
            if (!isset($value[$key])) {
                return $default;
            }
            
            $value = $value[$key];
        }
        
        return $value;
    }

    /**
     * Проверить, существует ли путь в данных
     * 
     * @param array $data
     * @param string $path
     * @return bool
     */
    public static function hasNestedValue(array $data, string $path): bool
    {
        return self::getNestedValue($data, $path, '__NOT_FOUND__') !== '__NOT_FOUND__';
    }

    /**
     * Рендеринг списка элементов
     * 
     * @param array $items
     * @param string $itemType Тип элемента для контекста
     * @return string HTML для списка
     */
    public static function renderList(array $items, string $itemType = 'item'): string
    {
        if (empty($items)) {
            return '';
        }
        
        $html = '<ul class="list-disc list-inside space-y-1 ml-2">';
        
        foreach ($items as $item) {
            $displayValue = self::safeDisplay($item, $itemType);
            if (!empty($displayValue)) {
                $html .= '<li class="text-sm text-gray-700 dark:text-gray-300">' . e($displayValue) . '</li>';
            }
        }
        
        $html .= '</ul>';
        
        return $html;
    }

    /**
     * Рендеринг тегов/бейджей
     * 
     * @param array $tags
     * @param string $cssClass Дополнительные CSS классы
     * @return string HTML для тегов
     */
    public static function renderTags(array $tags, string $cssClass = ''): string
    {
        if (empty($tags)) {
            return '';
        }
        
        $html = '<div class="flex flex-wrap gap-2 ' . e($cssClass) . '">';
        
        foreach ($tags as $tag) {
            $tagText = self::safeDisplay($tag);
            if (!empty($tagText)) {
                $formattedTag = mb_ucfirst(mb_strtolower(str_replace('_', ' ', $tagText)));
                $html .= '<span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300 rounded-full text-sm border border-gray-300 dark:border-gray-600">';
                $html .= e($formattedTag);
                $html .= '</span>';
            }
        }
        
        $html .= '</div>';
        
        return $html;
    }
}

