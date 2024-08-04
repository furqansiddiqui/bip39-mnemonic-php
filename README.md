# BIP39 Mnemonic

Mnemonic BIP39 implementation in PHP

## Installation

### Prerequisite

* PHP ^8.2
* [ext-mbstring](http://php.net/manual/en/book.mbstring.php) (MultiByte string PHP ext. for non-english wordlist)

### Composer

`composer require furqansiddiqui/bip39-mnemonic-php`

## Generating a Secure Mnemonic

Generate a mnemonic using a secure PRNG implementation.

### `BIP39::fromRandom`

| Argument   | Type               | Description                   |
|------------|--------------------|-------------------------------|
| $wordList  | `AbstractLanguage` | Langage instance for wordlist |
| $wordCount | int                | Number of words (12-24)       |

**Returns instance of [Mnemonic](#mnemonic-class) class.**

#### Example:

```php
// Generate entropy using PRNG
$mnemonic = \FurqanSiddiqui\BIP39\BIP39::fromRandom(
    \FurqanSiddiqui\BIP39\Language\English::getInstance(),
    wordCount: 12
);

# array(12) { [0]=> string(4) "tape" [1]=> string(8) "solution" ... [10]=> string(6) "border" [11]=> string(6) "sample" }
var_dump($mnemonic->words);
# string(32) "ddd9dbcd1b07a09c16f080637818675f"
var_dump($mnemonic->entropy);
```

## Entropy to Mnemonic

Generate mnemonic codes from given entropy

### `BIP39::fromRandom`

| Argument  | Type                | Description                   |
|-----------|---------------------|-------------------------------|
| $entropy  | `AbstractByteArray` | Entropy                       |
| $wordList | `AbstractLanguage`  | Langage instance for wordlist |

**Returns instance of [Mnemonic](#mnemonic-class) class.**

#### Example:

```php
$mnemonic = \FurqanSiddiqui\BIP39\BIP39::fromEntropy(
    \Charcoal\Buffers\Buffer::fromBase16("ddd9dbcd1b07a09c16f080637818675f"),
    \FurqanSiddiqui\BIP39\Language\English::getInstance()
);

# array(12) { [0]=> string(4) "tape" [1]=> string(8) "solution" ... [10]=> string(6) "border" [11]=> string(6) "sample" }
var_dump($mnemonic->words);
```

## Mnemonic sentence/Words to Mnemonic

Generate entropy from mnemonic codes

### `BIP39::fromWords`

| Argument        | Type               | Description                                                           |
|-----------------|--------------------|-----------------------------------------------------------------------|
| $words          | Array\<string>     | Array of mnemonic codes                                               |
| $wordList       | `AbstractLanguage` | Langage instance for wordlist                                         |
| $verifyChecksum | bool               | Defaults to `TRUE`, computes and verifies checksum as per BIP39 spec. |

**Returns instance of [Mnemonic](#mnemonic-class) class.**

#### Example:

```php
$mnemonic = \FurqanSiddiqui\BIP39\BIP39::fromWords(
    ["tape", "solution", "viable", "current", "key",
        "evoke", "forward", "avoid", "gloom", "school", "border", "sample"],
    \FurqanSiddiqui\BIP39\Language\English::getInstance()
);

#string(32) "ddd9dbcd1b07a09c16f080637818675f"
var_dump($mnemonic->entropy);
```
## Mnemonic Class

### `readonly class`  Mnemonic

This lib will create this Mnemonic object as a result:

| Property   | Type           | Description                                                           |
|------------|----------------|-----------------------------------------------------------------------|
| language   | string         | Language name (as it was passed to constructor of wordlist class)     |
| words      | Array\<string> | Mnemonic codes                                                        |
| wordsIndex | Array\<int>    | Position/index # of each mnemonic code corresponding to wordlist used |
| wordsCount | int            | Number of mnemonic codes (i.e. 12, 15, 18, 21 or 24)                  |
| entropy    | string         | Entropy in hexadecimal encoding                                       |


### Generating Seed with Passphrase

#### `Mnemonic->generateSeed`

Generates seed from a mnemonic as per BIP39 specifications.

| Argument    | Type   | Description                                |
|-------------|--------|--------------------------------------------|
| $passphrase | string | Defaults to empty string ("")              |
| $bytes      | int    | Number of bytes to return, defaults to 64. |

**Returns**:

| Type   | Description                                          |
|--------|------------------------------------------------------| 
| string | Returns computed PBKDF2 hash in RAW BINARY as string |

## Generate non-english mnemonic codes

Check `AbstractLanguage` and `AbstractLanguageFile` classes. [English](src/Language/English.php) class has all 
2048 words pre-loaded instead of reading from a local file every time. To implement other languages or 
custom set of 2048 words, check [ChineseWords.php](tests/ChineseWords.php) file in `tests` directory for example of implementation.

```php
class CustomWords extends \FurqanSiddiqui\BIP39\Language\AbstractLanguageFile
{
    /**
     * @return static
     */
    protected static function constructor(): static
    {
        return new static(
            language: "some_language",
            words: static::wordsFromFile(
                pathToFile: "/path/to/wordlist.txt",
                eolChar: PHP_EOL
            ),
            mbEncoding: "UTF-8"
        );
    }
}
```

and then use in place of `AbstractLanguage` where required:

```php
CustomWords::getInstance()
```

## Test Vectors

Include PHPUnit tests for all test vectors mentioned in [official BIP-0039 specification](https://github.com/bitcoin/bips/blob/master/bip-0039.mediawiki#user-content-Test_vectors). 
Use following command to execute all tests using `phpunit.phar`

```php phpunit.phar --bootstrap vendor/autoload.php tests/```
