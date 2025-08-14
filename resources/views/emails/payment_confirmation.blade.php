<!DOCTYPE html>
<html>
<head>
    <title>Payment Confirmation</title>
</head>
<body>
    <h1>Payment Confirmation</h1>
    <p>Dear {{ $loan->customer->name }},</p>
    <p>Your payment of <strong>${{ number_format($payment->amount, 2) }}</strong> 
    for Loan #{{ $loan->id }} has been received.</p>
    <p>The original loan amount was <strong>${{ number_format($loan->amount, 2) }}</strong>.</p>
    <p>Thank you for your payment.</p>
</body>
</html>
