<?php
declare(strict_types=1);

namespace furqansiddiqui\BIP39;

/**
 * Class Mnemonic
 * @package furqansiddiqui\BIP39
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
}