<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;

class UpdateGuideArticlesFormat extends Command
{
    protected $signature = 'guides:update-format';
    protected $description = 'Обновить форматирование всех существующих статей-инструкций';

    public function handle()
    {
        $this->info('Обновление форматирования статей-инструкций...');

        $articles = Article::where('type', 'guide')->get();
        
        if ($articles->isEmpty()) {
            $this->warn('Статьи-инструкции не найдены.');
            return 0;
        }

        $updated = 0;
        $createCommand = new CreateGuideArticles();

        foreach ($articles as $article) {
            // Определяем категорию по slug
            $categoryContent = $this->getCategoryContentBySlug($article->slug, $createCommand);
            
            if ($categoryContent) {
                $article->content = $categoryContent;
                $article->save();
                $this->info("✓ Обновлено: {$article->title} (ID: {$article->id})");
                $updated++;
            } else {
                $this->warn("⚠ Пропущено: {$article->title} (slug: {$article->slug}) - категория не найдена");
            }
        }

        $this->newLine();
        $this->info("Готово! Обновлено: {$updated} из {$articles->count()} статей");

        return 0;
    }

    private function getCategoryContentBySlug(string $slug, CreateGuideArticles $createCommand): ?string
    {
        $telegramLink = 'https://t.me/snovidec_ru';
        
        $categoryMap = [
            'nachalo-raboty' => fn() => $createCommand->getCategory1Content(),
            'tolkovanie-snov' => fn() => $createCommand->getCategory2Content($telegramLink),
            'otchety-i-sny' => fn() => $createCommand->getCategory3Content(),
            'analiz-otchetov' => fn() => $createCommand->getCategory4Content(),
            'dnevnik-i-profil' => fn() => $createCommand->getCategory5Content(),
            'druzya-i-soobshchestvo' => fn() => $createCommand->getCategory6Content(),
            'kommentarii-i-vzaimodeystvie' => fn() => $createCommand->getCategory7Content(),
            'poisk-i-navigatsiya' => fn() => $createCommand->getCategory8Content(),
            'tehnicheskaya-podderzhka' => fn() => $createCommand->getCategory9Content($telegramLink),
            'bezopasnost-i-privatnost' => fn() => $createCommand->getCategory10Content(),
        ];

        if (isset($categoryMap[$slug])) {
            return $categoryMap[$slug]();
        }

        return null;
    }
}
