<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UsersController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = User::select(['id','name','email', 'role', 'storage_limit'])->get();
        return response()->json($user, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function updateLimit(Request $request, User $user): JsonResponse
    {
        $request->validate(['storage_limit' => 'required|integer|min:1048576']);

        $user->update(['storage_limit' => $request->storage_limit]);

        return response()->json([
            'success' => true,
            'message' => 'Límite de almacenamiento actualizado correctamente',
            'user' => $user,
        ]);
    }

    /**
     * @param int $user
     * @return JsonResponse
     */
    public function delete(int $user): JsonResponse
    {
        try {
            $user = User::findOrFail($user);
            $user->files()->delete();
            $user->tokens()->delete();
            $user->delete();
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al eliminar el usuario',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
