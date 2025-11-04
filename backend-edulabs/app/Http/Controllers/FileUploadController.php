<?php

namespace App\Http\Controllers;

use App\Models\FileUpload;
use App\Models\Setting;
use App\Services\Settings\StorageLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use File;

class FileUploadController extends Controller
{
    public function __construct(
        protected StorageLimitService $storageLimitService
    )
    {
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $files = $user->files()
            ->get(['id', 'filename', 'size', 'created_at'])
            ->map(function ($file) {
                return [
                    'id' => $file->id,
                    'filename' => $file->filename,
                    'size' => $file->size,
                    'created_at' => $file->created_at,
                    'url' => Storage::url('uploads/' . $file->filename),
                ];
            });

        return response()->json($files);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate(['file' => 'required|file|max:20480']);
            $user = $request->user();
            $file = $request->file('file');

            $fileSize = $file->getSize();

            // ⚙️ Verificar límite antes de subir
            if (! $this->storageLimitService->canUpload($user, $fileSize)) {
                $limit = $this->storageLimitService->getUserLimit($user);
                return response()->json([
                    'error' => 'Cuota de almacenamiento excedida',
                    'message' => 'Tu límite es de ' . round($limit / 1048576, 2) . ' MB',
                ], 422);
            }

            $user = $request->user();
            $file = $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension());

            // ⚙️ Configuración base
            $limits = $this->getStorageLimits($user);
            $forbidden = $this->getForbiddenExtensions();

            // Validar extensión
            if (in_array($ext, $forbidden)) {
                return $this->error("El tipo de archivo .$ext no está permitido.");
            }

            //Verificar cuota
            if (!$this->hasAvailableSpace($user, $file->getSize(), $limits['effective'])) {
                $limitMB = round($limits['effective'] / 1048576, 2);
                return $this->error("Error: Cuota de almacenamiento excedida. Límite asignado: {$limitMB} MB.");
            }

            //Si es ZIP
            if ($ext === 'zip') {
                return $this->handleZipUpload($user, $file, $forbidden, $limits['effective']);
            }

            // 📄 Archivo normal
            return $this->handleSingleUpload($user, $file);

        } catch (\Exception $e) {
            report($e);
            return $this->error('Error al procesar la solicitud.', 500);
        }
    }

    /**
     * @param $user
     * @return array
     */
    private function getStorageLimits($user): array
    {
        $globalLimit = Setting::getValue('default_limit', 10 * 1024 * 1024); // 10MB
        $groupLimit  = optional($user->group)->storage_limit;
        $userLimit   = $user->storage_limit;

        return [
            'global' => $globalLimit,
            'group' => $groupLimit,
            'user' => $userLimit,
            'effective' => $userLimit ?? $groupLimit ?? $globalLimit,
        ];
    }

    /**
     * @return array
     */
    private function getForbiddenExtensions(): array
    {
        $forbidden = Setting::getValue('forbidden_extensions', []);
        return is_array($forbidden)
            ? array_map('strtolower', $forbidden)
            : array_map('strtolower', explode(',', $forbidden));
    }

    /**
     * @param $user
     * @param int $newFileSize
     * @param int $limit
     * @return bool
     */
    private function hasAvailableSpace($user, int $newFileSize, int $limit): bool
    {
        $used = $user->files()->sum('size');
        return ($used + $newFileSize) <= $limit;
    }

    /**
     * @param $user
     * @param $file
     * @param array $forbidden
     * @param int $limit
     * @return JsonResponse
     */
    private function handleZipUpload($user, $file, array $forbidden, int $limit): JsonResponse
    {
        $zip = new ZipArchive;
        $path = $file->getRealPath();

        if ($zip->open($path) !== TRUE) {
            return $this->error('No se pudo abrir el archivo ZIP.');
        }

        // Validar contenido
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);
            $internalExt = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
            if (in_array($internalExt, $forbidden)) {
                $zip->close();
                return $this->error("El archivo '{$entry}' dentro del .zip no está permitido.");
            }
        }

        // Crear carpeta única
        $uuid = (string) Str::uuid();
        $relativeFolder = "uploads/{$uuid}";
        $absoluteFolder = storage_path("app/public/{$relativeFolder}");
        File::ensureDirectoryExists($absoluteFolder);

        // Extraer archivos
        $zip->extractTo($absoluteFolder);
        $zip->close();

        $saved = $this->saveExtractedFiles($user, $absoluteFolder, $relativeFolder, $limit);

        return response()->json([
            'message' => 'ZIP procesado y archivos guardados correctamente.',
            'files' => $saved,
        ]);
    }

    /**
     * @param $user
     * @param string $absoluteFolder
     * @param string $relativeFolder
     * @param int $limit
     * @return array
     */
    private function saveExtractedFiles($user, string $absoluteFolder, string $relativeFolder, int $limit): array
    {
        $allFiles = collect(File::allFiles($absoluteFolder));
        $saved = [];

        foreach ($allFiles as $f) {
            $used = $user->files()->sum('size');
            if (($used + $f->getSize()) > $limit) {
                break;
            }

            $relativePath = "{$relativeFolder}/" . $f->getFilename();
            $record = FileUpload::create([
                'user_id' => $user->id,
                'filename' => $f->getFilename(),
                'path' => $relativePath,
                'size' => $f->getSize(),
            ]);

            $record->url = asset('storage/' . $relativePath);
            $saved[] = $record;
        }

        return $saved;
    }

    /**
     * @param $user
     * @param $file
     * @return JsonResponse
     */
    private function handleSingleUpload($user, $file): JsonResponse
    {
        $uniqueName = uniqid() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('uploads', $uniqueName, 'public');

        $upload = FileUpload::create([
            'user_id' => $user->id,
            'filename' => $uniqueName,
            'path' => $path,
            'size' => $file->getSize(),
        ]);

        $upload->url = asset('storage/' . $path);

        return response()->json([
            'message' => 'Archivo guardado correctamente.',
            'file' => $upload,
        ]);
    }

    /**
     * @param string $message
     * @param int $status
     * @return JsonResponse
     */
    private function error(string $message, int $status = 422): JsonResponse
    {
        return response()->json(['error' => $message], $status);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        // 🔍 Buscar archivo del usuario
        $file = $user->files()->find($id);

        if (!$file) {
            return response()->json(['error' => 'Archivo no encontrado o no pertenece al usuario.'], 404);
        }

        // Ruta física
        $filePath = storage_path('app/public/' . $file->path);

        //Eliminar del sistema de archivos si existe
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $file->delete();

        return response()->json(['message' => 'Archivo eliminado correctamente.']);
    }

}
