<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Management</title>
    <!-- Include your CSS and other dependencies here -->
    <link rel="stylesheet" href="path/to/your/css/bootstrap.min.css">
    <link rel="stylesheet" href="path/to/your/css/datatables.min.css">
    <style>
        .text-white { color: white !important; }
        /* Add any additional styles here */
    </style>
</head>
<body>
    <div class="container-fluid" style="margin-bottom: 58px;">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <large class="card-title">
                        <b>Loan List</b>
                        <button class="btn btn-light btn-sm btn-block col-md-2 float-right" type="button" id="new_application">
                            <i class="fa fa-plus"></i> New Loan Application
                        </button>
                    </large>
                </div>
                <div class="card-body" style="overflow-x:auto;">
                    <table class="table table-bordered" id="loan-list">
                        <colgroup>
                            <col width="5%">
                            <col width="10%">
                            <col width="17%">
                            <col width="32%">
                            <col width="15%">
                            <col width="15%">
                            <?php if ($_SESSION['login_type'] == 1) : ?>
                                <col width="15%">
                            <?php endif; ?>
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="text-center text-white">#</th>
                                <th class="text-center text-white">Borrower ID</th>
                                <th class="text-center text-white">Borrower</th>
                                <th class="text-center text-white">Loan Details</th>
                                <th class="text-center text-white">Dates</th>
                                <th class="text-center text-white">Status</th>
                                <?php if ($_SESSION['login_type'] == 1) : ?>
                                    <th class="text-center text-white">Action</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            $type_arr = [];
                            $plan_arr = [];

                            $type = $conn->query("SELECT * FROM loan_types WHERE id IN (SELECT loan_type_id FROM loan_list)");
                            while ($row = $type->fetch_assoc()) {
                                $type_arr[$row['id']] = $row['type_name'];
                            }

                            $plan = $conn->query("SELECT *, CONCAT(days, ' day/s [ ', interest_percentage, '%, ', penalty_rate, ' ]') AS plan FROM loan_plan WHERE id IN (SELECT plan_id FROM loan_list)");
                            while ($row = $plan->fetch_assoc()) {
                                $plan_arr[$row['id']] = $row;
                            }

                            $qry = $conn->query("SELECT l.*, b.id AS borrower_id, CONCAT(b.firstname, ' ', b.lastname) AS name, b.contact_no, b.email, b.address, b.aadhaar, b.pan FROM loan_list l INNER JOIN borrowers b ON b.id = l.borrower_id ORDER BY l.id ASC");
                            while ($row = $qry->fetch_assoc()) :
                                if (isset($plan_arr[$row['plan_id']])) {
                                    $daily = ($row['amount'] + ($row['amount'] * ($plan_arr[$row['plan_id']]['interest_percentage'] / 100))) / $plan_arr[$row['plan_id']]['days'];
                                    $days = $plan_arr[$row['plan_id']]['days'];
                                    $total = $daily * $days;
                                    $penalty = $daily * ($plan_arr[$row['plan_id']]['penalty_rate'] / 100);
                                } else {
                                    $daily = $total = $penalty = $days = 0;
                                }

                                $payments = $conn->query("SELECT * FROM payments WHERE loan_id =" . $row['id']);
                                $sum_paid = 0;

                                while ($p = $payments->fetch_assoc()) {
                                    $sum_paid += ($p['amount'] - $p['penalty_amount']);
                                }
                                $remain = $total - $sum_paid;

                                $file_charges = $conn->query("SELECT * FROM file_charges WHERE loan_id =" . $row['id']);
                                $file_charge = ($row['amount'] * 2 / 100);
                                while ($f = $file_charges->fetch_assoc()) {
                                    $file_charge -= $f['amount'];
                                }

                                // Calculate the remaining days for the loan
                                $date_created = new DateTime($row['date_created']);
                                $final_date = $date_created->modify('+' . $days . ' days');
                                $current_date = new DateTime();
                                $remaining_days = $final_date->diff($current_date)->format('%r%a'); // Calculate remaining days
                            ?>
                                <tr>
                                    <td class="text-center"><?php echo $i++ ?></td>
                                    <td class="text-center"><?php echo $row['borrower_id'] ?></td>
                                    <td>
                                        <p>Name: <b><?php echo $row['name'] ?></b></p>
                                        <p><small>Contact: <b><?php echo $row['contact_no'] ?></b></small></p>
                                        <p><small>Email: <b><?php echo $row['email'] ?></b></small></p>
                                    </td>
                                    <td>
                                        
                                        <p><small>Loan type: <b><?php echo $type_arr[$row['loan_type_id']] ?? 'Unknown'; ?></b></small></p>
                                        <p><small>Plan: <b><?php echo $plan_arr[$row['plan_id']]['plan'] ?? 'Unknown'; ?></b></small></p>
                                        <p><small>Amount: <b><?php echo $row['amount'] ?></b></small></p>
                                        <p><small>Total Payable: <b><?php echo number_format($total, 2) ?></b></small></p>
                                    </td>
                                    <td>
    <p><small>Date Created: <b><?php echo date("M d, Y", strtotime($row['date_created'])) ?></b></small></p>
    <p><small>Date Released: <b><?php echo $row['date_released'] ? date("M d, Y", strtotime($row['date_released'])) : 'N/A'; ?></b></small></p>
    <!-- Use the updated check for date_completed -->
    <p><small>Date Completed: <b><?php echo isset($row['date_completed']) ? date("M d, Y", strtotime($row['date_completed'])) : 'N/A'; ?></b></small></p>
    <p><small>Remaining Days: <b><?php echo ($remaining_days >= 0) ? $remaining_days : 'Overdue'; ?></b></small></p>
