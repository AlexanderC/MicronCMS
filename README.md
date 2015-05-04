# MicronCMS
MicronCMS- a drop in micro CMS for minimalist websites with zero configuration and no headache

# Main Goal
The goal was to build extendable, small and reliable CMS for microsites
that follows best practices and is a drop in replacement for an old CMS.

User creates and manages itself the pages in most convenient way and the MicronCMS would take care of anything else...

# Requirements
 - PHP >=5.5.2
 
# Supported files format
 - .html
 - .txt
 - .json
 - .md (extended markdown using `michelf/php-markdown`)
 
# Usage
 - Download [latest snapshot](https://github.com/AlexanderC/MicronCMS/raw/master/snapshots/master.zip "Latest snapshot") from GitHub
 - Unzip it in you web directory (ex. /var/www)
 - Create you own or overwrite pages in `_content` directory
 - That's it!
 
### How it works
 
> By accessing `www.yoursite.com/` - CMS would look for an `_content/_index.*` file

> By accessing `www.yoursite.com/whatever` - CMS would look for an `_content/whatever.*` file

> By accessing `www.yoursite.com/nested/whatever` - CMS would look for an `_content/nested/whatever.*` file

> On `404` error - CMS would look for an `_content/_404.*` file

> On `500` error - CMS would look for an `_content/_500.*` file
 
### Managing content 

In order to upload documents of known formats you have to add 
`${include widgets/_upload.html}` code into any template.
This will trigger upload on file/s drop onto any page zone.

> In order to secure content management was added Google compatible OTP(One Time Password) functionality.

Here are the steps to follow in order to start uploading new documents:
 - Change default `micron_cms_auth_secret` environment variable from `.htaccess` file
 - Generate MD5 hash from newly added `micron_cms_auth_secret`
 - Install [Google OTP Client](https://www.google.com/search?rls=en&q=download+google+authenticator&ie=UTF-8&oe=UTF-8 "Google search") for your platform
 - Visit `www.yoursite.com/_/otp_setup?_secret_hash=%secret_hash%` page (replace `%secret_hash%` with generated MD5 hash)
 - Scan QR code using `Google Authenticator`
 - Test generated tokens on the same page to assure that it works
 
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

# TODO
 - [x] Advanced templates pre processing
 - [x] More flexible templates
 - [x] Add simple content management
 - [ ] Add more servers support
 - [ ] Add tests  