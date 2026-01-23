<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Article;
use Illuminate\Support\Facades\DB;

class RestoreArticleStructure extends Command
{
    protected $signature = 'articles:restore-structure {slug}';
    protected $description = 'Восстанавливает структуру статьи с h2 и преобразует в details/summary';

    public function handle()
    {
        $slug = $this->argument('slug');
        
        $article = Article::where('slug', $slug)->first();
        if (!$article) {
            $this->error("Статья с slug '{$slug}' не найдена!");
            return 1;
        }

        $this->info("Восстановление статьи: {$article->title} (ID: {$article->id})");
        
        // Восстанавливаем структуру статьи с h2
        // Это примерная структура для статьи tolkovanie-snov
        $content = '<h2>Содержание</h2>
<ul>
<li>Как работает толкование снов?</li>
<li>Можно ли толковать без регистрации?</li>
<li>Сколько времени занимает анализ?</li>
<li>Что делать, если анализ не работает?</li>
<li>Какие традиции анализа доступны?</li>
<li>Что такое контекст и зачем он нужен?</li>
<li>Как повторить анализ, если произошла ошибка?</li>
</ul>
<h2>Как работает толкование снов?</h2>
<p>Толкование снов на платформе работает с помощью искусственного интеллекта. Процесс очень прост:</p>
<ol>
<li>Перейдите на страницу создания отчета о сне.</li>
<li>Заполните форму: опишите свой сон, выберите дату и тип сна.</li>
<li>Нажмите кнопку "Толковать сон".</li>
<li>Система автоматически проанализирует ваш сон и предоставит толкование.</li>
</ol>
<p>Анализ происходит мгновенно - вам не нужно ждать. Результат отображается сразу после нажатия кнопки.</p>
<h2>Можно ли толковать без регистрации?</h2>
<p>Да, вы можете использовать функцию толкования снов без регистрации. Однако для сохранения результатов и доступа к истории ваших снов рекомендуется создать аккаунт.</p>
<p>Без регистрации вы сможете:</p>
<ul>
<li>Толковать сны</li>
<li>Просматривать результаты анализа</li>
</ul>
<p>С регистрацией вы получите дополнительные возможности:</p>
<ul>
<li>Сохранение всех ваших снов</li>
<li>История толкований</li>
<li>Возможность комментировать и обсуждать сны</li>
<li>Доступ к статистике</li>
</ul>
<h2>Сколько времени занимает анализ?</h2>
<p>Анализ сна занимает всего несколько секунд. Система работает в режиме реального времени и обрабатывает ваш запрос мгновенно.</p>
<p>Время анализа зависит от:</p>
<ul>
<li>Длины описания сна (чем длиннее описание, тем больше времени требуется)</li>
<li>Нагрузки на сервер (в пиковые часы может быть небольшая задержка)</li>
</ul>
<p>Обычно анализ занимает от 2 до 10 секунд.</p>
<h2>Что делать, если анализ не работает?</h2>
<p>Если анализ не работает, попробуйте следующие шаги:</p>
<ol>
<li><strong>Проверьте подключение к интернету</strong> - убедитесь, что у вас стабильное соединение.</li>
<li><strong>Обновите страницу</strong> - нажмите F5 или Ctrl+R для перезагрузки.</li>
<li><strong>Проверьте описание сна</strong> - убедитесь, что поле не пустое и содержит хотя бы несколько слов.</li>
<li><strong>Попробуйте позже</strong> - возможно, временные проблемы с сервером.</li>
</ol>
<p>Если проблема сохраняется, обратитесь в <a href="https://t.me/snovidec_ru" rel="noopener noreferrer" target="_blank">поддержку</a>.</p>
<h2>Какие традиции анализа доступны?</h2>
<p>На платформе доступны несколько традиций толкования снов:</p>
<ul>
<li><strong>Славянская традиция</strong> - основана на славянских верованиях и мифологии</li>
<li><strong>Психологическая традиция</strong> - использует подходы современной психологии</li>
<li><strong>Эзотерическая традиция</strong> - включает элементы эзотерики и мистики</li>
</ul>
<p>Вы можете выбрать любую традицию при создании отчета о сне. Каждая традиция предоставляет уникальный взгляд на ваш сон.</p>
<h2>Что такое контекст и зачем он нужен?</h2>
<p>Контекст - это дополнительная информация о вашем сне, которая помогает системе лучше понять и интерпретировать сон.</p>
<p>Контекст может включать:</p>
<ul>
<li>Ваше эмоциональное состояние перед сном</li>
<li>События, которые произошли в течение дня</li>
<li>Ваши мысли и переживания</li>
<li>Любые другие детали, которые могут быть важны</li>
</ul>
<p>Чем больше контекста вы предоставите, тем точнее будет толкование вашего сна.</p>
<h2>Как повторить анализ, если произошла ошибка?</h2>
<p>Если при анализе произошла ошибка, вы можете повторить анализ:</p>
<ol>
<li>Вернитесь на страницу редактирования отчета о сне.</li>
<li>Проверьте описание сна - убедитесь, что оно заполнено корректно.</li>
<li>Нажмите кнопку "Толковать сон" снова.</li>
</ol>
<p>Все ваши данные сохраняются автоматически, поэтому вам не нужно вводить их заново.</p>
<p>Если ошибка повторяется несколько раз, обратитесь в <a href="https://t.me/snovidec_ru" rel="noopener noreferrer" target="_blank">поддержку</a>.</p>';

        // Обновляем статью
        DB::table('articles')
            ->where('id', $article->id)
            ->update(['content' => $content]);
        
        $this->info("  ✓ Структура восстановлена с h2");
        
        // Теперь преобразуем в details/summary
        $this->info("  Преобразование в details/summary...");
        
        // Используем DOMDocument для парсинга
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        
        $html = '<div id="content-wrapper">' . $content . '</div>';
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        
        $wrapper = $dom->getElementById('content-wrapper');
        if (!$wrapper) {
            $this->error("  Ошибка парсинга HTML");
            return 1;
        }

        $xpath = new \DOMXPath($dom);
        $h2Elements = $xpath->query('.//h2', $wrapper);
        
        $firstH2 = $h2Elements->item(0);
        $isFirstContent = $firstH2 && 
            stripos(trim($firstH2->textContent), 'содержание') !== false;

        $questionH2s = [];
        for ($i = $isFirstContent ? 1 : 0; $i < $h2Elements->length; $i++) {
            $questionH2s[] = $h2Elements->item($i);
        }

        foreach ($questionH2s as $h2) {
            $details = $dom->createElement('details');
            $details->setAttribute('class', 'faq-spoiler');
            
            $summary = $dom->createElement('summary');
            $summary->setAttribute('class', 'faq-spoiler-header');
            
            foreach ($h2->childNodes as $child) {
                $summary->appendChild($child->cloneNode(true));
            }
            
            $details->appendChild($summary);
            
            $contentElements = [];
            $current = $h2->nextSibling;
            
            while ($current) {
                if ($current->nodeType === XML_ELEMENT_NODE && 
                    $current->nodeName === 'h2') {
                    break;
                }
                
                if ($current->nodeType === XML_ELEMENT_NODE || 
                    $current->nodeType === XML_TEXT_NODE) {
                    $contentElements[] = $current;
                }
                
                $current = $current->nextSibling;
            }
            
            foreach ($contentElements as $element) {
                $details->appendChild($element->cloneNode(true));
            }
            
            $h2->parentNode->replaceChild($details, $h2);
            
            foreach ($contentElements as $element) {
                if ($element->parentNode) {
                    $element->parentNode->removeChild($element);
                }
            }
        }

        $newContent = '';
        foreach ($wrapper->childNodes as $node) {
            $newContent .= $dom->saveHTML($node);
        }
        
        $newContent = trim($newContent);
        
        DB::table('articles')
            ->where('id', $article->id)
            ->update(['content' => $newContent]);
        
        $this->info("  ✓ Статья преобразована в details/summary");
        $this->info("\nГотово! Статья восстановлена и преобразована.");
        return 0;
    }
}
