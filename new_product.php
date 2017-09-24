<?php
/**
 * Script to handle a POST request for a new product, and save it to the database.
 * Output is sent by functions successOutput() and dieError(), as it is unclear whether this script
 * needs to provide JSON or HTML output.
 *
 * @author Michael Kowalenko <michael.kowalenko2@gmail.com>
 */

// Step 1: Validate input (preferably this would be done by specifying rules - including value ranges,
// and helper functions/libraries would contain reusable logic).

$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
// Assumption: name is required and must be 20 chars or less
if (!$name || strlen($name) > 20) {
    dieError('Name input invalid');
}

$description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
// Assumption: description is required and must be 100 chars or less
if (!$description || strlen($description) > 100) {
    dieError('Description input invalid');
}

$price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
// Assumption: price is required and must be greater or equal to 0.00
if (!$price || $price < 0) {
    dieError('Price input invalid');
}

// Step 2: Save input to the database - database configuration ideally specified in an environment or config file.
$DB_DETAILS = [
    'type'       => 'mysql',
    'host'       => 'localhost',
    'dbname'     => 'test_database',
    'dbusername' => 'username',
    'dbpassword' => 'password'
];

// Connect to the database:
$str = "{$DB_DETAILS['type']}:host={$DB_DETAILS['host']};dbname={$DB_DETAILS['dbname']}";
try {
    $connection = new PDO($str, $DB_DETAILS['dbusername'], $DB_DETAILS['dbpassword']);
} catch (PDOException $pe) {
    dieError('Database connection failed.');
}
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Throw an exception if an error occurs

// Insert new record using a prepared statement
// (assumption: the table contains an id field with auto-increment, and no transaction is required in this case)
$statement = $connection->prepare('INSERT INTO table_name (product_name, description, price) VALUES (?, ?, ?)');
try {
    $statement->execute([$name, $description, $price]);
    success('Data saved successfully', $connection->lastInsertId());
} catch (PDOException $pe) {
    dieError('Database insertion failed.');
}

/**
 * Output to display to the user once the save is complete...
 * @param string $message
 * @param int $id
 */
function successOutput($message, $id) {
    echo "$message Insert ID: {$id}";
}

/**
 * A function to provide consistent outputs in the case of an error
 * @param string $reason
 * @param int $code HTTP response code
 */
function dieError($reason, $code = 500) {
    http_response_code($code);
    echo "Error due to: {$reason}";
    die();
}