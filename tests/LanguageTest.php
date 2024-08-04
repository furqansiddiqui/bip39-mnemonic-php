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

include_once "ChineseWords.php";

class LanguageTest extends \PHPUnit\Framework\TestCase
{
    public function testLanguageFileLoading(): void
    {
        $chinese1 = ChineseWords::getInstance();
        $this->assertEquals("chinese_traditional", $chinese1->language);
    }

    /**
     * @return void
     */
    public function testLanguageSingleton(): void
    {
        \FurqanSiddiqui\BIP39\Language\English::getInstance();
        $eng = \FurqanSiddiqui\BIP39\Language\English::getInstance();
        $this->assertEquals(spl_object_id($eng), spl_object_id(\FurqanSiddiqui\BIP39\Language\English::getInstance()));
        $chinese1 = ChineseWords::getInstance();
        $eng2 = \FurqanSiddiqui\BIP39\Language\English::getInstance();
        $this->assertEquals(spl_object_id($chinese1), spl_object_id(ChineseWords::getInstance()));
        $this->assertNotEquals(spl_object_id($eng2), spl_object_id($chinese1));
        $this->assertEquals(spl_object_id($eng2), spl_object_id($eng));
    }
}
