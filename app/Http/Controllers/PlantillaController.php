<?php

namespace App\Http\Controllers;

use App\Models\Plantilla;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlantillaController extends Controller
{
    public function index()
    {
        // Solo personalizadas (no predefinidas)
        $plantillas = Plantilla::query()
            ->where('predefinida', false)
            ->with(['campos', 'creador'])
            ->latest()
            ->get();

        return view('plantillas.index', compact('plantillas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['nullable', 'string', 'max:255'],
            'campos' => ['required', 'array'],
            'campos.*' => ['string', 'max:255'],
        ]);

        $userId = auth()->id();

        DB::transaction(function () use ($data, $userId) {
            $plantilla = Plantilla::create([
                'nombre' => $data['nombre'],
                'tipo' => $data['tipo'] ?? 'custom',
                'descripcion' => $data['descripcion'] ?? null,
                'predefinida' => false,
                'created_by' => $userId,
            ]);

            $rows = [];
            $orden = 1;

            foreach ($data['campos'] as $campo) {
                $rows[] = [
                    'plantilla_id' => $plantilla->id,
                    'campo' => $campo,
                    'orden' => $orden++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $plantilla->campos()->insert($rows);
        });

        return redirect()
            ->route('plantillas.index')
            ->with('success', 'Plantilla creada correctamente');
    }

    public function update(Request $request, Plantilla $plantilla)
    {
        abort_if($plantilla->predefinida, 403);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['nullable', 'string', 'max:255'],
            'campos' => ['required', 'array'],
            'campos.*' => ['string', 'max:255'],
        ]);

        DB::transaction(function () use ($plantilla, $data) {
            $plantilla->update([
                'nombre' => $data['nombre'],
                'tipo' => $data['tipo'] ?? $plantilla->tipo ?? 'custom',
                'descripcion' => $data['descripcion'] ?? $plantilla->descripcion,
            ]);

            $plantilla->campos()->delete();

            $rows = [];
            $orden = 1;
            foreach ($data['campos'] as $campo) {
                $rows[] = [
                    'plantilla_id' => $plantilla->id,
                    'campo' => $campo,
                    'orden' => $orden++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $plantilla->campos()->insert($rows);
        });

        return redirect()
            ->route('plantillas.index')
            ->with('success', 'Plantilla actualizada correctamente');
    }

    public function destroy(Plantilla $plantilla)
    {
        abort_if($plantilla->predefinida, 403);

        DB::transaction(function () use ($plantilla) {
            $plantilla->campos()->delete();
            $plantilla->delete();
        });

        return redirect()
            ->route('plantillas.index')
            ->with('success', 'Plantilla eliminada correctamente');
    }
}
