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

namespace furqansiddiqui\BIP39;

use furqansiddiqui\BIP39\Exception\MnemonicException;
use furqansiddiqui\BIP39\Exception\WordlistException;

/**
 * Class BIP39
 * @package furqansiddiqui\BIP39
 */
class BIP39
{
    /** @var int */
    private $wordsCount;
    /** @var int */
    private $overallBits;
    /** @var int */
    private $checksumBits;
    /** @var int */
    private $entropyBits;
    /** @var null|string */
    private $entropy;
    /** @var null|string */
    private $checksum;
    /** @var null|array */
    private $rawBinaryChunks;
    /** @var null|array */
    private $words;

    /** @var null|Wordlist */
    private $wordlist;

    /**
     * @param string $entropy
     * @return Mnemonic
     * @throws MnemonicException
     * @throws WordlistException
     */
    public static function Entropy(string $entropy): Mnemonic
    {
        self::validateEntropy($entropy);

        $entropyBits = strlen($entropy) * 4;
        $checksumBits = (($entropyBits - 128) / 32) + 4;
        $wordsCount = ($entropyBits + $checksumBits) / 11;
        return (new self($wordsCount))
            ->useEntropy($entropy)
            ->wordlist(Wordlist::English())
            ->mnemonic();
    }

    /**
     * @param int $wordCount
     * @return Mnemonic
     * @throws MnemonicException
     * @throws WordlistException
     */
    public static function Generate(int $wordCount = 12): Mnemonic
    {
        return (new self($wordCount))
            ->generateSecureEntropy()
            ->wordlist(Wordlist::English())
            ->mnemonic();
    }

    /**
     * @param $words
     * @param Wordlist|null $wordlist
     * @param bool $verifyChecksum
     * @return Mnemonic
     * @throws MnemonicException
     * @throws WordlistException
     */
    public static function Words($words, ?Wordlist $wordlist = null, bool $verifyChecksum = true): Mnemonic
    {
        if (is_string($words)) {
            $words = explode(" ", $words);
        }

        if (!is_array($words)) {
            throw new MnemonicException('Mnemonic constructor requires an Array of words');
        }

        $wordCount = count($words);
        return (new self($wordCount))
            ->wordlist($wordlist ?? Wordlist::English())
            ->reverse($words, $verifyChecksum);
    }

    /**
     * BIP39 constructor.
     * @param int $wordCount
     * @throws MnemonicException
     */
    public function __construct(int $wordCount = 12)
    {
        if ($wordCount < 12 || $wordCount > 24) {
            throw new MnemonicException('Mnemonic words count must be between 12-24');
        } elseif ($wordCount % 3 !== 0) {
            throw new MnemonicException('Words count must be generated in multiples of 3');
        }

        // Actual words count
        $this->wordsCount = $wordCount;
        // Overall entropy bits (ENT+CS)
        $this->overallBits = $this->wordsCount * 11;
        // Checksum Bits are 1 bit per 3 words, starting from 12 words with 4 CS bits
        $this->checksumBits = (($this->wordsCount - 12) / 3) + 4;
        // Entropy Bits (ENT)
        $this->entropyBits = $this->overallBits - $this->checksumBits;
    }

    /**
     * @param string $entropy
     * @return BIP39
     * @throws MnemonicException
     */
    public function useEntropy(string $entropy): self
    {
        self::validateEntropy($entropy);
        $this->entropy = $entropy;
        $this->checksum = $this->checksum($entropy, $this->checksumBits);
        $this->rawBinaryChunks = str_split($this->hex2bits($this->entropy) . $this->checksum, 11);
        return $this;
    }

    /**
     * @return BIP39
     * @throws MnemonicException
     * @throws \Exception
     */
    public function generateSecureEntropy(): self
    {
        $this->useEntropy(bin2hex(random_bytes($this->entropyBits / 8)));
        return $this;
    }

    /**
     * @return Mnemonic
     * @throws MnemonicException
     */
    public function mnemonic(): Mnemonic
    {
        if (!$this->entropy) {
            throw new MnemonicException('Entropy is not defined');
        }

        if (!$this->wordlist) {
            throw new MnemonicException('Wordlist is not defined');
        }

        $mnemonic = new Mnemonic($this->entropy);
        foreach ($this->rawBinaryChunks as $bit) {
            $index = bindec($bit);
            $mnemonic->wordsIndex[] = $index;
            $mnemonic->words[] = $this->wordlist->getWord($index);
            $mnemonic->rawBinaryChunks[] = $bit;
            $mnemonic->wordsCount++;
        }

        return $mnemonic;
    }

    /**
     * @param Wordlist $wordlist
     * @return BIP39
     */
    public function wordlist(Wordlist $wordlist): self
    {
        $this->wordlist = $wordlist;
        return $this;
    }

    /**
     * @param array $words
     * @param bool $verifyChecksum
     * @return Mnemonic
     * @throws MnemonicException
     * @throws WordlistException
     */
    public function reverse(array $words, bool $verifyChecksum = true)
    {
        if (!$this->wordlist) {
            throw new MnemonicException('Wordlist is not defined');
        }

        $mnemonic = new Mnemonic();
        $pos = 0;
        foreach ($words as $word) {
            $pos++;
            $index = $this->wordlist->findIndex($word);
            if (is_null($index)) {
                throw new WordlistException(sprintf('Invalid/unknown word at position %d', $pos));
            }

            $mnemonic->words[] = $word;
            $mnemonic->wordsIndex[] = $index;
            $mnemonic->wordsCount++;
            $mnemonic->rawBinaryChunks[] = str_pad(decbin($index), 11, '0', STR_PAD_LEFT);
        }

        $rawBinary = implode('', $mnemonic->rawBinaryChunks);
        $entropyBits = substr($rawBinary, 0, $this->entropyBits);
        $checksumBits = substr($rawBinary, $this->entropyBits, $this->checksumBits);

        $mnemonic->entropy = $this->bits2hex($entropyBits);

        // Verify Checksum?
        if ($verifyChecksum) {
            if (!hash_equals($checksumBits, $this->checksum($mnemonic->entropy, $this->checksumBits))) {
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
            $bits .= str_pad(base_convert($hex[$i], 16, 2), 4, '0', STR_PAD_LEFT);
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
     * @param int $bits
     * @return string
     */
    private function checksum(string $entropy, int $bits): string
    {
        $checksumChar = ord(hash("sha256", hex2bin($entropy), true)[0]);
        $checksum = '';
        for ($i = 0; $i < $bits; $i++) {
            $checksum .= $checksumChar >> (7 - $i) & 1;
        }

        return $checksum;
    }

    /**
     * @param string $entropy
     * @throws MnemonicException
     */
    private static function validateEntropy(string $entropy): void
    {
        if (!preg_match('/^[a-f0-9]{2,}$/', $entropy)) {
            throw new MnemonicException('Invalid entropy (requires hexadecimal)');
        }

        $entropyBits = strlen($entropy) * 4;
        if (!in_array($entropyBits, [128, 160, 192, 224, 256])) {
            throw new MnemonicException('Invalid entropy length');
        }
    }
}