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

namespace FurqanSiddiqui\BIP39;

/**
 * Class Mnemonic
 * @package FurqanSiddiqui\BIP39
 */
readonly class Mnemonic
{
    public int $wordsCount;

    /**
     * @param string $language
     * @param array $words
     * @param array $wordsIndex
     * @param string $entropy
     */
    public function __construct(
        public string $language,
        #[\SensitiveParameter]
        public array  $words,
        public array  $wordsIndex,
        #[\SensitiveParameter]
        public string $entropy
    )
    {
        $this->wordsCount = count($this->words);
    }

    /**
     * @param string $passphrase
     * @param int $bytes
     * @return string
     */
    public function generateSeed(string $passphrase = "", int $bytes = 0): string
    {
        return hash_pbkdf2(
            "sha512",
            implode(" ", $this->words),
            "mnemonic" . $passphrase,
            2048,
            $bytes,
            true
        );
    }
}
