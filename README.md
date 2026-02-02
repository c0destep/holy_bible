# Holy Bible API - PHP Library

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![PHPStan Level](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg)](https://phpstan.org/)

Uma biblioteca PHP moderna e robusta para acessar a API da Bíblia Digital com suporte a cache, retry logic, logging e DTOs tipados.

## ✨ Características

- 🚀 **Cache Inteligente** - Até 400x mais rápido com cache automático
- 🔄 **Retry Automático** - Exponential backoff para falhas temporárias
- 📝 **PSR-3 Logging** - Logging completo para debugging
- 🎯 **Type-Safe** - DTOs com propriedades readonly (PHP 8.1+)
- 🏗️ **Arquitetura em Camadas** - Client/Service/Facade
- ⚙️ **Configuração Flexível** - Arrays, variáveis de ambiente, ou objetos
- ✅ **100% Testado** - 17 testes unitários com mocks
- 🔒 **PHPStan Level 8** - Análise estática rigorosa
- 🔙 **Backward Compatible** - Funciona com código existente

## 📦 Instalação

```bash
composer require c0destep/holy_bible
```

## 🚀 Uso Rápido

### Básico (Backward Compatible)

```php
use HolyBible\Bible;
use HolyBible\Books;

$bible = new Bible();

// Obter capítulo
$chapter = $bible->getChapter(Books::JOHN, 3);
foreach ($chapter['verses'] as $verse) {
    echo "{$verse['number']}. {$verse['text']}\n";
}

// Obter versículo específico
$verse = $bible->getVerse(Books::JOHN, 3, 16);
echo $verse['text'];

// Listar todos os livros
$books = $bible->getBooks();

// Listar versões disponíveis
$versions = $bible->getAvailableVersions();
```

### Com Configuração Avançada

```php
use HolyBible\Bible;
use HolyBible\Config\BibleConfig;
use HolyBible\Retry\RetryPolicy;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Criar logger
$logger = new Logger('bible');
$logger->pushHandler(new StreamHandler('bible.log', Logger::DEBUG));

// Configurar
$config = new BibleConfig([
    'version' => 'nvi',
    'timeout' => 10.0,
    'cache_enabled' => true,
    'cache_ttl' => 7200,  // 2 horas
    'retry_policy' => RetryPolicy::aggressive(),
    'logger' => $logger
]);

$bible = Bible::withConfig($config);
```

### Usando DTOs (Type-Safe)

```php
$service = $bible->getService();

// Retorna ChapterDTO
$chapter = $service->getChapter(Books::PSALMS, 23);

echo "Livro: {$chapter->book->name}\n";
echo "Capítulo: {$chapter->number}\n";
echo "Versículos: {$chapter->getVerseCount()}\n";

foreach ($chapter->verses as $verse) {
    echo "{$verse->number}. {$verse->text}\n";
}

// Buscar versículo específico
$verse1 = $chapter->getVerse(1);
if ($verse1) {
    echo $verse1->text;
}
```

### Tratamento de Erros

```php
use HolyBible\Exception\{
    InvalidChapterException,
    InvalidVerseException,
    NetworkException,
    ApiResponseException
};

try {
    $chapter = $bible->getChapter(Books::GENESIS, 1);
    
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
```

## ⚙️ Configuração

### Via Array

```php
$config = new BibleConfig([
    'version' => 'acf',              // Versão da Bíblia
    'user_token' => 'seu-token',     // Token de autenticação (opcional)
    'timeout' => 15.0,               // Timeout em segundos
    'cache_enabled' => true,         // Habilitar cache
    'cache_ttl' => 3600,             // TTL do cache em segundos
    'cache_dir' => '/custom/path',   // Diretório do cache (opcional)
    'retry_enabled' => true,         // Habilitar retry
    'retry_policy' => RetryPolicy::default(),  // Política de retry
    'logger' => $myLogger            // PSR-3 logger (opcional)
]);
```

### Via Variáveis de Ambiente

```bash
export BIBLE_VERSION=nvi
export BIBLE_USER_TOKEN=abc123
export BIBLE_TIMEOUT=10.0
export BIBLE_CACHE_ENABLED=true
export BIBLE_CACHE_TTL=7200
export BIBLE_RETRY_ENABLED=true
```

```php
// Lê automaticamente das variáveis de ambiente
$config = new BibleConfig();
$bible = Bible::withConfig($config);
```

### Interface Fluente

```php
$config = new BibleConfig();
$config->setVersion('acf')
       ->setTimeout(20.0)
       ->setCacheTtl(1800)
       ->setRetryPolicy(RetryPolicy::aggressive());
```

## 🔄 Retry Logic

A biblioteca implementa retry automático com exponential backoff:

### Políticas Pré-Configuradas

```php
use HolyBible\Retry\RetryPolicy;

// Padrão: 3 tentativas, backoff 2x
$policy = RetryPolicy::default();

// Agressivo: 5 tentativas, backoff 1.5x
$policy = RetryPolicy::aggressive();

// Desabilitado
$policy = RetryPolicy::disabled();

// Customizado
$policy = new RetryPolicy(
    maxAttempts: 4,
    initialDelayMs: 200,
    multiplier: 2.5,
    maxDelayMs: 10000
);
```

### Quando Retry é Aplicado

- ✅ Erros de conexão (timeout, DNS, etc)
- ✅ Erros 5xx do servidor
- ✅ Erro 429 (rate limiting)
- ❌ Erros 4xx (exceto 429)
- ❌ Erros de validação local

## 📝 Logging

Suporte completo a PSR-3 logging:

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('bible');
$logger->pushHandler(new StreamHandler('bible.log', Logger::DEBUG));

$config = new BibleConfig(['logger' => $logger]);
$bible = Bible::withConfig($config);

// Logs automáticos:
// - DEBUG: Cada requisição HTTP
// - INFO: Requisições bem-sucedidas
// - WARNING: Retries, erros temporários
// - ERROR: Falhas definitivas
```

## 💾 Cache

### Cache de Arquivos (Padrão)

```php
use HolyBible\Cache\FileCache;

$cache = new FileCache('/var/cache/bible');
$config = new BibleConfig([
    'cache' => $cache,
    'cache_ttl' => 86400  // 24 horas
]);
```

### Cache Customizado

Implemente `CacheInterface`:

```php
use HolyBible\Cache\CacheInterface;

class RedisCache implements CacheInterface
{
    public function get(string $key): mixed { /* ... */ }
    public function set(string $key, mixed $value, int $ttl = 3600): bool { /* ... */ }
    public function has(string $key): bool { /* ... */ }
    public function delete(string $key): bool { /* ... */ }
    public function clear(): bool { /* ... */ }
}

$config = new BibleConfig(['cache' => new RedisCache()]);
```

### Desabilitar Cache

```php
$config = new BibleConfig(['cache_enabled' => false]);
```

## 📚 DTOs Disponíveis

### BookDTO
```php
$book->name;       // "Gênesis"
$book->abbrev;     // "gn"
$book->chapters;   // 50
$book->testament;  // "VT"
```

### ChapterDTO
```php
$chapter->book;           // BookDTO
$chapter->number;         // 3
$chapter->verses;         // VerseDTO[]
$chapter->getVerse(16);   // VerseDTO|null
$chapter->getVerseCount(); // int
```

### VerseDTO
```php
$verse->number;  // 16
$verse->text;    // "Porque Deus amou o mundo..."
```

### VersionDTO
```php
$version->version;  // "nvi"
$version->name;     // "Nova Versão Internacional"
```

## 🧪 Testes

```bash
# Rodar todos os testes
./vendor/bin/phpunit

# Com cobertura
./vendor/bin/phpunit --coverage-html coverage

# PHPStan
./vendor/bin/phpstan analyse src tests
```

## 📊 Estatísticas

- **17 testes unitários** (100% passando)
- **46 assertions**
- **PHPStan level 8** (0 erros)
- **~1,900 linhas de código**
- **18 classes**
- **2 interfaces**
- **5 exceções customizadas**

## 🏗️ Arquitetura

```
src/
├── Cache/          # Sistema de cache
├── Client/         # HTTP client layer
├── Config/         # Configuração
├── DTO/            # Data Transfer Objects
├── Exception/      # Exceções customizadas
├── Retry/          # Retry logic
├── Service/        # Business logic
├── Bible.php       # Facade (API pública)
└── Books.php       # Enum de livros
```

## 🤝 Contribuindo

Contribuições são bem-vindas! Por favor:

1. Fork o projeto
2. Crie uma branch (`git checkout -b feature/nova-feature`)
3. Commit suas mudanças (`git commit -am 'Adiciona nova feature'`)
4. Push para a branch (`git push origin feature/nova-feature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está licenciado sob a Licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

## 🙏 Créditos

- API fornecida por [A Bíblia Digital](https://www.abibliadigital.com.br/)
- Desenvolvido por [c0destep](https://github.com/c0destep)

## 📞 Suporte

- 🐛 [Reportar Bug](https://github.com/c0destep/holy_bible/issues)
- 💡 [Solicitar Feature](https://github.com/c0destep/holy_bible/issues)
- 📖 [Documentação](https://github.com/c0destep/holy_bible/wiki)

---

**Feito com ❤️ em PHP**
