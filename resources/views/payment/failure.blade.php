<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            color: #dc3545;
            margin-bottom: 20px;
        }
        p {
            font-size: 1.1em;
            margin-bottom: 10px;
        }
        .order-details {
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .order-details p strong {
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Payment Failed!</h1>
        <p>Unfortunately, your payment could not be processed.</p>
        <p>Please try again or contact support.</p>

        @if(isset($order))
            <div class="order-details">
                <h2>Order Details</h2>
                <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
                <p><strong>Payment Status:</strong> {{ $order->payment_status }}</p>
                <p><strong>Total Amount:</strong> {{ $order->total_price }} {{ $order->currency->name ?? 'KWD' }}</p>
            </div>
        @endif

        <p style="margin-top: 30px;"><a href="/" style="color: #007bff; text-decoration: none;">Go to Homepage</a></p>
    </div>
</body>
</html>