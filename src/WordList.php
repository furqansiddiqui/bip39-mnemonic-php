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

use FurqanSiddiqui\BIP39\Exception\WordListException;

/**
 * Class WordList
 * @package FurqanSiddiqui\BIP39
 */
class WordList
{
    /** @var array */
    private static array $instances = [];

    /** @var string */
    public readonly string $language;
    /** @var array */
    public readonly array $words;
    /** @var int */
    public readonly int $count;

    /**
     * @return WordList
     * @throws WordListException
     */
    public static function English(): self
    {
        return self::getLanguage("english");
    }

    /**
     * @return WordList
     * @throws WordListException
     */
    public static function French(): self
    {
        return self::getLanguage("french");
    }

    /**
     * @return WordList
     * @throws WordListException
     */
    public static function Italian(): self
    {
        return self::getLanguage("italian");
    }

    /**
     * @return WordList
     * @throws WordListException
     */
    public static function Spanish(): self
    {
        return self::getLanguage("spanish");
    }

    /**
     * @param string $lang
     * @return WordList
     * @throws WordListException
     */
    public static function getLanguage(string $lang = "english"): self
    {
        $instance = self::$instances[$lang] ?? null;
        if ($instance) {
            return $instance;
        }

        $wordList = new self($lang);
        self::$instances[$lang] = $wordList;
        return self::getLanguage($lang);
    }

    /**
     * WordList constructor.
     * @param string $language
     * @throws WordListException
     */
    public function __construct(string $language = "english")
    {
        $this->language = trim($language);
        $words = [];
        $count = 0;

        $wordListFile = sprintf('%1$s%2$swordlists%2$s%3$s.txt', __DIR__, DIRECTORY_SEPARATOR, $this->language);
        if (!file_exists($wordListFile) || !is_readable($wordListFile)) {
            throw new WordListException(
                sprintf('BIP39 wordlist for "%s" not found or is not readable', ucfirst($this->language))
            );
        }

        $wordList = preg_split("/(\r\n|\n|\r)/", file_get_contents($wordListFile));
        foreach ($wordList as $word) {
            $words[] = mb_strtolower(trim($word));
            $count++;
        }

        $this->words = $words;
        $this->count = $count;
        if ($this->count !== 2048) {
            throw new WordListException('BIP39 words list file must have precise 2048 entries');
        }
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return [sprintf('BIP39 wordlist for "%s" Language', ucfirst($this->language))];
    }

    /**
     * @param string $word
     * @return int|null
     */
    public function findIndex(string $word): ?int
    {
        $search = mb_strtolower($word);
        foreach ($this->words as $pos => $word) {
            if ($search === $word) {
                return $pos;
            }
        }

        return null;
    }
}
