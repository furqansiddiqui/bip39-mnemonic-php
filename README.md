# BIP39 Mnemonic

Mnemonic BIP39 implementation in PHP

## Installation

### Prerequisite

* PHP 7.2+
* [ext-mbstring](http://php.net/manual/en/book.mbstring.php) (MultiByte string PHP ext. for non-english wordlist)

### Composer

`composer require furqansiddiqui/bip39-mnemonic-php`

## Mnemonic Object

This lib will create this Mnemonic object as a result:

| Prop | Data | Description
| --- | --- | ---
| entropy | string | Hexadecimal representation of binary Entropy bits
| wordsCount | int | Number of words (12, 15,18, 21 or 24)
| wordsIndex | array | Indexed array (of integers) indexes from wordlist
| words | array | Indexed array of mnemonic codes
| rawBinaryChunks | array | Indexed array of binary bits (1s and 0s) each containing 11 bits according to [BIP39 Spec](https://github.com/bitcoin/bips/blob/master/bip-0039.mediawiki)

## Generate Mnemonic Codes (12, 15, 18, 21 or 24 words)

Generate mnemonic of 12, 15, 18, 21 or 24 words using an entropy driven from cryptographically secure pseudo-random bytes. 

```php
<?php
declare(strict_types=1);

use \FurqanSiddiqui\BIP39\BIP39;

$mnemonic = BIP39::Generate(12);
var_dump($mnemonic->words);
# array(12) { [0]=> string(6) "barrel" [1]=> string(6) "viable" [2]=> string(6) "become" [3]=> string(4) "kiss" [4]=> string(6) "spider" [5]=> string(8) "business" [6]=> string(4) "wool" [7]=> string(6) "amused" [8]=> string(7) "satoshi" [9]=> string(4) "duty" [10]=> string(4) "girl" [11]=> string(5) "april" }
var_dump($mnemonic->entropy);
# string(32) "12de684fbd6d1a3e3f5041bf68918905" 
```

## Generate Mnemonic using specified Entropy

Specify your own entropy to generate mnemonic codes:

```php
<?php
declare(strict_types=1);

use \FurqanSiddiqui\BIP39\BIP39;

$mnemonic = BIP39::Entropy("f47f0e5dcf6d1ddf0e70791dafc9ae512130891817769976cd50533021e58a8b");
var_dump($mnemonic->wordsCount); # int(24) 
var_dump($mnemonic->words); # array(24) { [0]=> string(7) "virtual" [1]=> string(4) "wear" [2]=> stri...
var_dump($mnemonic->rawBinaryChunks); # array(24) { [0]=> string(11) "11110100011" [1]=> string(11) "11111000011" [2]=> string(11) "10010...
var_dump($mnemonic->entropy); # string(64) "f47f0e5dcf6d1ddf0e70791dafc9ae512130891817769976cd50533021e58a8b"
```

## Reverse (Mnemonic to Entropy)

Use mnemonic codes to find entropy. By default lib will cross-check checksum therefore not using valid mnemonic codes will throw an exception.

```php
<?php
declare(strict_types=1);

use \FurqanSiddiqui\BIP39\BIP39;

$mnemonic = BIP39::Words("virtual wear number paddle spike usage degree august buffalo layer high pelican basic duty gate uphold offer reopen favorite please acoustic version clay leader");
var_dump($mnemonic->entropy); # string(64) "f47f0e5dcf6d1ddf0e70791dafc9ae512130891817769976cd50533021e58a8b"
```

## Generate non-english mnemonic codes

Mnemonic codes may be generated in ALL languages supported in BIP39 spec. This example generates 12 mnemonic codes in Spanish language as an example and it may be replaced with other any other language with wordlists in BIP39 spec, check [here](https://github.com/bitcoin/bips/blob/master/bip-0039/bip-0039-wordlists.md).

```php
<?php
declare(strict_types=1);

use \FurqanSiddiqui\BIP39\BIP39;
use \FurqanSiddiqui\BIP39\Wordlist;

$mnemonic = (new BIP39(12)) // 12 words
    ->generateSecureEntropy() // Generate cryptographically secure entropy
    ->wordlist(Wordlist::Spanish()) // Use Spanish wordlist
    ->mnemonic(); // Generate mnemonic
    
print implode(" ", $mnemonic->words); # bastón tímido turismo pez pez fideo pellejo persona brinco yoga rasgo diluir
print $mnemonic->entropy; # 1c9cfbc5d93b26b12bcd8c229fdb07a2
```