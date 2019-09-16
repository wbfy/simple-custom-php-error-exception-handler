<?php
/***
 * Example usage and tests
 */
include 'ExceptionErrorHandler.class.php';

// Add your email address to send test email
$test_email_to = '';

// Initialise exception/error handler
ExceptionErrorHandler::register($test_email_to);

// TESTS
// Comment out as appropriate

// Test exception handler
throw new Exception('Test exception handler');

// Test error handler
this_is_not_a_function();
