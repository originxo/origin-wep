<?php


// =====================
// Change values below:
// =====================

$domain_name = "yoursitename.com"; // Change value to your domain name (without http://, https://, or www).
$admin_email  = "your@email.com"; // Change value to your valid email address (where a message will be sent).
$form_subject = "Message from yoursitename.com"; // Change value to your own message subject.




// ================================
// DO NOT CHANGE ANYTHING BELOW!!!
// ================================

$method = $_SERVER['REQUEST_METHOD'];

// Initialize the message variable
$message = "";

// Check request method
if ($method === 'POST') {

    // Validate admin email (recipient)
    if (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Invalid recipient email."]);
        exit();
    }

    // Build message table
    $c = true;
    foreach ($_POST as $key => $value) {
        if (!empty($value) && !in_array($key, ["project_name", "admin_email", "form_subject"])) {
            $key = ucwords(str_replace("_", " ", $key));  // Replace underscores with spaces and capitalize the first letter of each word
            $value = nl2br(htmlspecialchars($value, ENT_QUOTES, 'UTF-8')); // Prevent XSS
            $message .= "
            " . (($c = !$c) ? '<tr>' : '<tr style="background-color: #f3f3f3;">') . "
            <td style='padding: 10px; border: #e9e9e9 1px solid; width: 100px; vertical-align: top;'><strong>$key:</strong></td>
            <td style='padding: 10px; border: #e9e9e9 1px solid;'>$value</td>
            </tr>";
        }
    }
    $message = "<table style='width: 100%;'>$message</table>";

    // Get sender email (fallback to domain-based email). 
    $domain = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
    if (empty($domain) || !filter_var($domain, FILTER_VALIDATE_DOMAIN)) {
        echo json_encode(["success" => false, "message" => "Invalid domain."]);
        exit();
    }
    // Note: 'Email' is the input name. Make sure it is correct (case sensitive)!
    $sender_email = isset($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)
        ? $_POST['email']
        : "noreply@$domain";

    // Set email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: $domain_name <no-reply@$domain_name>" . "\r\n";
    $headers .= "Reply-To: $sender_email" . "\r\n";

    // Encode subject to avoid issues with special characters
    $encoded_subject = '=?UTF-8?B?' . base64_encode($form_subject) . '?=';

    // Send email
    if (mail($admin_email, $encoded_subject, $message, $headers)) {
        echo json_encode(["success" => true, "message" => "Thank you! Email sent successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Sorry, email could not be sent."]);
    }
    exit();
}

// If not a POST request, return error
echo json_encode(["success" => false, "message" => "Invalid request method."]);
exit();
