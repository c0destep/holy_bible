# Holy Bible API

![GitHub contributors](https://img.shields.io/github/contributors/c0destep/holy_bible?style=for-the-badge)
![Packagist Downloads](https://img.shields.io/packagist/dm/c0destep/holy_bible?style=for-the-badge)
![Packagist PHP Version](https://img.shields.io/packagist/dependency-v/c0destep/holy_bible/php?label=PHP&logo=PHP&style=for-the-badge)
![GitHub](https://img.shields.io/github/license/c0destep/holy_bible?style=for-the-badge)
[![README English](https://img.shields.io/badge/LANGUAGE-English-blue?style=for-the-badge)](https://github.com/c0destep/holy_bible/blob/main/docs/README-EN.md)

<!-- <img src="" alt=""> -->

> Está biblioteca tem como objetivo consumir o serviço [ABibliaDigital](https://www.abibliadigital.com.br) fornecido por
> [Márcio Sena](https://github.com/marciovsena).

## 💻 Pré-requisitos

Antes de começar, verifique se você atendeu aos seguintes requisitos:

- Você tem a versão `PHP 8.1` ou mais recente.
- Você tem instalada é ativada a extensão `php-curl`.

## 🚀 Instalando Holy Bible

Para instalar o Holy Bible, siga estas etapas:

```
composer require c0destep/holy_bible
```

## ☕ Usando Holy Bible

Para usar Holy Bible, siga o exemplo:

```php
use HolyBible\Bible;
use HolyBible\Books;

$bible = new Bible();

print_r($bible->getChapter(Books::FIRST_CORINTHIANS, 2));
```

## 📫 Contribuindo para Holy Bible

Para contribuir com Holy Bible, siga estas etapas:

1. Bifurque este repositório.
2. Crie um branch: `git checkout -b <nome_branch>`.
3. Faça suas alterações e confirme-as: `git commit -m '<mensagem_commit>'`
4. Envie para o branch original: `git push origin <nome_do_projeto> / <local>`
5. Crie a solicitação de pull.

Como alternativa, consulte a documentação do GitHub
em [como criar uma solicitação pull](https://help.github.com/en/github/collaborating-with-issues-and-pull-requests/creating-a-pull-request).

## 🤝 Colaboradores

Agradecemos às seguintes pessoas que contribuíram para este projeto:

<table>
  <tr>
    <td align="center">
      <a href="#">
        <img src="https://avatars.githubusercontent.com/u/65411044" width="100px;" alt="Foto do Lucas Alves no GitHub"/><br>
        <sub>
          <b>Lucas Alves</b>
        </sub>
      </a>
    </td>
  </tr>
</table>

## 😄 Seja um dos contribuidores<br>

Quer fazer parte desse projeto? Clique [AQUI](CONTRIBUTING.md) e leia como contribuir. Caso queira contribuir para
a [ABibliaDigital](https://github.com/marciovsena/abibliadigital).

## 📝 Licença

Esse projeto está sob licença. Veja o arquivo [LICENÇA](LICENSE.md) para mais detalhes.

[⬆ Voltar ao topo](#holy-bible-api)<br>
