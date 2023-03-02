# Holy Bible API

![GitHub contributors](https://img.shields.io/github/contributors/c0destep/holy_bible?style=for-the-badge)
![Packagist Downloads](https://img.shields.io/packagist/dm/c0destep/holy_bible?style=for-the-badge)
![Packagist PHP Version](https://img.shields.io/packagist/dependency-v/c0destep/holy_bible/php?label=PHP&logo=PHP&style=for-the-badge)
![GitHub](https://img.shields.io/github/license/c0destep/holy_biblee?style=for-the-badge)
[![README Português](https://img.shields.io/badge/LANGUAGE-Português-blue?style=for-the-badge)](https://github.com/c0destep/holy_bible/blob/main/README.md)

<!-- <img src="" alt=""> -->

> This library aims to consume the service [ABibliaDigital](https://www.abibliadigital.com.br) provided by
> [Márcio Sena](https://github.com/marciovsena).

## 💻 Prerequisites

Before you begin, make sure you've met the following requirements:

- You have `PHP 8.1` or newer.
- You have the `php-curl` extension installed and activated.

## 🚀 Installing Holy Bible

To install Holy Bible, follow these steps:

```
composer require c0destep/holy_bible
```

## ☕ Using Holy Bible

To use Holy Bible, follow the example:

```php
use HolyBible\Bible;
use HolyBible\Books;

$bible = new Bible();

print_r($bible->getChapter(Books::FIRST_CORINTHIANS, 2));
```

## 📫 Contributing to Holy Bible

To contribute to Holy Bible, follow these steps:

1. Fork this repository.
2. Create a branch: `git checkout -b <nome_branch>`.
3. Make your changes and confirm them: `git commit -m '<mensagem_commit>'`
4. Push to original branch: `git push origin <nome_do_projeto> / <local>`
5. Create the pull request.

Alternatively, see the GitHub documentation
on [how to create a pull request](https://help.github.com/en/github/collaborating-with-issues-and-pull-requests/creating-a-pull-request).

## 🤝 Collaborators

Thanks to the following people who contributed to this project:

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

## 😄 Be one of the contributors

Do you want to be part of this project? Click [HERE](CONTRIBUTING.md) and read how to contribute. If you want to
contribute to [ABibliaDigital](https://github.com/marciovsena/abibliadigital).

## 📝 License

This project is under license. See the [LICENÇA](LICENSE.md) file for details.

[⬆ Back to the top](#holy-bible-api)
