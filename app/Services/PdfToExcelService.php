<?php

namespace App\Services;

use Ilovepdf\Ilovepdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PdfToExcelService
{
    public static function convert(UploadedFile $file): string
    {
        $ilovepdf = new Ilovepdf(
            config('services.ilovepdf.public'),
            config('services.ilovepdf.secret')
        );

        $task = $ilovepdf->newTask('pdfoffice');
        $task->addFile($file->getPathname());
        $task->setOutputFormat('xlsx');
        $task->execute();

        $path = storage_path('app/temp');
        if (!is_dir($path)) mkdir($path, 0777, true);

        $task->download($path);

        return collect(scandir($path))
            ->filter(fn($f) => str_ends_with($f, '.xlsx'))
            ->map(fn($f) => $path.'/'.$f)
            ->first();
    }
}
