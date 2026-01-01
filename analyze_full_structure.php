<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$interp = \App\Models\DreamInterpretation::where('hash', 'siOIAGGml6FlVwYHvWOVln1HVdSVoJzv')->first();

if (!$interp) {
    echo "Interpretation not found\n";
    exit;
}

$result = \App\Models\DreamInterpretationResult::where('dream_interpretation_id', $interp->id)->first();

if (!$result || !$result->analysis_data) {
    echo "No result or analysis_data\n";
    exit;
}

$data = $result->analysis_data;

function analyzeStructure($data, $prefix = '', $maxDepth = 10, $currentDepth = 0) {
    if ($currentDepth >= $maxDepth) {
        return;
    }
    
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $currentPath = $prefix ? "$prefix.$key" : $key;
            
            if (is_array($value)) {
                if (empty($value)) {
                    echo "$currentPath: [] (empty array)\n";
                } elseif (isset($value[0]) && is_numeric(array_keys($value)[0])) {
                    // Numeric array
                    $firstItem = reset($value);
                    if (is_array($firstItem)) {
                        echo "$currentPath: array[" . count($value) . "] of objects\n";
                        if ($currentDepth < 3) {
                            echo "  First item keys: " . implode(', ', array_keys($firstItem)) . "\n";
                        }
                    } else {
                        echo "$currentPath: array[" . count($value) . "] of " . gettype($firstItem) . "s\n";
                        if (is_string($firstItem) && strlen($firstItem) < 100) {
                            echo "  First item: " . substr($firstItem, 0, 80) . "\n";
                        }
                    }
                } else {
                    // Associative array
                    echo "$currentPath: object with keys: " . implode(', ', array_keys($value)) . "\n";
                    analyzeStructure($value, $currentPath, $maxDepth, $currentDepth + 1);
                }
            } else {
                $type = gettype($value);
                $preview = is_string($value) ? substr($value, 0, 80) : var_export($value, true);
                if (strlen($preview) > 80) {
                    $preview = substr($preview, 0, 80) . "...";
                }
                echo "$currentPath: $type - $preview\n";
            }
        }
    }
}

echo "=== FULL STRUCTURE ANALYSIS ===\n\n";
analyzeStructure($data);

echo "\n\n=== SYMBOLIC ELEMENTS DETAIL ===\n";
if (!empty($data['symbolic_elements'])) {
    foreach ($data['symbolic_elements'] as $type => $elements) {
        echo "\n$type:\n";
        if (is_array($elements) && !empty($elements)) {
            $first = reset($elements);
            if (is_array($first)) {
                echo "  Keys in first element: " . implode(', ', array_keys($first)) . "\n";
                echo "  First element:\n";
                foreach ($first as $k => $v) {
                    if (is_array($v)) {
                        echo "    $k: array[" . count($v) . "]\n";
                    } else {
                        $preview = is_string($v) ? substr($v, 0, 60) : var_export($v, true);
                        echo "    $k: " . substr($preview, 0, 60) . "\n";
                    }
                }
            }
        }
    }
}

