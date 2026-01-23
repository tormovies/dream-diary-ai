<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;

class UpdateGuideArticlesContent extends Command
{
    protected $signature = 'guides:update-content';
    protected $description = 'Обновить контент всех существующих статей-инструкций с новым форматированием';

    public function handle()
    {
        $this->info('Обновление контента статей-инструкций...');

        $articles = Article::where('type', 'guide')->get();
        
        if ($articles->isEmpty()) {
            $this->warn('Статьи-инструкции не найдены.');
            return 0;
        }

        $updated = 0;
        $createCommand = new \App\Console\Commands\CreateGuideArticles();

        // Используем рефлексию для доступа к приватным методам
        $reflection = new \ReflectionClass($createCommand);
        
        foreach ($articles as $article) {
            $methodName = $this->getMethodNameBySlug($article->slug);
            
            if ($methodName) {
                try {
                    $method = $reflection->getMethod($methodName);
                    $method->setAccessible(true);
                    
                    // Вызываем метод с правильными параметрами
                    if ($methodName === 'getCategory2Content' || $methodName === 'getCategory9Content') {
                        $content = $method->invoke($createCommand, 'https://t.me/snovidec_ru');
                    } else {
                        $content = $method->invoke($createCommand);
                    }
                    
                    $article->content = $content;
                    $article->save();
                    $this->info("✓ Обновлено: {$article->title} (ID: {$article->id})");
                    $updated++;
                } catch (\Exception $e) {
                    $this->error("✗ Ошибка при обновлении {$article->title}: " . $e->getMessage());
                }
            } else {
                $this->warn("⚠ Пропущено: {$article->title} (slug: {$article->slug}) - метод не найден");
            }
        }

        $this->newLine();
        $this->info("Готово! Обновлено: {$updated} из {$articles->count()} статей");

        return 0;
    }

    private function getMethodNameBySlug(string $slug): ?string
    {
        $slugMap = [
            'nachalo-raboty' => 'getCategory1Content',
            'tolkovanie-snov' => 'getCategory2Content',
            'otchety-i-sny' => 'getCategory3Content',
            'analiz-otchetov' => 'getCategory4Content',
            'dnevnik-i-profil' => 'getCategory5Content',
            'druzya-i-soobshchestvo' => 'getCategory6Content',
            'kommentarii-i-vzaimodeystvie' => 'getCategory7Content',
            'poisk-i-navigatsiya' => 'getCategory8Content',
            'tehnicheskaya-podderzhka' => 'getCategory9Content',
            'bezopasnost-i-privatnost' => 'getCategory10Content',
        ];

        return $slugMap[$slug] ?? null;
    }
}
