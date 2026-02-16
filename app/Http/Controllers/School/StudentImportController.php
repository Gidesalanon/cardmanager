<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\Eleve;
use App\Models\Classe;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class StudentImportController extends Controller
{
    public function create()
    {
        return view('school.eleves.import.create', [
            'activeYear' => SchoolYear::active()->firstOrFail(),
            'classes' => Classe::select('id', 'nom')
                ->orderBy('nom')
                ->get()
                ->unique('nom')
                ->values(),
        ]);
    }

    public function preview(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $rows = Excel::toArray([], $request->file('document'))[0] ?? [];

        $students = [];
        $seenMatricules = [];

        foreach ($rows as $row) {

            if (!isset($row[2])) continue;

            $matricule = trim((string) $row[2]);

            if (
                empty($matricule) ||
                str_contains(strtolower($matricule), 'matricule')
            ) continue;

            if (in_array($matricule, $seenMatricules)) continue;
            $seenMatricules[] = $matricule;

            $rawSexe = strtoupper(trim((string)($row[5] ?? '')));

            $sexe = 'M';
            if ($rawSexe === 'F' || str_contains($rawSexe, 'FEM')) {
                $sexe = 'F';
            }

            $dateNaissance = null;

            if (!empty($row[7])) {
                try {
                    if (is_numeric($row[7])) {
                        $dateNaissance = Carbon::instance(
                            \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[7])
                        )->format('Y-m-d');
                    } else {
                        $dateNaissance = Carbon::parse($row[7])->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    $dateNaissance = null;
                }
            }

            $students[] = [
                'photo' => null,
                'matricule' => $matricule,
                'nom' => trim($row[3] ?? ''),
                'prenom' => trim($row[4] ?? ''),
                'sexe' => $sexe,
                'nationalite' => trim($row[6] ?? ''),
                'date_naissance' => $dateNaissance,
                'lieu_naissance' => trim($row[8] ?? ''),
                'telephone_tuteur' => trim($row[9] ?? ''),
            ];
        }

        return response()->json(['students' => $students]);
    }

    public function storeAll(Request $request)
    {
        $request->validate([
            'classe_id' => 'required|exists:classes,id',
            'students'  => 'required|array|min:1',
        ]);

        $ecole = auth()->user()->ecole;
        abort_if(!$ecole, 403);

        DB::transaction(function () use ($request, $ecole) {

            foreach ($request->students as $index => $s) {

                if (
                    empty($s['photo']) ||
                    empty($s['matricule']) ||
                    empty($s['nom']) ||
                    empty($s['prenom']) ||
                    empty($s['sexe']) ||
                    empty($s['date_naissance']) ||
                    empty($s['lieu_naissance']) ||
                    empty($s['telephone_tuteur'])
                ) {
                    throw new \Exception("Champs manquants ligne " . ($index+1));
                }

                if (Eleve::where('matricule_edumaster', $s['matricule'])->exists()) {
                    continue;
                }

                $photoPath = null;

                if (preg_match('/^data:image\/(\w+);base64,/', $s['photo'], $type)) {

                    $data = substr($s['photo'], strpos($s['photo'], ',') + 1);
                    $data = base64_decode($data);

                    $extension = strtolower($type[1]);
                    $fileName = uniqid('eleve_').'.'.$extension;

                    $photoPath = 'eleves/photos/'.$fileName;

                    Storage::disk('public')->put($photoPath, $data);
                }

                $matricule = $s['matricule'];

                $qrCodePath = 'eleves/qrcodes/' . Str::slug($matricule) . '.png';
                $qrCodeFullPath = storage_path('app/public/' . $qrCodePath);

                // 🔥 créer le dossier si inexistant
                $directory = dirname($qrCodeFullPath);

                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                $qrCode = new \Endroid\QrCode\QrCode($matricule);
                $writer = new \Endroid\QrCode\Writer\PngWriter();

                $result = $writer->write($qrCode);

                $result->saveToFile($qrCodeFullPath);

                Eleve::create([
                    'ecole_id' => $ecole->id,
                    'classe_id' => $request->classe_id,
                    'nom' => $s['nom'],
                    'prenom' => $s['prenom'],
                    'sexe' => $s['sexe'],
                    'nationalite' => $s['nationalite'] ?? null,
                    'date_naissance' => $s['date_naissance'],
                    'lieu_naissance' => $s['lieu_naissance'],
                    'telephone_tuteur' => $s['telephone_tuteur'],
                    'photo' => $photoPath,
                    'matricule_edumaster' => $matricule,
                    'qr_code' => $qrCodePath,
                ]);
            }
        });

        return response()->json(['success' => true]);
    }
}
