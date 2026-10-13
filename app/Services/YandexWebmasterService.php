<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class YandexWebmasterService
{
    public function request(string $method, string $path, array $data = []): array
    {
        $encrypted = Setting::getValue('yandex_seo_token');
        if (! $encrypted) {
            throw new RuntimeException('OAuth-токен Яндекс Вебмастера не настроен.');
        }
        $request = Http::withToken(Crypt::decryptString($encrypted), 'OAuth')
            ->acceptJson()->timeout(60)->connectTimeout(15);
        $response = $request->send($method, 'https://api.webmaster.yandex.net/v4/'.ltrim($path, '/'), [
            $method === 'GET' ? 'query' : 'json' => $data,
        ]);
        if (! $response->successful()) {
            throw new RuntimeException('Яндекс Вебмастер: HTTP '.$response->status().' ('.($response->json('error_code') ?? $response->json('code') ?? 'API_ERROR').').');
        }
        $result = $response->json();
        if (! is_array($result)) {
            throw new RuntimeException('Яндекс Вебмастер вернул ответ неизвестного формата.');
        }

        return $result;
    }

    public function hostPath(string $suffix): string
    {
        $user = (string) Setting::getValue('yandex_seo_user_id', '');
        $host = (string) Setting::getValue('yandex_seo_host_id', '');
        if ($user === '' || $host === '') {
            throw new RuntimeException('Выберите сайт Яндекс Вебмастера.');
        }

        return 'user/'.rawurlencode($user).'/hosts/'.rawurlencode($host).'/'.ltrim($suffix, '/');
    }

    public function download(string $url): string
    {
        $parts = parse_url($url);
        if (($parts['scheme'] ?? '') !== 'https' || ($parts['host'] ?? '') !== 'storage.mds.yandex.net' || isset($parts['user']) || isset($parts['pass']) || (isset($parts['port']) && $parts['port'] !== 443)) {
            throw new RuntimeException('Неожиданный адрес файла выгрузки Яндекса.');
        }
        $response = Http::timeout(120)->withOptions(['allow_redirects' => false])->get($url);
        if (! $response->successful()) {
            throw new RuntimeException('Не удалось скачать выгрузку Яндекса: HTTP '.$response->status());
        }
        if (strlen($response->body()) > 50 * 1024 * 1024) {
            throw new RuntimeException('Выгрузка превышает допустимый размер 50 МБ. Уменьшите чанк.');
        }

        return $response->body();
    }
}
