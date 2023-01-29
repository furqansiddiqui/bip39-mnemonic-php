<?php
declare(strict_types=1);

require "../vendor/autoload.php";

/**
 * Test vectors and instructions taken from:
 * https://github.com/bitcoin/bips/blob/master/bip-0039.mediawiki#user-content-Test_vectors
 */

$seedPassphrase = "TREZOR";
$wordlist = \FurqanSiddiqui\BIP39\WordList::English();
$vectors = json_decode(file_get_contents("vectors.json"), true)["english"];
$results = [];
$pass = 0;
$fail = 0;

foreach ($vectors as $tI => $test) {
    $tResult = [
        "test" => [
            "entropy" => $test[0], // Entropy from test vector
            "words" => $test[1], // Words list from test vector
            "seed" => $test[2], // Seed from test vector
        ],
        "pass" => false
    ];

    $mA = \FurqanSiddiqui\BIP39\BIP39::Entropy($tResult["test"]["entropy"], $wordlist);
    $tResult["entropy2Words"] = implode(" ", $mA->words);
    $tResult["seedA"] = bin2hex($mA->generateSeed($seedPassphrase));
    $mB = \FurqanSiddiqui\BIP39\BIP39::Words($tResult["test"]["words"], $wordlist, verifyChecksum: true);
    $tResult["words2Entropy"] = $mB->entropy;
    $tResult["seedB"] = bin2hex($mB->generateSeed($seedPassphrase));

    if ($tResult["test"]["entropy"] === $tResult["words2Entropy"]) {
        if ($tResult["test"]["words"] === $tResult["entropy2Words"]) {
            if (hash_equals($tResult["seedA"], $tResult["seedB"])) {
                if (hash_equals($tResult["seedA"], $tResult["test"]["seed"])) {
                    $tResult["pass"] = true;
                    $pass++;
                }
            }
        }
    }

    if (!$tResult["pass"]) {
        $fail++;
    }

    $results[] = $tResult;
}

printf("Total pass: %d\r\n", $pass);
printf("Total fails: %d\r\n", $fail);
printf("\r\n\r\n");
var_dump($results);
