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
 * Class WordList
 * @package FurqanSiddiqui\BIP39
 */
abstract class AbstractLanguage
{
    private static array $instances = [];

    /**
     * @return static
     */
    abstract protected static function constructor(): static;

    /**
     * @return static
     */
    public static function getInstance(): static
    {
        if (!isset(static::$instances[static::class])) {
            static::$instances[static::class] = static::constructor();
        }

        return static::$instances[static::class];
    }

    /**
     * @param string $language
     * @param array $words
     * @param string|null $mbEncoding
     */
    final protected function __construct(
        public readonly string  $language,
        public readonly array   $words,
        public readonly ?string $mbEncoding = null,
    )
    {
        if (count($this->words) !== 2048) {
            throw new \RangeException('BIP39 requires set of 2048 words');
        }
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        return ["BIP39 Wordlist"];
    }

    /**
     * @param string $word
     * @return int|null
     */
    public function getIndex(string $word): ?int
    {
        $search = $this->mbEncoding ? mb_strtolower($word) : strtolower($word);
        foreach ($this->words as $i => $word) {
            if ($search === $word) {
                return $i;
            }
        }

        return null;
    }
}
