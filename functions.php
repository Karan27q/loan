<?php
include 'db_connect.php';

// Function to save or update borrower
function save_borrower($data, $files) {
    global $conn;

    $id = $data['id'] ?? '';
    $firstname = $data['firstname'];
    $lastname = $data['lastname'];
    $date_created = $data['date_created'] ?? date('Y-m-d H:i:s'); // Defaults to current timestamp if not provided
    $address = $data['address'];
    $contact_no = $data['contact_no'];
    $email = $data['email'];
    $aadhaar = $data['aadhaar'];
    $pan = $data['pan'];
    $photo = $files['fileToUpload']['name'] ?? ''; // Handle photo upload

    if (!empty($photo)) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($photo);
        if (!move_uploaded_file($files['fileToUpload']['tmp_name'], $target_file)) {
            return "Error: Failed to upload photo.";
        }
    }

    if ($id) {
        $stmt = $conn->prepare("UPDATE borrowers SET firstname=?, lastname=?, address=?, contact_no=?, email=?, aadhaar=?, pan=?, photo=? WHERE id=?");
        $stmt->bind_param('ssssssssi', $firstname, $lastname, $address, $contact_no, $email, $aadhaar, $pan, $photo, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO borrowers (firstname, lastname, date_created, address, contact_no, email, aadhaar, pan, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssssssss', $firstname, $lastname, $date_created, $address, $contact_no, $email, $aadhaar, $pan, $photo);
    }

    if ($stmt->execute()) {
        return $id ? 2 : 1; // Return 2 for update, 1 for insert
    } else {
        return "Error: " . $stmt->error;
    }
}

// Function to delete borrower
function delete_borrower($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM borrowers WHERE id=?");
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        return 1; // Success
    } else {
        return "Error: " . $stmt->error;
    }
}

// Function to get borrower details
function get_borrower($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM borrowers WHERE id=?");
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    } else {
        return "Error: " . $stmt->error;
    }
}

// Function to get all borrowers
function get_all_borrowers() {
    global $conn;
    $result = $conn->query("SELECT * FROM borrowers");
    $borrowers = [];

    while ($row = $result->fetch_assoc()) {
        $borrowers[] = $row;
    }

    return $borrowers;
}

// Function to save a user (example implementation)
function save_user($data) {
    global $conn;
    $id = $data['id'] ?? '';
    $username = $data['username'];
    $password = password_hash($data['password'], PASSWORD_DEFAULT);

    if ($id) {
        $stmt = $conn->prepare("UPDATE users SET username=?, password=? WHERE id=?");
        $stmt->bind_param('ssi', $username, $password, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param('ss', $username, $password);
    }

    if ($stmt->execute()) {
        return $id ? 2 : 1; // Return 2 for update, 1 for insert
    } else {
        return "Error: " . $stmt->error;
    }
}

// Function to delete a user
function delete_user($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        return 1; // Success
    } else {
        return "Error: " . $stmt->error;
    }
}

// Function to handle login
function login($data) {
    global $conn;
    $username = $data['username'];
    $password = $data['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['login_id'] = $user['id'];
        return 1;
    } else {
        return "Invalid username or password.";
    }
}

// Function to handle logout
function logout() {
    session_start();
    session_destroy();
    return 1; // Success
}
?>
