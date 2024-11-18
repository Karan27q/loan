<?php
ob_start();
include 'admin_class.php';
include 'db_connect.php';

$crud = new Action();
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

try {
    switch ($action) {
        case 'login':
            echo $crud->login();
            break;

        case 'login2':
            echo $crud->login2();
            break;

        case 'logout':
            echo $crud->logout();
            break;

        case 'logout2':
            echo $crud->logout2();
            break;

        case 'save_user':
            echo $crud->save_user();
            break;

        case 'delete_user':
            echo $crud->delete_user();
            break;

        case 'signup':
            echo $crud->signup();
            break;

        case 'save_settings':
            echo $crud->save_settings();
            break;

        case 'save_loan_type':
            echo $crud->save_loan_type();
            break;

        case 'delete_loan_type':
            echo $crud->delete_loan_type();
            break;

        case 'save_plan':
            echo $crud->save_plan();
            break;

        case 'delete_plan':
            echo $crud->delete_plan();
            break;

        case 'save_borrower':
            handle_save_borrower($conn);
            break;

        case 'delete_borrower':
            echo $crud->delete_borrower();
            break;

        case 'delete_loan':
            echo $crud->delete_loan();
            break;

        case 'save_payment':
            echo $crud->save_payment();
            break;

        case 'delete_payment':
            echo $crud->delete_payment();
            break;

        case 'save_file_charge':
            echo $crud->save_file_charge();
            break;

        case 'delete_file_charge':
            echo $crud->delete_file_charge();
            break;

        case 'save_expenditure':
            echo $crud->save_expenditure();
            break;

        case 'delete_expenditure':
            echo $crud->delete_expenditure();
            break;

        case 'get_loans':
            handle_get_loans($conn);
            break;

        case 'save_loan':
            handle_save_loan($conn);
            break;

        case 'get_borrower_id':
            handle_get_borrower_id($conn);
            break;

        case 'update_final_date':
            handle_update_final_date($conn);
            break;

        default:
            echo "Error: Invalid or missing action.";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
}

function handle_save_borrower($conn) {
    try {
        $id = $_POST['id'] ?? '';
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $address = $_POST['address'];
        $contact_no = $_POST['contact_no'];
        $email = $_POST['email'];
        $aadhaar = $_POST['aadhaar'];
        $pan = $_POST['pan'];
        $photo = '';

        if (!empty($_FILES['fileToUpload']['name'])) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $photo = $target_dir . basename($_FILES['fileToUpload']['name']);
            move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $photo);
        }

        if ($id) {
            $stmt = $conn->prepare("UPDATE borrowers SET firstname=?, lastname=?, address=?, contact_no=?, email=?, aadhaar=?, pan=?, photo=? WHERE id=?");
            $stmt->bind_param('ssssssssi', $firstname, $lastname, $address, $contact_no, $email, $aadhaar, $pan, $photo, $id);
        } else {
            $unique_borrower_id = '';
            do {
                $unique_borrower_id = 'B' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
                $check = $conn->query("SELECT id FROM borrowers WHERE unique_borrower_id = '$unique_borrower_id'");
            } while ($check->num_rows > 0);

            $date_created = date('Y-m-d H:i:s');
            $stmt = $conn->prepare("INSERT INTO borrowers (unique_borrower_id, firstname, lastname, date_created, address, contact_no, email, aadhaar, pan, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ssssssssss', $unique_borrower_id, $firstname, $lastname, $date_created, $address, $contact_no, $email, $aadhaar, $pan, $photo);
        }

        if ($stmt->execute()) {
            echo $id ? 2 : 1;
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } catch (Exception $e) {
        echo "Exception: " . $e->getMessage();
    }
}

function handle_save_loan($conn) {
    $unique_id = $_POST['unique_id'];
    $borrower_id = $_POST['borrower_id'];
    $loan_type_id = $_POST['loan_type_id'];
    $plan_id = $_POST['plan_id'];
    $amount = $_POST['amount'];

    if (empty($_POST['id'])) {
        $sql = "INSERT INTO loan_list (unique_id, borrower_id, loan_type_id, plan_id, amount, status) 
                VALUES ('$unique_id', '$borrower_id', '$loan_type_id', '$plan_id', '$amount', 0)";
    } else {
        $sql = "UPDATE loan_list SET 
                unique_id = '$unique_id',
                borrower_id = '$borrower_id',
                loan_type_id = '$loan_type_id',
                plan_id = '$plan_id',
                amount = '$amount'
                WHERE id = {$_POST['id']}";
    }

    $save = $conn->query($sql);
    echo $save ? 1 : "Error: " . $conn->error;
}
function handle_get_borrower_id($conn) {
    $borrower_id = intval($_POST['id']);
    $qry = $conn->query("SELECT unique_borrower_id FROM borrowers WHERE id = $borrower_id");
    echo $qry->num_rows > 0 ? $qry->fetch_assoc()['unique_borrower_id'] : '';
}

function handle_update_final_date($conn) {
    try {
        if (!isset($_POST['loan_id']) || !isset($_POST['final_date'])) {
            echo "Error: Missing loan_id or final_date.";
            return;
        }

        $loan_id = $_POST['loan_id'];
        $final_date = $_POST['final_date'];

        $stmt = $conn->prepare("UPDATE loan_list SET manual_final_date = ? WHERE id = ?");
        $stmt->bind_param("si", $final_date, $loan_id);

        echo $stmt->execute() ? 1 : "Error: " . $stmt->error;
        $stmt->close();
    } catch (Exception $e) {
        echo "Exception: " . $e->getMessage();
    }
}

function handle_get_loans($conn) {
    $qry = $conn->query("SELECT l.*, b.id AS borrower_id, CONCAT(b.firstname, ' ', b.lastname) AS name FROM loan_list l INNER JOIN borrowers b ON b.id = l.borrower_id ORDER BY l.id ASC");
    $result = [];
    while ($row = $qry->fetch_assoc()) {
        $result[] = $row;
    }
    echo json_encode($result);
}

if (isset($_POST['action']) && $_POST['action'] == 'change_status') {
    $loan_id = $_POST['id'];
    $status = $_POST['status'];

    // Validate status value (0 to 4)
    if ($status >= 0 && $status <= 4) {
        $query = "UPDATE loan_list SET status = ? WHERE id = ?";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("ii", $status, $loan_id);
            if ($stmt->execute()) {
                echo 1;  // Success
            } else {
                echo 0;  // Failure
            }
            $stmt->close();
        } else {
            echo 0;  // Failure
        }
    } else {
        echo 0;  // Invalid status
    }
}



?>
