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

use FurqanSiddiqui\BIP39\Exception\MnemonicException;
use FurqanSiddiqui\BIP39\Exception\WordListException;

/**
 * Class BIP39
 * @package FurqanSiddiqui\BIP39
 */
class BIP39
{
    /** @var string */
    public const VERSION = "0.1.6";

    /** @var int */
    public readonly int $overallBits;
    /** @var int */
    public readonly int $checksumBits;
    /** @var int */
    public readonly int $entropyBits;

    /**
     * @param string $entropy
     * @param string|\FurqanSiddiqui\BIP39\WordList $lang
     * @return \FurqanSiddiqui\BIP39\Mnemonic
     * @throws \FurqanSiddiqui\BIP39\Exception\MnemonicException
     * @throws \FurqanSiddiqui\BIP39\Exception\WordListException
     */
    public static function Entropy(string $entropy, string|WordList $lang = "english"): Mnemonic
    {
        static::validateHexEntropy($entropy);
        $entropyBits = strlen($entropy) * 4;
        $checksumBits = (($entropyBits - 128) / 32) + 4;
        $wordsCount = ($entropyBits + $checksumBits) / 11;
        return (new self($wordsCount, is_string($lang) ? WordList::getLanguage($lang) : $lang))
            ->entropy2Mnemonic($entropy);
    }

    /**
     * @param int $wordCount
     * @param string|\FurqanSiddiqui\BIP39\WordList $lang
     * @return \FurqanSiddiqui\BIP39\Mnemonic
     * @throws \FurqanSiddiqui\BIP39\Exception\MnemonicException
     * @throws \FurqanSiddiqui\BIP39\Exception\WordListException
     */
    public static function Generate(int $wordCount = 12, string|WordList $lang = "english"): Mnemonic
    {
        return (new self($wordCount, is_string($lang) ? WordList::getLanguage($lang) : $lang))
            ->generateSecureMnemonic();
    }

    /**
     * @param string|array $words
     * @param string|\FurqanSiddiqui\BIP39\WordList $lang
     * @param bool $verifyChecksum
     * @return \FurqanSiddiqui\BIP39\Mnemonic
     * @throws \FurqanSiddiqui\BIP39\Exception\MnemonicException
     * @throws \FurqanSiddiqui\BIP39\Exception\WordListException
     */
    public static function Words(string|array $words, string|WordList $lang = "english", bool $verifyChecksum = true): Mnemonic
    {
        if (is_string($words)) {
            $words = explode(" ", $words);
        }

        return (new self(count($words), is_string($lang) ? WordList::getLanguage($lang) : $lang))
            ->words2Mnemonic($words, $verifyChecksum);
    }

    /**
     * @param int $wordsCount
     * @param \FurqanSiddiqui\BIP39\WordList $wordsList
     * @throws \FurqanSiddiqui\BIP39\Exception\MnemonicException
     */
    public function __construct(public readonly int $wordsCount, public readonly WordList $wordsList)
    {
        if ($this->wordsCount < 12 || $this->wordsCount > 24) {
            throw new MnemonicException('Mnemonic words count must be between 12-24');
        } elseif ($this->wordsCount % 3 !== 0) {
            throw new MnemonicException('Words count must be generated in multiples of 3');
        }

        // Overall entropy bits (ENT+CS)
        $this->overallBits = $this->wordsCount * 11;
        // Checksum Bits are 1 bit per 3 words, starting from 12 words with 4 CS bits
        $this->checksumBits = (($this->wordsCount - 12) / 3) + 4;
        // Entropy Bits (ENT)
        $this->entropyBits = $this->overallBits - $this->checksumBits;
    }

    /**
     * @param string $entropy
     * @return array
     * @throws \FurqanSiddiqui\BIP39\Exception\MnemonicException
     */
    private function entropy2Chunks(string $entropy): array
    {
        static::validateHexEntropy($entropy);
        return str_split($this->hex2bits($entropy) . $this->checksum($entropy), 11);
    }

