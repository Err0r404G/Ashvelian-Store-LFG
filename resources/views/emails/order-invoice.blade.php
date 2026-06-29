<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $order->order_number }} Invoice</title>
</head>
<body style="font-family: Arial, sans-serif; color:#111; line-height:1.5;">
    <h1>Ashvalian Invoice</h1>
    <p>Thank you for your order, {{ $order->customer_name }}.</p>
    <p><strong>Order:</strong> {{ $order->order_number }}</p>
    <p><strong>Payment:</strong> {{ strtoupper($order->payment_method) }} / {{ ucfirst($order->payment_status) }}</p>
    <p><strong>Shipping address:</strong> {{ $order->shipping_address }}</p>

    <table width="100%" cellpadding="8" cellspacing="0" border="1" style="border-collapse:collapse;">
        <thead>
            <tr>
                <th align="left">Product</th>
                <th align="right">Qty</th>
                <th align="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td align="right">{{ $item->quantity }}</td>
                    <td align="right">৳{{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p><strong>Subtotal:</strong> ৳{{ number_format($order->subtotal, 2) }}</p>
    <p><strong>Discount:</strong> ৳{{ number_format($order->discount_total, 2) }}</p>
    <p><strong>Shipping:</strong> ৳{{ number_format($order->shipping_total, 2) }}</p>
    <p><strong>Tax:</strong> ৳{{ number_format($order->tax_total, 2) }}</p>
    <h2>Total: ৳{{ number_format($order->grand_total, 2) }}</h2>
</body>
</html>
