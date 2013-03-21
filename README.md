jettmail for Elgg
==================
>A robust mail framework for the elgg platform

Jett Mail takes elgg email notifications to the next level, allowing deep integration with email clients.

 - plaintext / html support
 - mail process forking
 - daily digest capability
 - reply to notifications directly from email
 - status updates from email
 - beautiful email template

**Please note this code is in alpha and is currently being developed. Use at your own risk.**

## Server requirements
 - Elgg 1.8.3 or greater
 - Unix-based
 - PHP 5.3 or greater
 - PHP MailParse extention
 - Ability to forward incoming emails to PHP scripts. *see below*

## Server Mail Configuration (Unix-based)
 1. Configure the server to forward email to the JettMail terminal plugin script. As a side note, most hosts provide control panel that you can do this easily. If you do not have access to such feature then edit `/etc/aliases` and add the following line

```
elggmail: "|/usr/bin/php -q /full/path/to/elgg/mod/jettmail/terminal/handle_email.php"
```
> Afterwards type `newaliases` to rebuild the email aliases.


 2. Create a symbolic link in `/etc/smrsh` so sendmail will know about our script. *If you don't do this then sendmail will spit out the wholly ugly error message Service unavailable, and smrsh: "php" not available for sendmail programs (stat failed).*
Run the following commands from the terminal:

``` 
$ cd /etc/smrsh
$ ln -s /usr/bin/php ./php
```

 3. Edit `/etc/mail/sendmail.mc` file and add the following to tell sendmail to route all messages to the elggmail account which are destined for a user which doesn't exist on our server.

```
define(`LUSER_RELAY',`local:elggmail')dnl
```


## PHP Configuration
Run the following commands from the terminal:
 1. ### Macports
```
$ sudo port install php5-mailparse
```
### Debian/Ubuntu/Linux Mint Installation
```
$ sudo apt-get install php5-mailparse
```

 2. Restart Apache 
```
$ apachectl restart
```

## Server Configuration (Windows-based)
*Not supported*

## Developer's guide
See the [developer's guide](https://github.com/jumbojett/jettmail/wiki/Developer%27s-Guide) on github.


