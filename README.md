# A Simple Custom PHP Error/Exception Handler
## Version 1.0.0
## Synopsis
This is a simple PHP error/exception handler. It can deal with shutdown (fatel) errors, standard errors and exceptions.

It uses only standard PHP functionality with no calls to external libraries or other code. This helps to ensure no unforeseen errors actually within the error handler which of course can present problems!

If an error happens then it can do three things:

1. Log the error to the standard PHP error_log;
1. Send a notification email to your email address;
1. Display the error honouring the PHP display_errors flag to hide details in production.

It’s also smart enough to distinguish whether its running under PHP CLI or HTTP mode and format the display accordingly.

## Usage
```PHP
include 'ExceptionErrorHandler.class.php';              // Include class

$error_email_to = '';                                   // Leave blank for no email
ExceptionErrorHandler::register($error_email_to);       // initialise class
```
See index.php for more details on usage

## Testing
See index.php for some examples of exceptions and errors

## Contributors
Steve Perkins

## Licence
The class is licensed under the MIT licence.

Copyright (c) 2012 Steve Perkins, [Websites Built For You](https://websitesbuiltforyou.com)
