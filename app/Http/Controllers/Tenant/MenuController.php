<?php

namespace App\Http\Controllers\Tenant;

use App\Models\Menu;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class MenuController
{
    public function index(Tenant $tenant): JsonResponse
    {
        Gate::authorize('viewAny', Menu::class);

        return response()->json(
            Menu::query()->orderBy('id')->get(['id', 'name', 'price_amount', 'is_available'])
        );
    }

    public function show(Tenant $tenant, Menu $menu): JsonResponse
    {
        Gate::authorize('view', $menu);

        return response()->json($menu->only(['id', 'name', 'price_amount', 'is_available']));
    }

    public function update(Request $request, Tenant $tenant, Menu $menu): JsonResponse
    {
        Gate::authorize('update', $menu);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'price_amount' => ['sometimes', 'integer', 'min:0'],
            'is_available' => ['sometimes', 'boolean'],
        ]);

        $menu->update($validated);

        return response()->json($menu->only(['id', 'name', 'price_amount', 'is_available']));
    }

    public function destroy(Tenant $tenant, Menu $menu): JsonResponse
    {
        Gate::authorize('delete', $menu);

        $menu->delete();

        return response()->json(['deleted' => true]);
    }
}