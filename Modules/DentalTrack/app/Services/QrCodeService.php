<?php

namespace Modules\DentalTrack\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Modules\DentalTrack\Models\Order;
use Modules\DentalTrack\Models\Workstation;

class QrCodeService
{
    public function generateForOrder(Order $order): string
    {
        return $this->generateSvg($order->qrUrl(), 200);
    }

    public function generateForWorkstation(Workstation $workstation): string
    {
        return $this->generateSvg($workstation->qrUrl(), 300);
    }

    public function generateSvg(string $url, int $size = 200): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, 2),
            new SvgImageBackEnd
        );
        $writer = new Writer($renderer);

        return $writer->writeString($url);
    }

    public function generateBase64Image(string $url, int $size = 200): string
    {
        $svg = $this->generateSvg($url, $size);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
