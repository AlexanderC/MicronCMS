# MicronCMS
MicronCMS- a drop in micro CMS for minimalist websites

# Main Goal
The goal was to build extendable, small and reliable CMS for microsites
that follows best practices and is a drop in replacement for an old CMS.

User build his own structure and CMS would take care of anything else...

# Requirements
 - PHP >=5.5.2
 
# Supported files
 - .html
 - .txt
 - .json
 - .md (extended markdown using `michelf/php-markdown`)
 
# Usage
 - Download latest snapshot from `https://github.com/AlexanderC/MicronCMS/raw/master/snapshots/1429171587_master.zip`
 - Unzip it in you web directory (ex. /var/www)
 - Create you own or overwrite pages in `_content` directory
 - That's it!
 
How it works: 
 
> By accessing `www.yoursite.com/` - CMS would look for an `_content/_index.*` file

> By accessing `www.yoursite.com/whatever` - CMS would look for an `_content/whatever.*` file

> By accessing `www.yoursite.com/nested/whatever` - CMS would look for an `_content/nested/whatever.*` file

> On `404` error - CMS would look for an `_content/_404.*` file

> On `500` error - CMS would look for an `_content/_500.*` file
 
# Development
 - Clone from github
 - Run `composer install`
 - Thats it!
 
# Compilation
In order to compile run `./bin/compile` (you may change `DEBUG` flag for debugging purposes)

# TODO
 - Add more servers support
 - Add tests
 - Advanced templates pre processing
 - More flexible templates