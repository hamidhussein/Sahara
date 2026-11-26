<?php

namespace Database\Seeders;

use App\Payments\SampleDetails;
use App\Settings\DonationSettings;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Fills sample JazzCash, Easypaisa and Raast details and placeholder QR codes
 * so the Donate page can be tried end to end. Runs only on local and test
 * copies, and never overwrites details Finance has entered. The live site
 * ignores these values (see SampleDetails).
 */
class SamplePaymentDetailsSeeder extends Seeder
{
    public function run(): void
    {
        if (! SampleDetails::allowed()) {
            return;
        }

        $settings = app(DonationSettings::class);

        $fill = function (string $property, string $value) use ($settings) {
            if (blank($settings->{$property})) {
                $settings->{$property} = $value;
            }
        };

        $fill('jazzcash_title', SampleDetails::TITLE);
        $fill('jazzcash_number', SampleDetails::JAZZCASH_NUMBER);
        $fill('easypaisa_title', SampleDetails::TITLE);
        $fill('easypaisa_number', SampleDetails::EASYPAISA_NUMBER);
        $fill('raast_id', SampleDetails::RAAST_ID);

        $fill('jazzcash_qr', $this->qr('jazzcash', "SAMPLE JazzCash\n".SampleDetails::JAZZCASH_NUMBER));
        $fill('easypaisa_qr', $this->qr('easypaisa', "SAMPLE Easypaisa\n".SampleDetails::EASYPAISA_NUMBER));
        $fill('bank_qr', $this->qr('bank', "SAMPLE Raast\n".SampleDetails::RAAST_ID."\n{$settings->bank_name}\nA/C {$settings->account_number}"));

        $settings->save();
    }

    /** Writes a placeholder QR code (SVG) to the public disk and returns its path. */
    protected function qr(string $name, string $content): string
    {
        $svg = (new QRCode(new QROptions([
            'outputType' => QROutputInterface::MARKUP_SVG,
            'outputBase64' => false,
            'svgAddXmlHeader' => true,
            'drawLightModules' => false,
            'addQuietzone' => true,
        ])))->render($content);

        $path = SampleDetails::QR_DIRECTORY."/{$name}-qr.svg";
        Storage::disk('public')->put($path, $svg);

        return $path;
    }
}
