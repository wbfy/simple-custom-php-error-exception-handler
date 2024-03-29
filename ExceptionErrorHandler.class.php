<?php
/*
 * Copyright 2017 Websites Built For You
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in the
 * Software without restriction, including without limitation the rights to use, copy,
 * modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so, subject to the
 * following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE
 * USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
class ExceptionErrorHandler
{
    // Email error notifications sender and recipient
    // Leave blank for no notification emails
    private static $email_to   = '';
    private static $email_from = '';

    // Register handlers
    public static function register($email_to = '', $email_from = '')
    {
        register_shutdown_function([__CLASS__, 'shutdown']);
        set_error_handler([__CLASS__, 'errorHandler']);
        set_exception_handler([__CLASS__, 'exceptionHandler']);
        self::setEmailFrom($email_from);
        self::setEmailTo($email_to);
    }

    // Recipient for error notification emails
    // Also set sender to same as recipient if not already set
    public static function setEmailTo($email)
    {
        self::$email_to = $email;
        if (empty(self::$email_from)) {
            self::$email_from = self::$email_to;
        }
    }

    // Sender for error notification emails
    public static function setEmailFrom($email)
    {
        self::$email_from = $email;
    }

    // Shutdown (fatal) error handler
    public static function shutdown()
    {
        $error = error_get_last();
        if (!is_null($error) && $error['type'] === E_ERROR) {
            $details = 'shutdownHandler: ' . PHP_EOL . print_r($error, 1) . PHP_EOL;
            $details .= '-----' . PHP_EOL;
            $details .= '$_GET' . PHP_EOL . print_r($_GET, 1) . '$_POST' . PHP_EOL . print_r($_POST, 1) . '$_SERVER' . PHP_EOL . print_r($_SERVER, 1);
            if (strpos($error['message'], 'Uncaught yafw_Exceptions') === false) {
                self::log($details);
                self::show($details);
                self::send('shutdownHandler: ' . $error['message'], $details);
            }
        }
    }

    // Standard error handler
    public static function errorHandler($err_code, $err_msg, $err_file, $err_line, $err_context)
    {
        if (!(error_reporting() & $err_code)) {
            // This error code is not included in error_reporting, so let it fall
            // through to the standard PHP error handler
            return false;
        }
        switch ($err_code) {
            case E_USER_WARNING:
            case E_USER_NOTICE:
                // Log and send error notification
                $details = $err_file . ': [' . $err_code . '] ' . $err_msg . PHP_EOL;
                self::log($details);
                self::send('errorHandler: ' . $errmsg, $details);
                // Ignore the error and continue
                return true;
            case E_USER_ERROR:
            default:
                // Throw the error as an exception to be handled by the exception error handler
                throw new ErrorException($err_msg, 0, $err_code, $err_file, $err_line);
                break;
        }
    }

    // Exception error handler
    public static function exceptionHandler($e)
    {
        $details = get_class($e) . ': [' . $e->getCode() . '] ' . $e->getMessage() . PHP_EOL;
        $details .= $e->getTraceAsString() . PHP_EOL;
        $details .= '-----' . PHP_EOL;
        $details .= '$_GET' . PHP_EOL . print_r($_GET, 1) . '$_POST' . PHP_EOL . print_r($_POST, 1) . '$_SERVER' . PHP_EOL . print_r($_SERVER, 1);

        self::log($details);
        self::show($details);
        self::send('exceptionHandler: ' . $e->getMessage(), $details);
    }

    // Log the error to the standard PHP error log
    private static function log($details)
    {
        error_log($details);
    }

    // Notify the error by email
    // Uses basic PHP mail() function
    private static function send($subject, $body)
    {
        if (!empty(self::$email_to)) {
            $headers = '';
            $headers = 'From: ' . self::$email_from;

            $sent = false;
            try {
                $sent = mail(self::$email_to, $subject, nl2br(htmlentities($body)), $headers);
            } catch (exception $e) {
                $sent = false;
            }
            // Can't send email
            if (!$sent) {
                error_log('Error email notification could not be sent');
            }
        }
    }

    // Display an error message
    // Honours the display_errors setting in php.ini when showing the full details
    private static function show($details)
    {
        ob_clean();

        if (php_sapi_name() != "cli") {
            // HTML error output
            if (!headers_sent()) {
                header('HTTP/1.1 500 Internal Server Error');
            }
            $date    = date(DATE_RFC2822);
            $details = nl2br(htmlentities($details));

            $debug = '';
            if (ini_get('display_errors')) {
                $debug = '<div style="border:1px solid red;padding:1em;margin-top:1em;max-width:1000px;font-family:arial,sans-serif;background-color:white">';
                $debug .= '<p><b>Debug:</b></p><pre>' . $details . '</pre>';
                $debug .= '</div>';
            }

            echo <<< END
                <!DOCTYPE html>
                <head>
                    <title>Oops!</title>
                </head>
                <body style="background-color:#E2E2E2">
                    <div style="padding:1em;margin-top:2em;max-width:450px;font-family:arial,sans-serif;background-color:white;margin-left:auto;margin-right:auto">
                        <h1>
                            Oops!
                        </h1>
                        <p>
                            Sorry but an error has occured in the application.
                        </p>
                        <p>
                            The problem has been reported and will be resolved soon.
                        </p>
                        <p>
                            Please try again later.
                        </p>
                        <hr>
                        <p style="font-size:80%">
                            $date (ERR)
                        </p>
                    </div>
                    $debug
                </body>
END;
        } else {
            // CLI error so text only error output
            echo 'Oops! Sorry but an error has occured in the application. The problem has been reported and well be resolved soon. Please try again later.' . PHP_EOL;
            if (ini_get('display_errors')) {
                echo PHP_EOL . $details . PHP_EOL;
            }
        }

        ob_flush();
    }
}
