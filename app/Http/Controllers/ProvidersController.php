<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Illuminate\Http\Request;

class ProvidersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $providers = Provider::query()
        ->where('name', 'LIKE', "%{$search}%")
            ->orderBy('id')
            ->paginate(20)
            ->appends(['search' => $search]);

        return view('providers.index', compact('providers', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('providers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:30',
            'mail' => 'nullable|email|max:255',
            'delivery_day' => 'nullable|string',
        ]);

        Provider::create($validated);

        return redirect()
            ->route('providers.index')
            ->with('success', 'Proveedor creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Provider $providers)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Provider $provider)
    {
        return view('providers.edit', compact('provider'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Provider $provider)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:30',
            'mail' => 'nullable|email|max:255',
            'delivery_day' => 'nullable|string',
        ]);

        $provider->update($validated);

        return redirect()
            ->route('providers.index', $provider)
            ->with('success', 'Proveedor actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Provider $provider)
    {
        try {
            $provider->delete();
            return redirect()
                ->route('providers.index')
                ->with('success', 'Proveedor eliminado exitosamente');
        } catch (\Exception $e) {
            return redirect()
                ->route('providers.index')
                ->with('error', 'No se puede eliminar el Proveedor');
        }
    }
}
