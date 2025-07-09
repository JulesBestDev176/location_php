<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Models\Location;
use App\Models\ModePaiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaiementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $paiements = DB::table('paiements')
                ->leftJoin('locations', 'paiements.IdLocation', '=', 'locations.IdLocation')
                ->leftJoin('modepaiements', 'paiements.IdModePaiement', '=', 'modepaiements.IdModePaiement')
                ->select(
                    'paiements.*',
                    'locations.NumeroLocation',
                    'modepaiements.LibelleModePaiement'
                )
                ->orderBy('paiements.DatePaiement', 'desc')
                ->get();

            return response()->json($paiements);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des paiements: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la récupération des paiements'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'DatePaiement' => 'nullable|date',
            'MontantPaiement' => 'required|integer|min:0',
            'NumeroFacture' => 'required|string|max:50|unique:paiements,NumeroFacture',
            'Statut' => 'required|boolean',
            'IdLocation' => 'nullable|exists:locations,IdLocation',
            'IdModePaiement' => 'nullable|exists:modepaiements,IdModePaiement'
        ]);

        try {
            $paiement = Paiement::create([
                'DatePaiement' => $request->DatePaiement ?? now(),
                'MontantPaiement' => $request->MontantPaiement,
                'NumeroFacture' => $request->NumeroFacture,
                'Statut' => $request->Statut,
                'IdLocation' => $request->IdLocation,
                'IdModePaiement' => $request->IdModePaiement
            ]);

            return response()->json([
                'message' => 'Paiement créé avec succès.',
                'id' => $paiement->IdPaiement
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du paiement: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la création du paiement: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $paiement = DB::table('paiements')
                ->leftJoin('locations', 'paiements.IdLocation', '=', 'locations.IdLocation')
                ->leftJoin('modepaiements', 'paiements.IdModePaiement', '=', 'modepaiements.IdModePaiement')
                ->select(
                    'paiements.*',
                    'locations.NumeroLocation',
                    'modepaiements.LibelleModePaiement'
                )
                ->where('paiements.IdPaiement', $id)
                ->first();

            if (!$paiement) {
                return response()->json(['erreur' => 'Paiement non trouvé'], 404);
            }

            return response()->json($paiement);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du paiement: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la récupération du paiement'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'DatePaiement' => 'nullable|date',
            'MontantPaiement' => 'sometimes|required|integer|min:0',
            'NumeroFacture' => 'sometimes|required|string|max:50|unique:paiements,NumeroFacture,' . $id . ',IdPaiement',
            'Statut' => 'sometimes|required|boolean',
            'IdLocation' => 'nullable|exists:locations,IdLocation',
            'IdModePaiement' => 'nullable|exists:modepaiements,IdModePaiement'
        ]);

        try {
            $paiement = Paiement::find($id);
            if (!$paiement) {
                return response()->json(['erreur' => 'Paiement non trouvé'], 404);
            }

            $paiement->update($request->all());

            return response()->json(['message' => 'Paiement mis à jour avec succès']);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du paiement: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la mise à jour du paiement'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $paiement = Paiement::find($id);
            if (!$paiement) {
                return response()->json(['erreur' => 'Paiement non trouvé'], 404);
            }

            $paiement->delete();

            return response()->json(['message' => 'Paiement supprimé avec succès']);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du paiement: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la suppression du paiement'], 500);
        }
    }
} 