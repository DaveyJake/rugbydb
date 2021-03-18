# The OFFICIAL Theme for Rugby Database

Installation Steps In Order
---------------------------

### 1. Requirements

To add to the development of this theme, please install the following:

- [Node.js](https://nodejs.org/)
- [Composer](https://getcomposer.org/)

### 2. Local Quick Start

Clone this repository to your local environment and create a new branch describing a feature (like, say, `search-result-tiles`).


### 3. Setup Theme Environment

To start developing on this theme, you'll need to install the necessary Node.js and Composer dependencies.

Open your CLI (e.g. _Terminal_; _PuTTY_; _Command Prompt_) and go your project directory (i.e. `cd /path/to/RDB`).

Run the following commands:

```sh
$ composer install
$ npm install
```

### Available CLI commands

`RDB` comes packed with CLI commands tailored for WordPress theme development :

- `composer lint:wpcs` : checks all PHP files against [PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/).
- `composer lint:php` : checks all PHP files for syntax errors.
- `composer make-pot` : generates a .pot file in the `language/` directory.
- `npm run watch` : watches all SASS files and recompiles them to css when they change.
- `npm run compile:css` : compiles SASS files to css.
- `npm run lint:scss` : checks all SASS files against [CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/).
- `npm run lint:js` : checks all JavaScript files against [JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/).

### Changelog

* 2020-07-16: Initial commit.
* 2020-07-21: Perfect setup with 0 linter & compiler errors.
* 2020-07-21: Optimized all `content-{$post_type}.php` file output; new `theme-functions.php` file.