    /**
     * @return \FurqanSiddiqui\BIP39\Mnemonic
     * @throws \FurqanSiddiqui\BIP39\Exception\MnemonicException
     */
    public function generateSecureMnemonic(): Mnemonic
    {
        try {
            $prng = random_bytes($this->entropyBits / 8);
        } catch (\Exception) {
            throw new \RuntimeException('Failed to generate secure PRNG entropy');
        }

        return $this->entropy2Mnemonic($prng);
    }

    /**
     * @param string $entropy
     * @return \FurqanSiddiqui\BIP39\Mnemonic
     * @throws \FurqanSiddiqui\BIP39\Exception\MnemonicException
     */
    public function entropy2Mnemonic(string $entropy): Mnemonic
    {
        return new Mnemonic($this->wordsList, $entropy, $this->entropy2Chunks($entropy));
    }

    /**
     * @param array $words
     * @param bool $verifyChecksum
     * @return \FurqanSiddiqui\BIP39\Mnemonic
     * @throws \FurqanSiddiqui\BIP39\Exception\MnemonicException
     * @throws \FurqanSiddiqui\BIP39\Exception\WordListException
     */
    public function words2Mnemonic(array $words, bool $verifyChecksum = true): Mnemonic
    {
        if (count($words) !== $this->wordsCount) {
            throw new MnemonicException(
                sprintf('Required word count of %d, got %d', $this->wordsCount, count($words))
            );
        }

        $chunks = [];
        $pos = 0;
        foreach ($words as $word) {
            $pos++;
            $index = $this->wordsList->findIndex($word);
            if (is_null($index)) {
                throw new WordListException(sprintf('Invalid/unknown word at position %d', $pos));
            }

            $chunks[] = str_pad(decbin($index), 11, "0", STR_PAD_LEFT);
        }

        $bits = implode("", $chunks);
        $entropyBits = substr($bits, 0, $this->entropyBits);
        $checksumBits = substr($bits, $this->entropyBits, $this->checksumBits);
        $mnemonic = $this->entropy2Mnemonic($this->bits2hex($entropyBits));

        // Verify Checksum?
        if ($verifyChecksum) {
            if ($checksumBits !== $this->checksum($mnemonic->entropy)) {
                throw new MnemonicException('Entropy checksum match failed');
            }
        }

        return $mnemonic;
    }

    /**
     * @param string $hex
     * @return string
     */
    private function hex2bits(string $hex): string
    {
        $bits = "";
        for ($i = 0; $i < strlen($hex); $i++) {
            $bits .= str_pad(base_convert($hex[$i], 16, 2), 4, "0", STR_PAD_LEFT);
        }
        return $bits;
    }

    /**
     * @param string $bits
     * @return string
     */
    private function bits2hex(string $bits): string
    {
        $hex = "";
        foreach (str_split($bits, 4) as $chunk) {
            $hex .= base_convert($chunk, 2, 16);
        }

        return $hex;
    }

    /**
     * @param string $entropy
     * @return string
     */
    private function checksum(string $entropy): string
    {
        $checksumChar = ord(hash("sha256", hex2bin($entropy), true)[0]);
        $checksum = "";
        for ($i = 0; $i < $this->checksumBits; $i++) {
            $checksum .= $checksumChar >> (7 - $i) & 1;
        }

        return $checksum;
    }

    /**
     * @param string $entropy
     * @return void
     * @throws \FurqanSiddiqui\BIP39\Exception\MnemonicException
     */
    private static function validateHexEntropy(string $entropy): void
    {
        if (!preg_match('/^[a-f0-9]{2,}$/', $entropy)) {
            throw new MnemonicException('Invalid entropy (requires hexadecimal)');
        }

        if (!in_array(strlen($entropy) * 4, [128, 160, 192, 224, 256])) {
            throw new MnemonicException('Invalid entropy length');
        }
    }
}
