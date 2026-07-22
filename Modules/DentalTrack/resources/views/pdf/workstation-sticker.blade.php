<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Workstation Sticker</title>
    <style>
        body { margin: 0; padding: 0; font-family: sans-serif; }
        .sticker { width: 170.08px; height: 170.08px; padding: 8px; box-sizing: border-box; text-align: center; }
        .qr { width: 110px; height: 110px; }
        .name { font-size: 11px; font-weight: bold; margin-top: 4px; }
        .type { font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <div class="sticker">
        <img src="{{ $qrImage }}" class="qr" alt="QR">
        <div class="name">{{ $workstation->name }}</div>
        <div class="type">{{ $workstation->type->value }}</div>
    </div>
</body>
</html>
