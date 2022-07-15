Overview
========
This module provides a service that parses the email content for airline reservations info.

It also contains a form and some default email content with which to demo the service.

Installation
========

The service relies on a PHP email parsing library: php-email-mime-parser. If this were a packaged module we could include it in a composer file and have it
install automatically. But because this is just a low-key demo, make sure you run this command before you enable the module:

```
composer require php-mime-mail-parser/php-mime-mail-parser
```

Configuration
========

IRL, we would have a different method of obtaining the .eml files. For example,
we could open an IMAP stream to an inbox and get unopened files with a particular subject,
for example. But that is out of the scope of this module. For the purposes of the demo,
add .eml files to the /email directory of this module (there are two provided by default).
If you would like to use a different directory, you can specify the directory in the config file provided.


Demo
========

Go to /get-reservations and click on the 'Get New Reservations' button to see the reservation data
for every file in the /email directory.
