<?php

namespace App\Support;

use DOMDocument;
use DOMXPath;
use ZipArchive;

/**
 * Pulls plain paragraphs out of an uploaded document.
 *
 * A .docx is just a zip holding word/document.xml, so the text can be read with
 * the zip and DOM extensions already required by the app — no Office library,
 * and nothing new in the dependency tree.
 *
 * Supports .docx and .txt. The legacy binary .doc format is not readable this
 * way; those must be saved as .docx first.
 */
class DocumentParagraphs
{
    private const WORD_NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    /**
     * @return array<int, string> One entry per non-empty paragraph.
     */
    public static function fromFile(string $path, ?string $extension = null): array
    {
        $extension = strtolower($extension ?: pathinfo($path, PATHINFO_EXTENSION));

        $text = match ($extension) {
            'docx' => self::readDocx($path),
            'txt', 'md' => (string) file_get_contents($path),
            default => '',
        };

        return self::splitParagraphs($text);
    }

    /**
     * Concatenates each <w:p> element's text runs, one paragraph per line.
     */
    private static function readDocx(string $path): string
    {
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            return '';
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            return '';
        }

        $document = new DOMDocument;

        // Word writes markup this parser does not need to understand; suppress
        // libxml's complaints rather than failing the whole upload.
        $previous = libxml_use_internal_errors(true);
        $loaded = $document->loadXML($xml);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded) {
            return '';
        }

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('w', self::WORD_NS);

        $paragraphs = [];

        foreach ($xpath->query('//w:p') ?: [] as $paragraph) {
            $runs = [];

            foreach ($xpath->query('.//w:t', $paragraph) ?: [] as $run) {
                $runs[] = $run->textContent;
            }

            // A <w:br> inside a paragraph is a line break, not a new paragraph.
            $paragraphs[] = trim(implode('', $runs));
        }

        return implode("\n", $paragraphs);
    }

    /**
     * @return array<int, string>
     */
    private static function splitParagraphs(string $text): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        return collect(explode("\n", $text))
            ->map(fn (string $line) => trim(preg_replace('/\s+/u', ' ', $line) ?? ''))
            ->filter(fn (string $line) => $line !== '')
            ->values()
            ->all();
    }
}
