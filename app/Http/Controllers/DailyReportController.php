<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyReportController extends Controller
{
    /**
     * Mostrar formulario de reporte diario.
     * Si ya envió hoy, muestra los datos enviados.
     */
    public function create()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $existingReport = DailyReport::where('user_id', $user->id)
            ->where('report_date', $today)
            ->first();

        // Obtener el team leader del equipo
        $teamLeader = null;
        if ($user->team_id) {
            $teamLeader = \App\Models\User::where('team_id', $user->team_id)
                ->where('role', 'team_leader')
                ->first();
        }

        return view('asesor.report-form', compact('user', 'today', 'existingReport', 'teamLeader'));
    }

    /**
     * Guardar el reporte diario.
     * Solo 1 reporte por día por asesor.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();

        // Verificar que no haya enviado ya hoy
        $existing = DailyReport::where('user_id', $user->id)
            ->where('report_date', $today)
            ->first();

        if ($existing) {
            return redirect()->route('asesor.dashboard')
                ->with('error', 'Ya enviaste tu reporte de hoy. Solo se permite un reporte por día.');
        }

        $rules = [
            'visits'               => 'required|integer|min:0|max:999',
            'sign_captures'        => 'required|integer|min:0|max:999',
            'exclusive_captures'   => 'required|integer|min:0|max:999',
            'closings'             => 'required|integer|min:0|max:999',
            'calls_made'           => 'required|integer|min:0|max:999',
            'properties_in_system' => 'required|integer|min:0|max:999',
            // Validación de visitas con datos de clientes
            'visit_client_name'    => 'nullable|array|max:50',
            'visit_client_phone'   => 'nullable|array|max:50',
            'visit_latitude'       => 'nullable|array|max:50',
            'visit_longitude'      => 'nullable|array|max:50',
            'visit_address'        => 'nullable|array|max:50',
        ];

        // Validar fotos obligatorias por cada captación con letrero
        $signCaptures = (int) $request->input('sign_captures', 0);
        if ($signCaptures > 0) {
            $rules['sign_images'] = 'required|array|min:' . $signCaptures;
            $rules['sign_images.*'] = 'required|image|max:10240';
        }

        $request->validate($rules);

        // Procesar fotos de letreros si se suben
        $signImagePathValue = null;
        if ($request->hasFile('sign_images')) {
            $signImagePaths = [];
            foreach ($request->file('sign_images') as $file) {
                if ($file->isValid()) {
                    $signImagePaths[] = $this->uploadAndCompressSignImage($file);
                }
            }
            $signImagePathValue = !empty($signImagePaths) ? json_encode($signImagePaths) : null;
        }

        // Construir el array de datos de visitas (con geolocalización)
        $visitsData = null;
        $visitCount = (int) $request->visits;
        if ($visitCount > 0 && $request->has('visit_client_name')) {
            $visitsData = [];
            $names    = $request->input('visit_client_name', []);
            $phones   = $request->input('visit_client_phone', []);
            $lats     = $request->input('visit_latitude', []);
            $lngs     = $request->input('visit_longitude', []);
            $addrs    = $request->input('visit_address', []);

            for ($i = 0; $i < $visitCount; $i++) {
                $visitsData[] = [
                    'client_name'  => trim($names[$i]  ?? ''),
                    'client_phone' => trim($phones[$i] ?? ''),
                    'latitude'     => isset($lats[$i])  && $lats[$i]  !== '' ? (float) $lats[$i]  : null,
                    'longitude'    => isset($lngs[$i])  && $lngs[$i]  !== '' ? (float) $lngs[$i]  : null,
                    'address'      => trim($addrs[$i]   ?? ''),
                ];
            }
        }

        // Convertir la lista de números telefónicos a un string separado por comas
        $phoneNumbers = null;
        if ($request->calls_made > 0 && $request->has('call_phone_number_list')) {
            $cleanedNumbers = array_filter(array_map('trim', $request->call_phone_number_list));
            if (!empty($cleanedNumbers)) {
                $phoneNumbers = implode(', ', $cleanedNumbers);
            }
        }

        DailyReport::create([
            'user_id'               => $user->id,
            'report_date'           => $today,
            'visits'                => $request->visits,
            'visits_data'           => $visitsData,
            'sign_captures'         => $request->sign_captures,
            'exclusive_captures'    => $request->exclusive_captures,
            'closings'              => $request->closings,
            'calls_made'            => $request->calls_made,
            'call_phone_number'     => $phoneNumbers,
            'properties_in_system'  => $request->properties_in_system,
            'source'                => 'web',
            'sign_image_path'       => $signImagePathValue,
        ]);

        return redirect()->route('asesor.dashboard')
            ->with('success', '✅ ¡Reporte del día enviado exitosamente!');
    }

    /**
     * Mostrar formulario para editar el reporte de HOY.
     */
    public function edit()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $report = DailyReport::where('user_id', $user->id)
            ->where('report_date', $today)
            ->first();

        if (!$report) {
            return redirect()->route('asesor.report.create')
                ->with('error', 'No tienes un reporte registrado hoy para editar. Por favor crea uno.');
        }

        // Obtener el team leader del equipo
        $teamLeader = null;
        if ($user->team_id) {
            $teamLeader = \App\Models\User::where('team_id', $user->team_id)
                ->where('role', 'team_leader')
                ->first();
        }

        // Parsear números de teléfono existentes
        $existingPhones = [];
        if ($report->call_phone_number) {
            $existingPhones = array_map('trim', explode(',', $report->call_phone_number));
        }

        return view('asesor.report-edit', compact('user', 'today', 'report', 'teamLeader', 'existingPhones'));
    }

    /**
     * Actualizar el reporte diario de HOY.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();

        $report = DailyReport::where('user_id', $user->id)
            ->where('report_date', $today)
            ->first();

        if (!$report) {
            return redirect()->route('asesor.dashboard')
                ->with('error', 'No tienes un reporte registrado hoy para editar.');
        }

        // Validar estrictamente que el reporte sea del día de hoy
        if (!$report->report_date->isToday()) {
            return redirect()->route('asesor.dashboard')
                ->with('error', 'Acción no permitida: Solo puedes editar el reporte del día de hoy. Los reportes anteriores no se pueden modificar.');
        }

        $rules = [
            'sign_captures'        => 'required|integer|min:0|max:999',
            'exclusive_captures'   => 'required|integer|min:0|max:999',
            'closings'             => 'required|integer|min:0|max:999',
            'calls_made'           => 'required|integer|min:0|max:999',
            'properties_in_system' => 'required|integer|min:0|max:999',
            'visit_client_name'    => 'nullable|array|max:50',
            'visit_client_phone'   => 'nullable|array|max:50',
            'visit_latitude'       => 'nullable|array|max:50',
            'visit_longitude'      => 'nullable|array|max:50',
            'visit_address'        => 'nullable|array|max:50',
        ];

        $signCaptures = (int) $request->input('sign_captures', 0);
        $existingPaths = [];
        if (!empty($report->sign_image_path)) {
            $decoded = json_decode($report->sign_image_path, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $existingPaths = $decoded;
            } else {
                $existingPaths = [$report->sign_image_path];
            }
        }

        // Validar foto obligatoria si no existe una previamente subida para esa posición
        for ($i = 0; $i < $signCaptures; $i++) {
            if (empty($existingPaths[$i])) {
                $rules["sign_images.{$i}"] = 'required|image|max:10240';
            } else {
                $rules["sign_images.{$i}"] = 'nullable|image|max:10240';
            }
        }

        $request->validate($rules);

        // Procesar fotos de letreros (mantener existentes y actualizar/añadir nuevas)
        $signImagePaths = [];
        for ($i = 0; $i < $signCaptures; $i++) {
            $signImagePaths[$i] = $existingPaths[$i] ?? null;
        }

        if ($request->hasFile('sign_images')) {
            foreach ($request->file('sign_images') as $index => $file) {
                if ($file && $file->isValid()) {
                    $signImagePaths[$index] = $this->uploadAndCompressSignImage($file);
                }
            }
        }

        $signImagePaths = array_values(array_filter($signImagePaths));
        $signImagePathValue = !empty($signImagePaths) ? json_encode($signImagePaths) : null;

        // 1. Obtener visitas previamente registradas hoy
        $existingVisits = $report->visits_data ?? [];
        if (!is_array($existingVisits)) {
            $existingVisits = [];
        }

        // Si es un reporte antiguo sin estructura JSON pero tiene visitas registradas
        if (empty($existingVisits) && $report->visits > 0) {
            for ($i = 0; $i < $report->visits; $i++) {
                $existingVisits[] = [
                    'client_name'  => 'Cliente Histórico ' . ($i + 1),
                    'client_phone' => '',
                    'latitude'     => null,
                    'longitude'    => null,
                    'address'      => 'Visita previa sin coordenadas'
                ];
            }
        }

        // 2. Procesar nuevas visitas agregadas hoy
        $newVisits = [];
        if ($request->has('visit_client_name')) {
            $names  = $request->input('visit_client_name', []);
            $phones = $request->input('visit_client_phone', []);
            $lats   = $request->input('visit_latitude', []);
            $lngs   = $request->input('visit_longitude', []);
            $addrs  = $request->input('visit_address', []);

            for ($i = 0; $i < count($names); $i++) {
                if (empty($names[$i])) continue;
                $newVisits[] = [
                    'client_name'  => trim($names[$i]  ?? ''),
                    'client_phone' => trim($phones[$i] ?? ''),
                    'latitude'     => isset($lats[$i])  && $lats[$i]  !== '' ? (float) $lats[$i]  : null,
                    'longitude'    => isset($lngs[$i])  && $lngs[$i]  !== '' ? (float) $lngs[$i]  : null,
                    'address'      => trim($addrs[$i]   ?? ''),
                ];
            }
        }

        // 3. Fusionar visitas previas y nuevas
        $visitsData = array_merge($existingVisits, $newVisits);
        $totalVisits = count($visitsData);

        // Convertir la lista de números telefónicos a un string separado por comas
        $phoneNumbers = null;
        if ($request->calls_made > 0 && $request->has('call_phone_number_list')) {
            $cleanedNumbers = array_filter(array_map('trim', $request->call_phone_number_list));
            if (!empty($cleanedNumbers)) {
                $phoneNumbers = implode(', ', $cleanedNumbers);
            }
        }

        $report->update([
            'visits'               => $totalVisits,
            'visits_data'          => $visitsData,
            'sign_captures'        => $request->sign_captures,
            'exclusive_captures'   => $request->exclusive_captures,
            'closings'             => $request->closings,
            'calls_made'           => $request->calls_made,
            'call_phone_number'    => $phoneNumbers,
            'properties_in_system' => $request->properties_in_system,
            'sign_image_path'      => $signImagePathValue,
        ]);

        return redirect()->route('asesor.dashboard')
            ->with('success', '✅ ¡Reporte del día actualizado exitosamente!');
    }

    /**
     * Sube y comprime la foto del letrero usando GD, con fallback a copia directa si no está disponible.
     */
    private function uploadAndCompressSignImage($file)
    {
        $filename = 'letrero_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $destinationPath = public_path('uploads/letreros');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }
        $targetPath = $destinationPath . '/' . $filename;

        // Intentar optimizar con GD si está disponible
        if (extension_loaded('gd')) {
            list($width, $height, $type) = getimagesize($file->getRealPath());
            
            // Redimensionar si es muy grande (máximo 1200px de ancho o alto)
            $maxDim = 1200;
            if ($width > $maxDim || $height > $maxDim) {
                $ratio = $width / $height;
                if ($ratio > 1) {
                    $newWidth = $maxDim;
                    $newHeight = $maxDim / $ratio;
                } else {
                    $newHeight = $maxDim;
                    $newWidth = $maxDim * $ratio;
                }
            } else {
                $newWidth = $width;
                $newHeight = $height;
            }

            // Crear recurso de imagen
            $src = null;
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $src = imagecreatefromjpeg($file->getRealPath());
                    break;
                case IMAGETYPE_PNG:
                    $src = imagecreatefrompng($file->getRealPath());
                    break;
                case IMAGETYPE_WEBP:
                    $src = imagecreatefromwebp($file->getRealPath());
                    break;
            }

            if ($src) {
                $dst = imagecreatetruecolor($newWidth, $newHeight);
                
                // Conservar transparencia para PNGs
                if ($type == IMAGETYPE_PNG) {
                    imagealphablending($dst, false);
                    imagesavealpha($dst, true);
                }

                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                // Guardar optimizado (calidad 75)
                $saved = false;
                if ($type == IMAGETYPE_PNG) {
                    $saved = imagepng($dst, $targetPath, 6); // 0-9 compression level
                } elseif ($type == IMAGETYPE_WEBP) {
                    $saved = imagewebp($dst, $targetPath, 75);
                } else {
                    $saved = imagejpeg($dst, $targetPath, 75);
                }

                imagedestroy($src);
                imagedestroy($dst);

                if ($saved) {
                    return 'uploads/letreros/' . $filename;
                }
            }
        }

        // Si falla GD o no está instalada, mover el archivo original
        $file->move($destinationPath, $filename);
        return 'uploads/letreros/' . $filename;
    }
}
