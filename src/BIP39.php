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

use Charcoal\Buffers\AbstractByteArray;
use Charcoal\Buffers\Buffer;
use FurqanSiddiqui\BIP39\Exception\Bip39EntropyException;
use FurqanSiddiqui\BIP39\Exception\Bip39MnemonicException;
use FurqanSiddiqui\BIP39\Language\AbstractLanguage;

/**
 * Class BIP39 Mnemonic Generator
 * @package FurqanSiddiqui\BIP39
 */
class BIP39
{
    /**
     * @param \FurqanSiddiqui\BIP39\Language\AbstractLanguage $wordList
     * @param int $wordCount
     * @return \FurqanSiddiqui\BIP39\Mnemonic
     * @throws \FurqanSiddiqui\BIP39\Exception\Bip39EntropyException
     * @throws \FurqanSiddiqui\BIP39\Exception\Bip39MnemonicException
     * @throws \Random\RandomException
     */
    public static function fromRandom(AbstractLanguage $wordList, int $wordCount = 12): Mnemonic
    {
        return static::fromEntropy(new Buffer(random_bytes(static::wordCountToBits($wordCount)[2] / 8)), $wordList);
    }

    /**
     * @param \Charcoal\Buffers\AbstractByteArray $entropy
     * @param \FurqanSiddiqui\BIP39\Language\AbstractLanguage $wordList
     * @return \FurqanSiddiqui\BIP39\Mnemonic
     * @throws \FurqanSiddiqui\BIP39\Exception\Bip39EntropyException
     */
    public static function fromEntropy(
        #[\SensitiveParameter]
        AbstractByteArray $entropy,
        AbstractLanguage  $wordList
    ): Mnemonic
    {
        // Validate Entropy Length
        $entropyBits = $entropy->len() * 8;
        if ($entropyBits < 128 || $entropyBits % 32 !== 0) {
            throw new Bip39EntropyException("Invalid entropy length");
        }

        // Convert entropy to padded
        $entropy16 = $entropy->toBase16();
        $entropyBn = "";
        for ($i = 0; $i < strlen($entropy16); $i++) {
            $entropyBn .= str_pad(base_convert($entropy16[$i], 16, 2), 4, "0", STR_PAD_LEFT);
        }

        // Create Checksum
        $checksumBn = static::checksum($entropy->raw(), $entropyBits / 32);

        // Combine Entropy+Checksum and Create 11 bit chunks
        $entropyChunks = str_split($entropyBn . $checksumBn, 11);

        // Find words
        $words = [];
        $wordsIndex = [];
        foreach ($entropyChunks as $chunk) {
            $index = bindec($chunk);
            if ($index < 0 || $index >= 2048) {
                throw new Bip39EntropyException('Bad BIP39 mnemonic entropy');
            }

            $wordsIndex[] = $index;
            $words[] = $wordList->words[$index];
        }

        // Return Mnemonic instance
        return new Mnemonic($wordList->language, $words, $wordsIndex, $entropy16);
    }

    /**
     * @param array $words
     * @param \FurqanSiddiqui\BIP39\Language\AbstractLanguage $wordList
     * @param bool $verifyChecksum
     * @return \FurqanSiddiqui\BIP39\Mnemonic
     * @throws \FurqanSiddiqui\BIP39\Exception\Bip39MnemonicException
     */
    public static function fromMnemonic(
        #[\SensitiveParameter] array $words,
        AbstractLanguage             $wordList,
        bool                         $verifyChecksum = true
    ): Mnemonic
    {
        $bitLen = static::wordCountToBits(count($words));

        // Create 11-bit chunks from words
        $chunksBn = "";
        $wordsIndex = [];
        $pos = 0;
        foreach ($words as $word) {
            $pos++;
            $index = $wordList->getIndex($word);
            if (is_null($index)) {
                throw new Bip39MnemonicException(sprintf('Bad BIP39 mnemonic word at index %d', $pos));
            }

            $wordsIndex[] = $index;
            $chunksBn .= str_pad(decbin($index), 11, "0", STR_PAD_LEFT);
        }

        $entropyBits = substr($chunksBn, 0, $bitLen[2]);
        $checksumBits = substr($chunksBn, $bitLen[2], $bitLen[1]);

        // Convert Entropy to Hexadecimal string
        $entropy16 = "";
        foreach (str_split($entropyBits, 4) as $chunk) {
            $entropy16 .= base_convert($chunk, 2, 16);
        }

        // Verify Checksum?
        if ($verifyChecksum) {
            if ($checksumBits !== static::checksum(hex2bin($entropy16), $bitLen[1])) {
                throw new Bip39MnemonicException('BIP39 mnemonic entropy checksum match failed');
            }
        }

        return new Mnemonic($wordList->language, $words, $wordsIndex, $entropy16);
    }

    /**
     * @param array $words
     * @param \FurqanSiddiqui\BIP39\Language\AbstractLanguage $wordList
     * @param bool $verifyChecksum
     * @return \FurqanSiddiqui\BIP39\Mnemonic
     * @throws \FurqanSiddiqui\BIP39\Exception\Bip39MnemonicException
     */
    public static function fromWords(array $words, AbstractLanguage $wordList, bool $verifyChecksum = true): Mnemonic
    {
        return static::fromMnemonic($words, $wordList, $verifyChecksum);
    }

    /**
     * @param string $entropyBn
     * @param int $checksumBits
     * @return string
     */
    private static function checksum(#[\SensitiveParameter] string $entropyBn, int $checksumBits): string
    {
        $checksumChar = ord(hash("sha256", $entropyBn, true)[0]);
        $checksum = "";
        for ($i = 0; $i < $checksumBits; $i++) {
            $checksum .= $checksumChar >> (7 - $i) & 1;
        }

        return $checksum;
    }

    /**
     * @param int $wordCount
     * @return float[]|int[]
     * @throws \FurqanSiddiqui\BIP39\Exception\Bip39MnemonicException
     */
    private static function wordCountToBits(int $wordCount): array
    {
        // Get the word count, minimum 12 and in multiplications of 3
        if ($wordCount < 12 || $wordCount % 3 !== 0) {
            throw new Bip39MnemonicException("Unacceptable word count for BIP39 mnemonic");
        }

        // Total bits in ENT+CS (each chunk is 11-bit)
        $overallBits = $wordCount * 11;
        // Checksum Bits are 1 bit per 3 words, starting from 12 words with 4 CS bits
        $checksumBits = (($wordCount - 12) / 3) + 4;
        // Final Entropy Bits
        $entropyBits = $overallBits - $checksumBits;

        return [$overallBits, $checksumBits, $entropyBits];
    }
}
