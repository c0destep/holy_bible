<?php

declare(strict_types=1);

use HolyBible\Bible;
use HolyBible\Books;
use HolyBible\Config\BibleConfig;
use HolyBible\Exception\ApiResponseException;
use HolyBible\Exception\InvalidChapterException;
use HolyBible\Exception\InvalidVerseException;
use HolyBible\Exception\NetworkException;
use HolyBible\Retry\RetryPolicy;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

require_once 'vendor/autoload.php';

$logger = new Logger('bible');
$logger->pushHandler(new StreamHandler('bible.log', Level::Debug));

$config = new BibleConfig([
    'version'       => 'nvi',
    //'user_token'    => '',
    'timeout'       => 10.0,
    'cache_enabled' => true,
    'cache_ttl'     => 120,
    //'cache_dir'     => '',
    'retry_enabled' => true,
    'retry_policy'  => RetryPolicy::aggressive(),
    'logger'        => $logger
]);

$bible = Bible::withConfig($config);
$service = $bible->getService();

try {
    $chapter = $service->getChapter(Books::PSALMS, 23);

    echo "Livro: {$chapter->book->name}\n";
    echo "Capítulo: $chapter->number\n";
    echo "Versículos: {$chapter->getVerseCount()}\n";

    foreach ($chapter->verses as $verse) {
        echo "$verse->number. $verse->text\n";
    }

    $verse1 = $chapter->getVerse(1);
    if ($verse1) {
        echo $verse1->text;
    }
} catch (InvalidChapterException $e) {
    // Entrada inválida (capítulo < 1)
    echo "Capítulo inválido: " . $e->getMessage();
} catch (InvalidVerseException $e) {
    // Entrada inválida (versículo < 1)
    echo "Versículo inválido: " . $e->getMessage();
} catch (NetworkException $e) {
    // Erro de rede/timeout
    echo "Erro de conexão: " . $e->getMessage();
    // Retry já foi tentado automaticamente
} catch (ApiResponseException $e) {
    // Erro da API (JSON inválido, etc)
    echo "Erro da API: " . $e->getMessage();
}
