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

class ChineseWords extends \FurqanSiddiqui\BIP39\Language\AbstractLanguageFile
{
    /**
     * @return static
     */
    protected static function constructor(): static
    {
        return new static(
            language: "chinese_traditional",
            words: static::wordsFromFile(
                pathToFile: dirname(__DIR__) . "/wordlists/chinese_traditional.txt",
                eolChar: "\n"
            ),
            mbEncoding: "UTF-8"
        );
    }
}
