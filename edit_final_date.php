<?php
include 'db_connect.php';
$id = $_GET['id'];
$qry = $conn->query("SELECT manual_final_date FROM loan_list WHERE id = $id")->fetch_assoc();
$final_date = $qry['manual_final_date'] ?? '';
?>

<form method="POST" action="ajax.php?action=update_final_date">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <div class="form-group">
        <label for="final_date">Final Date</label>
        <input type="date" name="final_date" value="<?php echo $final_date; ?>" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Save</button>
</form>


<script>
    $(document).ready(function() {
    $('#finalDateForm').submit(function(e) {
        e.preventDefault(); // Prevent default form submission

    // Ensure these variables are populated correctly with form values before sending the request
var loan_id = $('#loan_id').val();  // Ensure this is the correct field (hidden or input)
var final_date = $('#final_date').val();  // Ensure this is the correct field (text or date input)

// Check if values are retrieved correctly (debugging)
console.log('Loan ID:', loan_id);
console.log('Final Date:', final_date);

$.ajax({
    url: 'ajax.php',
    method: 'POST',
    data: {
        action: 'update_final_date', // Action name
        loan_id: loan_id,  // Pass loan_id
        final_date: final_date  // Pass final_date
    },
    success: function(response) {
        console.log(response); // Check the response in the console
        if (response == 1) {
            alert('Final Date updated successfully!');
        } else {
            alert('Failed to update Final Date.');
        }
    },
    error: function(xhr, status, error) {
        console.log("AJAX Error: " + error);
    }
});

    });
    });

</script>