</td>

                                    <td class="text-center">
                                        <?php if ($row['status'] == 0) : ?>
                                            <span class="badge badge-warning">For Approval</span>
                                        <?php elseif ($row['status'] == 1) : ?>
                                            <span class="badge badge-info">Approved</span>
                                        <?php elseif ($row['status'] == 2) : ?>
                                            <span class="badge badge-primary">Released</span>
                                        <?php elseif ($row['status'] == 3) : ?>
                                            <span class="badge badge-success">Completed</span>
                                        <?php elseif ($row['status'] == 4) : ?>
                                            <span class="badge badge-danger">Denied</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if ($_SESSION['login_type'] == 1) : ?>
                                        <td class="text-center">
    <button class="btn btn-outline-primary btn-sm edit_loan" type="button" data-id="<?php echo $row['id'] ?>">Edit</button>
    <button class="btn btn-outline-danger btn-sm delete_loan" type="button" data-id="<?php echo $row['id'] ?>">Delete</button>
</td>

                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="path/to/your/js/jquery.min.js"></script>
    <script src="path/to/your/js/bootstrap.min.js"></script>
    <script src="path/to/your/js/datatables.min.js"></script>
    <script src="path/to/your/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            // Initialize DataTable
            $('#loan-list').DataTable();

            // Handle new loan application button
            $('#new_application').on('click', function () {
                uni_modal('New Loan Application', 'manage_loan.php', 'mid-large');
            });

            // Use delegated event handler for dynamic elements
            $('#loan-list').on('click', '.edit_loan', function () {
                var id = $(this).attr('data-id');
                uni_modal('Edit Loan Application', 'manage_loan.php?id=' + id, 'mid-large');
            });

            $('#loan-list').on('click', '.edit_final_date', function () {
                var id = $(this).attr('data-id');
                uni_modal('Edit Final Date', 'edit_final_date.php?id=' + id, 'mid-large');
            });

            $('#loan-list').on('click', '.delete_loan', function () {
                var id = $(this).attr('data-id');
                _conf("Are you sure to delete this loan?", "delete_loan", [id]);
            });
        });

        function delete_loan(id) {
            start_load();
            $.ajax({
                url: 'ajax.php?action=delete_loan',
                method: 'POST',
                data: { id: id },
                success: function (resp) {
                    if (resp == 1) {
                        alert_toast("Loan deleted successfully", 'success');
                        setTimeout(function () {
                            location.reload();
                        }, 1500);
                    }
                }
            });
        }
    </script>
</body>
</html>
