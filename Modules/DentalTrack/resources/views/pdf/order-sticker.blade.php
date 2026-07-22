<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Sticker</title>
    <style>
        body { margin: 0; padding: 0; font-family: sans-serif; }
        .sticker { width: 141.73px; height: 113.39px; padding: 4px; box-sizing: border-box; text-align: center; }
        .qr { width: 70px; height: 70px; }
        .label { font-size: 8px; color: #555; margin-top: 2px; }
        .id { font-size: 9px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="sticker">
        <img src="{{ $qrImage }}" class="qr" alt="QR">
        <div class="id">#{{ $order->id }}</div>
        <div class="label">{{ $order->patient_ref ?? '' }}</div>
        <div class="label">{{ $order->tracking_code }}</div>
    </div>
</body>
</html>
