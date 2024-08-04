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

include_once "ChineseWords.php";

/**
 * Class Bip39TestVectors
 */
class Bip39VectorsTest extends \PHPUnit\Framework\TestCase
{
    private ?array $vectors = null;

    /**
     * @return void
     * @throws \FurqanSiddiqui\BIP39\Exception\Bip39EntropyException
     * @throws \FurqanSiddiqui\BIP39\Exception\Bip39MnemonicException
     */
    public function testEnglish(): void
    {
        $this->testVectors("English", "english", \FurqanSiddiqui\BIP39\Language\English::getInstance());
    }

    /**
     * @return void
     * @throws \FurqanSiddiqui\BIP39\Exception\Bip39EntropyException
     * @throws \FurqanSiddiqui\BIP39\Exception\Bip39MnemonicException
     */
    public function testChineseSimplified(): void
    {
        $this->testVectors("Chinese Traditional", "chinese_traditional", ChineseWords::getInstance());
    }

    /**
     * @param string $language
     * @param string $langProp
     * @param \FurqanSiddiqui\BIP39\Language\AbstractLanguage $wordList
     * @return void
     * @throws \FurqanSiddiqui\BIP39\Exception\Bip39EntropyException
     * @throws \FurqanSiddiqui\BIP39\Exception\Bip39MnemonicException
     */
    private function testVectors(string $language, string $langProp, \FurqanSiddiqui\BIP39\Language\AbstractLanguage $wordList): void
    {
        if (!$this->vectors) {
            $this->vectors = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "vectors.json"), true);
        }

        $vectors = $this->vectors[$langProp] ?? null;
        if (!$vectors || !is_array($vectors)) {
            throw new RuntimeException("No vectors for language: " . $language);
        }

        for ($i = 0; $i < count($vectors); $i++) {
            unset($secret1, $secret2, $seed1, $seed2);

            // Entropy to Words
            $secret1 = \FurqanSiddiqui\BIP39\BIP39::fromEntropy(\Charcoal\Buffers\Buffer::fromBase16($vectors[$i][0]), $wordList);
            $this->assertEquals(
                implode(" ", $secret1->words),
                $vectors[$i][1],
                $language . " vector #" . $i . " from Entropy to Words"
            );

            //  Words to Entropy
            $secret2 = \FurqanSiddiqui\BIP39\BIP39::fromMnemonic(explode(" ", $vectors[$i][1]), $wordList, true);
            $this->assertEquals(
                $secret2->entropy,
                $vectors[$i][0],
                $language . " vector #" . $i . " from Words to Entropy"
            );

            // Seed Generation
            $seed1 = $secret1->generateSeed("TREZOR");
            $seed2 = $secret2->generateSeed("TREZOR");

            $this->assertEquals($seed1, $seed2, $language . " vector #" . $i . " seed A and B");
            $this->assertEquals(bin2hex($seed1), $vectors[$i][2], $language . " vector #" . $i . " seeds");
        }
    }
}
