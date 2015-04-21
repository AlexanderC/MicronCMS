# MicronCMS
MicronCMS- a drop in micro CMS for minimalist websites with zero configuration and no headache

# Main Goal
The goal was to build extendable, small and reliable CMS for microsites
that follows best practices and is a drop in replacement for an old CMS.

User creates and manages itself the pages in most convenience way and the MicronCMS would take care of anything else...

# Requirements
 - PHP >=5.5.2
 
# Supported files format
 - .html
 - .txt
 - .json
 - .md (extended markdown using `michelf/php-markdown`)
 
# Usage
 - Download [latest snapshot](https://github.com/AlexanderC/MicronCMS/raw/master/snapshots/1429619749_master.zip "Latest snapshot") from GitHub
 - Unzip it in you web directory (ex. /var/www)
 - Create you own or overwrite pages in `_content` directory
 - That's it!
 
### How it works: 
 
> By accessing `www.yoursite.com/` - CMS would look for an `_content/_index.*` file

> By accessing `www.yoursite.com/whatever` - CMS would look for an `_content/whatever.*` file

> By accessing `www.yoursite.com/nested/whatever` - CMS would look for an `_content/nested/whatever.*` file

> On `404` error - CMS would look for an `_content/_404.*` file

> On `500` error - CMS would look for an `_content/_500.*` file
 
### Folder structure
 - `index.php` compiled CMS itself
 - `.htaccess` magic happens here
 - `_content`
    - `_404.md` default `404 Not Found` page   
    - `_500.md` default `500 Internal Server Error` page
    - `_index.md` sample homepage
    - `_header.md` header sample included from `_index.md`
    - `_footer.md` Footer sample included from `_index.md`
 
# Development
### Installation
 - Clone from github
 - Run `composer install`
 
### Compilation
In order to compile run `./bin/compile` (you may change `DEBUG` flag for debugging purposes)

# Changelog
###v1.0.0beta
 - First release...

# TODO
 - Add more servers support
 - Add tests
 - Advanced templates pre processing
 - More flexible templates