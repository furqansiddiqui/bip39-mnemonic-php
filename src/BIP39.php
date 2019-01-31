<?php
declare(strict_types=1);

namespace furqansiddiqui\BIP39;

use furqansiddiqui\BIP39\Exception\MnemonicException;

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
    public $entropy;
    /** @var null|string */
    public $checksum;
    /** @var null|array */
    public $rawBinaryChunks;
    /** @var null|array */
    public $words;

    /**
     * @param string $entropy
     * @return Mnemonic
     * @throws MnemonicException
     */
    public static function Entropy(string $entropy): Mnemonic
    {
        self::validateEntropy($entropy);

        $entropyBits = strlen($entropy) * 4;
        $checksumBits = (($entropyBits - 128) / 32) + 4;
        $wordsCount = ($entropyBits + $checksumBits) / 11;
        return (new self($wordsCount))
            ->useEntropy($entropy)
            ->mnemonic();
    }

    /**
     * @param int $wordCount
     * @return Mnemonic
     * @throws MnemonicException
     */
    public static function Generate(int $wordCount = 12): Mnemonic
    {
        return (new self($wordCount))
            ->generateSecureEntropy()
            ->mnemonic();
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

        $checksumChar = ord(hash("sha256", hex2bin($this->entropy), true)[0]);
        $checksum = '';
        for ($i = 0; $i < $this->checksumBits; $i++) {
            $checksum .= $checksumChar >> (7 - $i) & 1;
        }

        $this->checksum = $checksum;
        $this->rawBinaryChunks = str_split($this->hex2bits($this->entropy) . $checksum, 11);


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

        $mnemonic = new Mnemonic($this->entropy);
        foreach ($this->rawBinaryChunks as $bit) {
            $index = bindec($bit);
            $mnemonic->wordsIndex[] = $index;
            $mnemonic->words[] = "";
            $mnemonic->rawBinaryChunks[] = $bit;
            $mnemonic->wordsCount++;
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