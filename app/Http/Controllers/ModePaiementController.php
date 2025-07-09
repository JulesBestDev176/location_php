<?php

namespace App\Http\Controllers;

use App\Models\ModePaiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ModePaiementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $modesPaiement = ModePaiement::orderBy('LibelleModePaiement')->get();
            return response()->json($modesPaiement);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des modes de paiement: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la récupération des modes de paiement'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'LibelleModePaiement' => 'required|string|max:50|unique:modepaiements,LibelleModePaiement'
        ]);

        try {
            $modePaiement = ModePaiement::create([
                'LibelleModePaiement' => $request->LibelleModePaiement
            ]);

            return response()->json([
                'message' => 'Mode de paiement créé avec succès.',
                'id' => $modePaiement->IdModePaiement
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du mode de paiement: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la création du mode de paiement: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $modePaiement = ModePaiement::find($id);

            if (!$modePaiement) {
                return response()->json(['erreur' => 'Mode de paiement non trouvé'], 404);
            }

            return response()->json($modePaiement);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du mode de paiement: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la récupération du mode de paiement'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'LibelleModePaiement' => 'sometimes|required|string|max:50|unique:modepaiements,LibelleModePaiement,' . $id . ',IdModePaiement'
        ]);

        try {
            $modePaiement = ModePaiement::find($id);
            if (!$modePaiement) {
                return response()->json(['erreur' => 'Mode de paiement non trouvé'], 404);
            }

            $modePaiement->update($request->all());

            return response()->json(['message' => 'Mode de paiement mis à jour avec succès']);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du mode de paiement: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la mise à jour du mode de paiement'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $modePaiement = ModePaiement::find($id);
            if (!$modePaiement) {
                return response()->json(['erreur' => 'Mode de paiement non trouvé'], 404);
            }

            $modePaiement->delete();

            return response()->json(['message' => 'Mode de paiement supprimé avec succès']);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du mode de paiement: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la suppression du mode de paiement'], 500);
        }
    }
} 