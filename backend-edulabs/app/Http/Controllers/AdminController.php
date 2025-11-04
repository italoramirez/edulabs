<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use App\Models\Setting;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $groups = Group::with('users:id,name,email,group_id')->get();
            $groups->toArray();
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error al obtener los grupos',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json($groups);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createGroup(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

           $group =  Group::firstOrCreate(['name' => $validated['name']]);

            return response()->json([
                'message' => 'Grupo creado correctamente',
                'group' => $group
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error al crear el grupo',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function assignUserToGroup(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'group_id' => 'required|exists:groups,id',
            ]);

            $user = User::findOrFail($validated['user_id']);
            $user->group_id = $validated['group_id'];
            $user->save();

            return response()->json(['message' => 'Usuario asignado al grupo correctamente']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al asignar el usuario al grupo',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @param Group $group
     * @return JsonResponse
     */
    public function updateLimit(Request $request, Group $group): JsonResponse
    {
        $request->validate(['storage_limit' => 'required|integer|min:1048576']);

        $group->update(['storage_limit' => $request->storage_limit]);

        return response()->json([
            'success' => true,
            'message' => 'Límite de almacenamiento del grupo actualizado correctamente',
            'group' => $group,
        ]);
    }

}
