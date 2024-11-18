<?php include 'db_connect.php'; ?>

<?php 
if (isset($_GET['id'])) {
    $qry = $conn->query("SELECT * FROM borrowers WHERE id=" . $_GET['id']);
    foreach ($qry->fetch_array() as $k => $val) {
        $$k = $val;
    }
}

// Generate unique borrower ID for new borrowers
$borrower_unique_id = isset($borrower_unique_id) ? $borrower_unique_id : 'BRW' . date('Ymd') . rand(1000, 9999);
?>

<div class="container-fluid">
    <div class="col-lg-12">
        <form id="manage-borrower">
            <input type="hidden" name="id" value="<?php echo isset($_GET['id']) ? $_GET['id'] : ''; ?>">
            
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="firstname">First Name</label>
                        <input name="firstname" id="firstname" class="form-control" required value="<?php echo isset($firstname) ? $firstname : ''; ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="lastname">Last Name</label>
                        <input name="lastname" id="lastname" class="form-control" required value="<?php echo isset($lastname) ? $lastname : ''; ?>">
                    </div>
                </div>
                <div class="col-md-4">
    <div class="form-group">
        <label for="date_created">Date Created</label>
        <input type="text" name="date_created" id="date_created" class="form-control" readonly value="<?php echo isset($date_created) ? $date_created : date('Y-m-d H:i:s'); ?>">
    </div>
</div>


            <div class="row form-group">
                <div class="col-md-6">
                    <label for="address">Address</label>
                    <textarea name="address" id="address" cols="30" rows="2" class="form-control" required><?php echo isset($address) ? $address : ''; ?></textarea>
                </div>
            </div>

            <div class="row form-group">
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="contact_no">Contact No</label>
                        <input type="text" class="form-control" name="contact_no" id="contact_no" required value="<?php echo isset($contact_no) ? $contact_no : ''; ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" name="email" id="email" required value="<?php echo isset($email) ? $email : ''; ?>">
                </div>
            </div>

            <div class="row form-group">
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="aadhaar">Aadhaar No</label>
                        <input type="text" class="form-control" name="aadhaar" id="aadhaar" required value="<?php echo isset($aadhaar) ? $aadhaar : ''; ?>">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="pan">PAN</label>
                        <input type="text" class="form-control" name="pan" id="pan" required value="<?php echo isset($pan) ? $pan : ''; ?>">
                    </div>
                </div>
            </div>

            <!-- Display Unique ID (Optional) -->
            <div class="row form-group">
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="borrower_unique_id">Borrower ID</label>
                        <input type="text" class="form-control" readonly value="<?php echo $borrower_unique_id; ?>">
                    </div>
                </div>

                <!-- Display Photo upload option -->
                <div class="col-md-5">
                    <label for="fileToUpload">Upload Photo</label>
                    <input type="file" class="form-control" name="fileToUpload" id="fileToUpload" accept="image/*">
                </div>
            </div>

            <!-- Display the existing photo (if exists) -->
            <?php if (isset($photo) && $photo != ''): ?>
                <div class="row form-group">
                    <div class="col-md-5">
                        <label>Current Photo</label><br>
                        <img src="uploads/<?php echo $photo; ?>" alt="Borrower Photo" style="width: 100px; height: 100px;">
                    </div>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary btn-block">Save</button>
        </form>
    </div>
</div>

<script>
$('#manage-borrower').submit(function(e){
    e.preventDefault()
    start_load()
    $.ajax({
        url: 'ajax.php?action=save_borrower',
        method: 'POST',
        data: new FormData(this),
        contentType: false,
        processData: false,
        success: function(resp) {
            if (resp == 1) {
                $('.modal').modal('hide');
                alert_toast("Borrower successfully added", 'success');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else if (resp == 2) {
                $('.modal').modal('hide');
                alert_toast("Borrower successfully updated", 'success');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                alert_toast("Error: " + resp, 'danger');  // Show server response
            }
        }
    });
});
</script>
