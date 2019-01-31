# BIP39 Mnemonic

Mnemonic BIP39 implementation in PHP

### Generate Mnemonic (12, 15, 18, 21 or 24 words)

Lib will generate mnemonic of 12, 15, 18, 21 or 24 words using a entropy driven from cryptographically secure pseudo-random bytes.

```php
<?php
declare(strict_types=1);

use \furqansiddiqui\BIP39\BIP39;

$mnemonic = BIP39::Generate(12);
var_dump($mnemonic);
```

### Generate Mnemonic using specified Entropy

Lib will use specified entropy to generate mnemonic:

```php
$mnemonic = BIP39::Entropy("receive predict chase romance table chat catalog scissors middle soap satisfy fit");
var_dump($mnemonic);
```