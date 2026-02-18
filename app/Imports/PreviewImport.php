<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

class PreviewImport implements ToArray, WithDrawings
{
    public $rows = [];
    public $images = [];

    public function array(array $array)
    {
        $this->rows = $array;
    }

    public function drawings()
    {
        return function ($worksheet) {

            foreach ($worksheet->getDrawingCollection() as $drawing) {

                $coordinates = $drawing->getCoordinates();

                preg_match('/\d+/', $coordinates, $matches);
                $row = $matches[0] ?? null;

                if ($drawing instanceof Drawing) {

                    $imageContents = file_get_contents($drawing->getPath());
                    $base64 = base64_encode($imageContents);

                    $this->images[$row] = [
                        'image' => 'data:image/png;base64,' . $base64,
                        'coordinates' => $coordinates,
                    ];
                }

                // Cas images insérées différemment (MemoryDrawing)
                if ($drawing instanceof MemoryDrawing) {

                    ob_start();
                    call_user_func(
                        $drawing->getRenderingFunction(),
                        $drawing->getImageResource()
                    );
                    $imageContents = ob_get_contents();
                    ob_end_clean();

                    $base64 = base64_encode($imageContents);

                    $this->images[$row] = [
                        'image' => 'data:image/png;base64,' . $base64,
                        'coordinates' => $coordinates,
                    ];
                }
            }
        };
    }
}
