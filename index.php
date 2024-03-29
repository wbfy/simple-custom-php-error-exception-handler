<?php
/***
 * Example usage and tests
 */

// Set to false to simulate production
ini_set('display_errors', true);

// Include the class
require 'ExceptionErrorHandler.class.php';

// Add your email address to send test email
$send_email_to = '';

// Initialise exception/error handler
ExceptionErrorHandler::register($send_email_to);

// TESTS
// Comment out as appropriate below

// Test exception handler
throw new Exception('Test exception handler');

// Test error handler
this_is_not_a_function();
