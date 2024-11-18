<?php include 'db_connect.php'; ?>

<?php
// Check if the form has been submitted
if (isset($_POST['submit'])) {
    // Get the form id
    $form_id = $_POST["id"];

    // Check if the file has been uploaded
    if (isset($_FILES['fileToUpload'])) {
        $errors = array();

        // Define the allowed file types
        $allowed_file_types = array('jpg', 'jpeg', 'png');

        $file_name = $_FILES['fileToUpload']['name'];

        // Get the temporary location of the uploaded file
        $file_tmp = $_FILES['fileToUpload']['tmp_name'];

        // Get the file extension
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

        // Check if the file type is allowed
        if (in_array($file_ext, $allowed_file_types) === false) {
            $errors[] = "File type not allowed. Please upload 'jpg' or 'jpeg' format files only.";
        }

        // Check if the file size is allowed
        if ($_FILES['fileToUpload']['size'] > 400000000) {
            $errors[] = "File size must be less than 200KB.";
        }

        // Ensure the uploads directory exists
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        // Check if there are any errors
        if (empty($errors)) {
            // Rename the photo to form id
            $new_name = $form_id . '.' . $file_ext;
            $upload_path = 'uploads/' . $new_name;

            // Move the uploaded file to the target directory
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Save the photo name to your SQL database
                $sql = "UPDATE borrowers SET photo = '$new_name' WHERE id = " . intval($form_id);
                if (mysqli_query($conn, $sql)) {
                    echo "Photo uploaded and database updated successfully.";
                    header('Location: index.php?page=borrowers');
                    exit;
                } else {
                    echo "Database update failed: " . mysqli_error($conn);
                }
            } else {
                echo "Failed to move uploaded file.";
            }
        } else {
            // Display error messages
            foreach ($errors as $error) {
                echo $error . "<br>";
            }
        }
    } else {
        echo "No file uploaded.";
    }
} else {
    echo "Form not submitted.";
}
?>
