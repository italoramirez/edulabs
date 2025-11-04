<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\Settings\SettingsService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SettingsController extends Controller
{

    /**
     * @param SettingsService $settingsService
     */
    public function __construct(
        protected SettingsService $settingsService
    )
    {
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function settings(Request $request): JsonResponse
    {
        try {
            $defaultLimit = (int) Setting::getValue('default_limit', 10485760);
            $forbidden = Setting::getValue('forbidden_extensions', 'exe,bat,php,js,sh');

            if (is_string($forbidden)) {
                $forbidden = array_map('trim', explode(',', $forbidden));
            }

            return response()->json([
                'default_limit' => $defaultLimit,
                'forbidden_extensions' => $forbidden,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error al obtener la configuración',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'default_limit' => 'nullable|integer|min:1048576',
                'forbidden_extensions' => 'nullable|array',
                'forbidden_extensions.*' => 'string',
            ]);

            $this->settingsService->update(
                $request->only(['default_limit', 'forbidden_extensions'])
            );

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar la configuración',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
