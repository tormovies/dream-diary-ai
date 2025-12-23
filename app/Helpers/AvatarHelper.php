<?php

namespace App\Helpers;

class AvatarHelper
{
    /**
     * Получить эмодзи для аватара
     */
    public static function getEmoji(string $avatar): string
    {
        $emojiMap = [
            'avatar1.png' => '😀',
            'avatar2.png' => '😃',
            'avatar3.png' => '😄',
            'avatar4.png' => '😁',
            'avatar5.png' => '😆',
            'avatar6.png' => '😊',
            'avatar7.png' => '🙂',
            'avatar8.png' => '😉',
            'avatar9.png' => '😍',
            'avatar10.png' => '🤩',
            'avatar11.png' => '😎',
            'avatar12.png' => '🤗',
            'avatar13.png' => '😋',
            'avatar14.png' => '😌',
            'avatar15.png' => '😴',
            'avatar16.png' => '🤔',
        ];

        return $emojiMap[$avatar] ?? '👤';
    }

    /**
     * Получить цвет фона для аватара
     */
    public static function getBackgroundColor(string $avatar): string
    {
        $colorMap = [
            'avatar1.png' => 'bg-red-200',
            'avatar2.png' => 'bg-orange-200',
            'avatar3.png' => 'bg-yellow-200',
            'avatar4.png' => 'bg-green-200',
            'avatar5.png' => 'bg-teal-200',
            'avatar6.png' => 'bg-blue-200',
            'avatar7.png' => 'bg-indigo-200',
            'avatar8.png' => 'bg-purple-200',
            'avatar9.png' => 'bg-pink-200',
            'avatar10.png' => 'bg-rose-200',
            'avatar11.png' => 'bg-amber-200',
            'avatar12.png' => 'bg-lime-200',
            'avatar13.png' => 'bg-cyan-200',
            'avatar14.png' => 'bg-sky-200',
            'avatar15.png' => 'bg-violet-200',
            'avatar16.png' => 'bg-fuchsia-200',
        ];

        return $colorMap[$avatar] ?? 'bg-gray-200';
    }

    /**
     * Получить темный цвет фона для аватара (для темной темы)
     */
    public static function getDarkBackgroundColor(string $avatar): string
    {
        $colorMap = [
            'avatar1.png' => 'dark:bg-red-800',
            'avatar2.png' => 'dark:bg-orange-800',
            'avatar3.png' => 'dark:bg-yellow-800',
            'avatar4.png' => 'dark:bg-green-800',
            'avatar5.png' => 'dark:bg-teal-800',
            'avatar6.png' => 'dark:bg-blue-800',
            'avatar7.png' => 'dark:bg-indigo-800',
            'avatar8.png' => 'dark:bg-purple-800',
            'avatar9.png' => 'dark:bg-pink-800',
            'avatar10.png' => 'dark:bg-rose-800',
            'avatar11.png' => 'dark:bg-amber-800',
            'avatar12.png' => 'dark:bg-lime-800',
            'avatar13.png' => 'dark:bg-cyan-800',
            'avatar14.png' => 'dark:bg-sky-800',
            'avatar15.png' => 'dark:bg-violet-800',
            'avatar16.png' => 'dark:bg-fuchsia-800',
        ];

        return $colorMap[$avatar] ?? 'dark:bg-gray-700';
    }
}








