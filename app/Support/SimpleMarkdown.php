<?php

namespace App\Support;

/**
 * Convertisseur volontairement minimal (pas de dépendance Composer type
 * league/commonmark) — ne couvre que le sous-ensemble de syntaxe réellement
 * utilisé par le texte des Conditions générales édité par l'admin : titres
 * #/##/###, listes à puces, citations, séparateurs, gras/italique et
 * paragraphes. Tout le texte est échappé avant transformation, donc du HTML
 * tapé par erreur dans le textarea admin s'affiche tel quel plutôt que de
 * casser la mise en page.
 */
class SimpleMarkdown
{
    public static function toHtml(?string $markdown): string
    {
        if (blank($markdown)) {
            return '';
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($markdown));
        $html = [];
        $paragraph = [];
        $listItems = [];
        $quoteLines = [];

        $flushParagraph = function () use (&$paragraph, &$html) {
            if ($paragraph !== []) {
                $html[] = '<p>' . implode(' ', $paragraph) . '</p>';
                $paragraph = [];
            }
        };

        $flushList = function () use (&$listItems, &$html) {
            if ($listItems !== []) {
                $html[] = '<ul>' . implode('', $listItems) . '</ul>';
                $listItems = [];
            }
        };

        $flushQuote = function () use (&$quoteLines, &$html) {
            if ($quoteLines !== []) {
                $html[] = '<blockquote>' . implode(' ', $quoteLines) . '</blockquote>';
                $quoteLines = [];
            }
        };

        foreach ($lines as $rawLine) {
            $line = rtrim($rawLine);

            if (trim($line) === '') {
                $flushParagraph();
                $flushList();
                $flushQuote();
                continue;
            }

            if (preg_match('/^(#{1,3})\s+(.*)$/', $line, $m)) {
                $flushParagraph();
                $flushList();
                $flushQuote();
                $level = strlen($m[1]);
                $html[] = "<h{$level}>" . self::inline($m[2]) . "</h{$level}>";
                continue;
            }

            if (preg_match('/^-{3,}$/', trim($line))) {
                $flushParagraph();
                $flushList();
                $flushQuote();
                $html[] = '<hr>';
                continue;
            }

            if (preg_match('/^[-*]\s+(.*)$/', $line, $m)) {
                $flushParagraph();
                $flushQuote();
                $listItems[] = '<li>' . self::inline($m[1]) . '</li>';
                continue;
            }

            if (preg_match('/^>\s?(.*)$/', $line, $m)) {
                $flushParagraph();
                $flushList();
                $quoteLines[] = self::inline($m[1]);
                continue;
            }

            $flushList();
            $flushQuote();
            $paragraph[] = self::inline($line);
        }

        $flushParagraph();
        $flushList();
        $flushQuote();

        return implode("\n", $html);
    }

    private static function inline(string $text): string
    {
        $escaped = e($text);
        $escaped = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $escaped);
        $escaped = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/', '<em>$1</em>', $escaped);

        return $escaped;
    }
}
