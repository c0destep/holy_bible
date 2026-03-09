# Guia de Contribuição

Obrigado por seu interesse em contribuir para o projeto `HolyBible`! Siga este guia para garantir que sua contribuição
seja processada da melhor forma possível.

## Primeiros Passos

1. **Faça um Fork** do repositório no GitHub.
2. **Clone** o seu fork localmente:
   ```bash
   git clone https://github.com/seu-usuario/holy_bible.git
   ```
3. **Instale as dependências** com o Composer:
   ```bash
   composer install
   ```
4. **Configure os Git Hooks** (utilizamos o CaptainHook para garantir a qualidade):
   ```bash
   ./vendor/bin/captainhook install
   ```

## Desenvolvimento

- Crie uma **nova branch** para sua alteração:
  ```bash
  git checkout -b feature/minha-melhoria
  # ou
  git checkout -b fix/correcao-de-bug
  ```
- Siga os padrões de código **PSR-12**.
- Adicione ou atualize **testes unitários** sempre que necessário.
- Mantenha a compatibilidade com o **PHP >= 8.1**.

## Qualidade do Código

Antes de realizar o commit, recomendamos executar as seguintes ferramentas manualmente:

1. **Testes Unitários (PHPUnit)**:
   ```bash
   ./vendor/bin/phpunit
   ```
2. **Análise Estática (PHPStan)**:
   ```bash
   ./vendor/bin/phpstan analyse src
   ```

Note que os hooks de **pre-commit** do CaptainHook executarão automaticamente o **linting**, a **normalização do
composer.json** e a **análise estática** do PHPStan em cada commit.

## Padrões de Commit

Seguimos o padrão **Conventional Commits**. As mensagens de commit devem ser claras e objetivas. Os tipos permitidos
são:

- `feat`: Uma nova funcionalidade.
- `fix`: Correção de um bug.
- `docs`: Alterações na documentação.
- `style`: Mudanças de formatação que não afetam o comportamento do código.
- `refactor`: Alteração que não corrige bug nem adiciona funcionalidade, mas melhora o código.
- `perf`: Mudança voltada para melhoria de performance.
- `test`: Adição ou correção de testes.
- `chore`: Mudanças em ferramentas auxiliares ou processos de build.
- `security`: Melhorias ou correções relacionadas à segurança.

**Exemplo:** `feat: adiciona suporte a cache em memória`

## Enviando sua Contribuição

1. Faça o **push** da sua branch para o seu fork:
   ```bash
   git push origin feature/minha-melhoria
   ```
2. Abra um **Pull Request** apontando para a branch `main` do repositório original.
3. Descreva detalhadamente as mudanças realizadas e o motivo.
4. Certifique-se de que o Pull Request passe em todas as verificações do GitHub Actions.

---

Obrigado por ajudar a tornar a biblioteca `HolyBible` ainda melhor!
