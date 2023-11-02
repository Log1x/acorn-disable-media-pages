# Acorn Disable Media Pages

![Latest Stable Version](https://img.shields.io/packagist/v/log1x/acorn-disable-media-pages.svg?style=flat-square)
![Total Downloads](https://img.shields.io/packagist/dt/log1x/acorn-disable-media-pages.svg?style=flat-square)
![Build Status](https://img.shields.io/github/actions/workflow/status/log1x/acorn-disable-media-pages/main.yml?branch=main&style=flat-square)

Disable Media Pages is an [Acorn](https://github.com/roots/acorn) package for WordPress that disables media attachment pages and generates unique UUID's for each media attachment's `post_name` to prevent clashing.

WordPress 6.4 will bring the ability to ["disable" attachment pages](https://make.wordpress.org/core/2023/10/16/changes-to-attachment-pages/) but attachments will still generate unnecessary slugs based on the uploaded media's filename.

## Features

- Replaces media attachment `post_name` slugs with randomly generated UUID's with [`Str::uuid()`](https://laravel.com/docs/10.x/strings#method-str-uuid).
- Replaces the "View" media URL with a direct link to the media.
- 404's any requests made to media attachment page slugs.
- Easily convert/revert existing media page slugs using Acorn's CLI.

## Requirements

- [PHP](https://secure.php.net/manual/en/install.php) >= 8.1
- [Acorn](https://github.com/roots/acorn) >= 3.0

## Installation

Install via Composer:

```bash
$ composer require log1x/acorn-disable-media-pages
```

## Usage

### Getting Started

This package has no configuration and will start working once installed.

### Existing Media

To convert existing media, simply use the following Acorn command:

```bash
$ wp acorn media:generate-slugs
```

### Reverting Slugs

Reverting your attachment `post_name` slugs may not restore them to 1:1 to what they were prior to conversion. This command simply re-generates the slugs for each media attachment based on the existing `post_title` falling back to the filename if the existing `post_title` is empty.

```bash
$ wp acorn media:generate-slugs --revert
```

## Bug Reports

If you discover a bug in Acorn Disable Media Pages, please [open an issue](https://github.com/log1x/acorn-disable-media-pages/issues).

## Contributing

Contributing whether it be through PRs, reporting an issue, or suggesting an idea is encouraged and appreciated.

## License

Acorn Disable Media Pages is provided under the [MIT License](LICENSE.md).
