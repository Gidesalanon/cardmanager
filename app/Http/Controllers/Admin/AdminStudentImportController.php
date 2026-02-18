<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Eleve;
use App\Models\Classe;
use App\Models\Ecole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;


class AdminStudentImportController extends Controller
{
    public function create()
    {
        return view('admin.eleves.import.create', [
            'classes' => Classe::orderBy('nom')->get()->unique('nom'),
            'ecoles' => Ecole::orderBy('nom_ecole')->get(),
        ]);
    }



    public function preview(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:xlsx,xls',
        ]);

        $filePath = $request->file('document')->getPathname();

        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        $rows = $worksheet->toArray();
        $images = [];

        foreach ($worksheet->getDrawingCollection() as $drawing) {

            $coordinates = $drawing->getCoordinates();

            preg_match('/\d+/', $coordinates, $matches);
            $row = $matches[0] ?? null;

            if ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\Drawing) {

                $imageContents = file_get_contents($drawing->getPath());
                $base64 = base64_encode($imageContents);

                $images[$row] = [
                    'image' => 'data:image/png;base64,' . $base64,
                    'coordinates' => $coordinates,
                ];
            }

            if ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing) {

                ob_start();
                call_user_func(
                    $drawing->getRenderingFunction(),
                    $drawing->getImageResource()
                );
                $imageContents = ob_get_contents();
                ob_end_clean();

                $base64 = base64_encode($imageContents);

                $images[$row] = [
                    'image' => 'data:image/png;base64,' . $base64,
                    'coordinates' => $coordinates,
                ];
            }
        }

        return response()->json([
            'rows' => $rows,
            'images' => $images,
        ]);
    }


    // public function preview(Request $request)
    // {

    //     $request->validate([
    //         'document' => 'required|file|mimes:xlsx,xls',
    //     ]);

    //     $import = new PreviewImport();

    //     Excel::import($import, $request->file('document'));

    //     dd($import->images);

    //     return response()->json([
    //         'rows' => $import->rows,
    //         'images' => $import->images,
    //     ]);

    //     // $request->validate([
    //     //     'document' => 'required|file|mimes:xlsx,xls,csv',
    //     // ]);

    //     // $rows = Excel::toArray([], $request->file('document'))[0] ?? [];

    //     // return response()->json(['rows' => $rows]);
    // }

    public function storeAll(Request $request)
    {
        $request->validate([
            'ecole_id' => 'required|exists:ecoles,id',
            'classe_id' => 'required|exists:classes,id',
            'students' => 'required|array'
        ]);

        DB::transaction(function () use ($request) {

            foreach ($request->students as $s) {

                $photoPath = null;

                //Gestion photo base64
                if (!empty($s['photo']) && str_contains($s['photo'], 'base64')) {

                    // Extraire type image
                    preg_match('/^data:image\/(\w+);base64,/', $s['photo'], $type);

                    $image = substr($s['photo'], strpos($s['photo'], ',') + 1);
                    $image = base64_decode($image);

                    $extension = strtolower($type[1] ?? 'png');

                    // Sécuriser extension
                    if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
                        $extension = 'png';
                    }

                    $fileName = 'eleves/' . Str::uuid() . '.' . $extension;

                    Storage::disk('public')->put($fileName, $image);

                    $photoPath = $fileName;
                }

                Eleve::create([
                    'ecole_id' => $request->ecole_id,
                    'classe_id' => $request->classe_id,
                    'nom' => $s['nom'],
                    'prenom' => $s['prenom'],
                    'sexe' => $s['sexe'],
                    'date_naissance' => $s['date_naissance'],
                    'lieu_naissance' => $s['lieu_naissance'],
                    'telephone_tuteur' => $s['telephone_tuteur'],
                    'matricule_edumaster' => $s['matricule'],
                    'photo' => $photoPath, //ajout ici
                ]);
            }
        });

        return response()->json(['success' => true]);
    }

    // public function storeAll(Request $request)
    // {
    //     $request->validate([
    //         'ecole_id' => 'required|exists:ecoles,id',
    //         'classe_id' => 'required|exists:classes,id',
    //         'students' => 'required|array'
    //     ]);

    //     DB::transaction(function () use ($request) {

    //         foreach ($request->students as $s) {

    //             Eleve::create([
    //                 'ecole_id' => $request->ecole_id,
    //                 'classe_id' => $request->classe_id,
    //                 'nom' => $s['nom'],
    //                 'prenom' => $s['prenom'],
    //                 'sexe' => $s['sexe'],
    //                 'date_naissance' => $s['date_naissance'],
    //                 'lieu_naissance' => $s['lieu_naissance'],
    //                 'telephone_tuteur' => $s['telephone_tuteur'],
    //                 'matricule_edumaster' => $s['matricule'],
    //             ]);
    //         }
    //     });

    //     return response()->json(['success' => true]);
    // }
}
