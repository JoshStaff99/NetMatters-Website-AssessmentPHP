<?php
require_once 'connection.php';

header('Content-Type: application/json');

// Initialize variables and error flags
$errors = [];
$hasErrors = false;

// Function to sanitize input
function clean_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Check if the form is submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty($_POST["name"])) {
        $errors['name'] = "Name is required";
        $hasErrors = true;
    } else {
        $name = clean_input($_POST["name"]);
    }

    // Validate email
    if (empty($_POST["email"])) {
        $errors['email'] = "Email is required";
        $hasErrors = true;
    } else {
        $email = clean_input($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Invalid email format";
            $hasErrors = true;
        }
    }

    // Validate telephone number
    if (empty($_POST["telephone"])) {
        $errors['telephone'] = "Telephone number is required";
        $hasErrors = true;
    } else {
        $telephone = clean_input($_POST["telephone"]);
        // Example validation for telephone (use the same regex you had)
        if (!preg_match('/^\+?[0-9]{0,3}[-\s\.]?\(?[0-9]{3}\)?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/', $telephone)) {
            $errors['telephone'] = "Invalid telephone number format";
            $hasErrors = true;
        }
    }

    // Handle marketing consent checkbox
    // Default to 0 (no)
    $marketingConsent = (isset($_POST['marketingConsent']) && $_POST['marketingConsent'] === 'yes') ? 'yes' : 'no';
    

    // If there are no errors, insert the data into the database
    if (!$hasErrors) {
    try {
        // Prepare SQL insert statement
        $stmt = $pdo->prepare("INSERT INTO contact_form (name, email, telephone, message, marketing_consent) VALUES (:name, :email, :telephone, :message, :marketing_consent)");


        // Bind parameters to the prepared statement
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telephone', $telephone);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':marketing_consent', $marketingConsent);

        // Execute the insert query
        $stmt->execute();

        // Return success response in JSON format
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        // Handle database error
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    } else {
        // If there are validation errors, return the errors in JSON format
        echo json_encode(['success' => false, 'errors' => $errors]);
    }
}