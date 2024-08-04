<?php
/*
 * This file is a part of "furqansiddiqui/bip39-mnemonics-php" package.
 * https://github.com/furqansiddiqui/bip39-mnemonics-php
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/furqansiddiqui/bip39-mnemonics-php/blob/master/LICENSE
 */

declare(strict_types=1);

namespace FurqanSiddiqui\BIP39\Language;

/**
 * Class AbstractLanguageFile
 * @package FurqanSiddiqui\BIP39\Language
 */
abstract class AbstractLanguageFile extends AbstractLanguage
{
    /**
     * @param string $pathToFile
     * @param string $eolChar
     * @param bool $wordProcessing Converts to lowercase, trims " \n\r\t\v\x00" from left and right
     * @param string|null $mbEncoding
     * @return array
     */
    final protected static function wordsFromFile(
        string  $pathToFile,
        string  $eolChar = PHP_EOL,
        bool    $wordProcessing = true,
        ?string $mbEncoding = null
    ): array
    {
        $words = explode($eolChar, file_get_contents($pathToFile));
        if ($wordProcessing) {
            $words = array_map(function (string $word) use ($mbEncoding) {
                return mb_strtolower(trim($word), $mbEncoding);
            }, $words);
        }

        return $words;
    }
}
