<?php 
extract($_POST);

// Validate inputs
if (isset($amount) && isset($interest) && isset($days) && isset($penalty)) {

    // Calculate the total amount including interest
    $total_with_interest = $amount + ($amount * ($interest / 100));

    // Calculate the per day amount (divide by number of days)
    $daily_amount = $total_with_interest / $days;

    // Format the daily amount to 2 decimal places
    $daily_amount = sprintf('%0.2f', $daily_amount);

    // Calculate the penalty amount based on the daily amount
    $penalty_amount = ($daily_amount * $penalty) / 100;
    $penalty_amount = sprintf('%0.2f', $penalty_amount);

    // Calculate the total payable amount including penalties
    $total_payable_with_penalty = $daily_amount * $days + $penalty_amount;

    // Display the results
    echo "<h4>Loan Calculation:</h4>";
    echo "<p><strong>Daily Payable Amount:</strong> ₹" . $daily_amount . "</p>";
    echo "<p><strong>Penalty Amount (Per Day):</strong> ₹" . $penalty_amount . "</p>";

    // Hidden input field to pass daily amount for future form submissions
    echo '<input type="hidden" name="daily_amount" class="form-control text-right" step="any" value="' . $daily_amount . '" required>';

    echo '<hr>';

    // Display the summary table
    echo '<table width="100%">
            <tr>
                <th class="text-center" width="33.33%">Total Payable Amount</th>
                <th class="text-center" width="33.33%">Daily Payable Amount</th>
                <th class="text-center" width="33.33%">Penalty Amount</th>
            </tr>
            <tr>
                <td class="text-center"><small>₹' . number_format($daily_amount * $days, 2) . '</small></td>
                <td class="text-center"><small>₹' . number_format($daily_amount, 2) . '</small></td>
                <td class="text-center"><small>₹' . number_format($penalty_amount, 2) . '</small></td>
            </tr>
        </table>';

    echo '<hr>';
} else {
    // Error handling for missing data
    echo "<p style='color:red;'>Please provide all the required data (amount, interest, days, penalty).</p>";
}
?>
