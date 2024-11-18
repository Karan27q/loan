<?php
include 'db_connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0; // Validate and sanitize input

if ($id > 0) {
    $qry = $conn->query("SELECT * FROM loan_list WHERE id = $id");

    if ($qry && $qry->num_rows > 0) {
        foreach ($qry->fetch_assoc() as $k => $v) {
            $$k = $v; // Dynamically create variables
        }
    } else {
        echo "No record found or query failed: " . $conn->error;
    }
} else {
    echo "Invalid or missing loan ID.";
}
?>

<div class="container-fluid">
    <form action="" id="manage-loan">
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">

        <!-- Borrower Selection -->
        <div class="form-group">
            <label for="borrower_id" class="control-label">Borrower</label>
            <select name="borrower_id" id="borrower_id" class="form-control" required>
                <option value="" disabled selected>Select Borrower</option>
                <?php
                $borrowers = $conn->query("SELECT id, CONCAT(firstname, ' ', lastname) AS name FROM borrowers");
                while ($borrowers && $row = $borrowers->fetch_assoc()) : ?>
                    <option value="<?php echo $row['id'] ?>" 
                        <?php echo isset($borrower_id) && $borrower_id == $row['id'] ? 'selected' : '' ?>>
                        <?php echo $row['name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Unique Borrower ID Display -->
        <div class="form-group">
            <label for="unique_borrower_id" class="control-label">Borrower ID</label>
            <input type="text" class="form-control" id="unique_borrower_id" name="unique_borrower_id" value="" readonly>
        </div>

        <!-- Loan Type Selection -->
        <div class="form-group">
            <label for="loan_type_id" class="control-label">Loan Type</label>
            <select name="loan_type_id" id="loan_type_id" class="form-control" required>
                <option value="" disabled selected>Select Loan Type</option>
                <?php
                $loan_types = $conn->query("SELECT * FROM loan_types");
                while ($loan_types && $row = $loan_types->fetch_assoc()) : ?>
                    <option value="<?php echo $row['id'] ?>" 
                        <?php echo isset($loan_type_id) && $loan_type_id == $row['id'] ? 'selected' : '' ?>>
                        <?php echo $row['type_name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Loan Plan Selection -->
        <div class="form-group">
            <label for="plan_id" class="control-label">Loan Plan</label>
            <select name="plan_id" id="plan_id" class="form-control" required>
                <option value="" disabled selected>Select Loan Plan</option>
                <?php
                $plans = $conn->query("SELECT id, CONCAT(days, ' day/s [', interest_percentage, '%, ', penalty_rate, '%]') AS plan FROM loan_plan");
                while ($plans && $row = $plans->fetch_assoc()) : ?>
                    <option value="<?php echo $row['id'] ?>" 
                        <?php echo isset($plan_id) && $plan_id == $row['id'] ? 'selected' : '' ?>>
                        <?php echo $row['plan'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Loan Amount -->
        <div class="form-group">
            <label for="amount" class="control-label">Loan Amount</label>
            <input type="number" class="form-control" name="amount" value="<?php echo isset($amount) ? $amount : '' ?>" required>
        </div>

        <!-- Reference Number -->
        <div class="form-group">
            <label for="ref_no" class="control-label">Reference No.</label>
            <input type="text" class="form-control" name="ref_no" value="<?php echo isset($ref_no) ? $ref_no : '' ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>

    <!-- Loan Status -->
    <?php if(isset($status)): ?>
        <div class="row">
            <div class="form-group col-md-6">
                <label class="control-label">&nbsp;</label>
                <select class="custom-select browser-default" name="status">
                    <?php if($status !='2' && $status !='3' && $status !='4' ): ?>
                    <option value="0" <?php echo $status == 0 ? "selected" : '' ?>>For Approval</option>
                    <option value="1" <?php echo $status == 1 ? "selected" : '' ?>>Approved</option>
                    <?php endif ?>
                    <?php if($status !='4' ): ?>
                    <option value="2" <?php echo $status == 2 ? "selected" : '' ?>>Released</option>
                    <?php endif ?>
                    <?php if($status =='2' || $status =='3' ): ?>
                    <option value="3" <?php echo $status == 3 ? "selected" : '' ?>>Completed</option>
                    <?php endif ?>
                    <?php if($status != '1' && $status != '2' && $status !='3' ): ?>
                    <option value="4" <?php echo $status == 4 ? "selected" : '' ?>>Denied</option>
                    <?php endif ?>
                </select>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Load Borrower Unique ID on Selection
    $('#borrower_id').change(function() {
        var borrower_id = $(this).val();

        if (borrower_id) {
            $.ajax({
                url: 'ajax.php?action=get_borrower_id',
                method: 'POST',
                data: { id: borrower_id },
                success: function(resp) {
                    $('#unique_borrower_id').val(resp ? resp : 'No ID Found');
                },
                error: function() {
                    alert('Error fetching borrower ID.');
                }
            });
        } else {
            $('#unique_borrower_id').val('');
        }
    });

    // Handle Loan Form Submission
    $('#manage-loan').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: 'ajax.php',
            method: 'POST',
            data: $(this).serialize() + '&action=save_loan',
            success: function(resp) {
                if (resp == 1) {
                    alert_toast("Loan Data Successfully Saved", 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    alert_toast("An Error Occurred: " + resp, 'error'); // Display SQL error
                }
            }
        });
    });
});

$(document).ready(function() {
    $('.delete-loan').click(function() {
        var id = $(this).data('id');
        if (confirm('Are you sure you want to delete this loan?')) {
            $.ajax({
                url: 'ajax.php?action=delete_loan',
                method: 'POST',
                data: { id: id },
                success: function(resp) {
                    if (resp == 1) {
                        alert_toast("Loan record successfully deleted", 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        alert_toast("An error occurred: " + resp, 'error');
                    }
                }
            });
        }
    });
});


</script>
