<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Batch Order Stickers</title>
    <style>
        body { margin: 0; padding: 0; font-family: sans-serif; }
        .page { width: 595px; padding: 20px; }
        .grid { display: inline-block; }
        .sticker { width: 141.73px; height: 113.39px; padding: 4px; box-sizing: border-box; text-align: center; float: left; border: 1px dashed #ddd; }
        .qr { width: 70px; height: 70px; }
        .label { font-size: 8px; color: #555; margin-top: 2px; }
        .id { font-size: 9px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="page">
        @foreach ($stickers as $sticker)
            <div class="sticker">
                <img src="{{ $sticker['qrImage'] }}" class="qr" alt="QR">
                <div class="id">#{{ $sticker['order']->id }}</div>
                <div class="label">{{ $sticker['order']->patient_ref ?? '' }}</div>
                <div class="label">{{ $sticker['order']->tracking_code }}</div>
            </div>
        @endforeach
    </div>
</body>
</html>
