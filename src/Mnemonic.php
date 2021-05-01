<?php
/**
 * This file is a part of "furqansiddiqui/bip39-mnemonics-php" package.
 * https://github.com/furqansiddiqui/bip39-mnemonics-php
 *
 * Copyright (c) 2019 Furqan A. Siddiqui <hello@furqansiddiqui.com>
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
class Mnemonic
{
    /** @var string */
    public $entropy;
    /** @var int */
    public $wordsCount;
    /** @var array */
    public $wordsIndex;
    /** @var array */
    public $words;
    /** @var array */
    public $rawBinaryChunks;

    /**
     * Mnemonic constructor.
     * @param string|null $entropy
     */
    public function __construct(?string $entropy = null)
    {
        $this->entropy = $entropy;
        $this->wordsCount = 0;
        $this->wordsIndex = [];
        $this->words = [];
        $this->rawBinaryChunks = [];
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
