<?php

namespace App\Http\Controllers;

use App\Models\Appartement;
use App\Models\Proprietaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppartementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $appartements = DB::table('appartements')
                ->leftJoin('proprietaires', 'appartements.IdProprietaire', '=', 'proprietaires.IdPersonne')
                ->leftJoin('personnes', 'proprietaires.IdPersonne', '=', 'personnes.IdPersonne')
                ->select(
                    'appartements.*',
                    'personnes.Nom as NomProprietaire',
                    'personnes.Prenom as PrenomProprietaire'
                )
                ->orderBy('appartements.AdresseAppartement')
                ->get();

            return response()->json($appartements);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des appartements: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la récupération des appartements'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'AdresseAppartement' => 'required|string|max:255',
            'Surface' => 'nullable|numeric|min:0',
            'NombrePiece' => 'nullable|integer|min:0',
            'Capacite' => 'required|integer|min:1',
            'Disponiblite' => 'boolean',
            'nbrLocataire' => 'integer|min:0',
            'IdProprietaire' => 'nullable|exists:proprietaires,IdPersonne'
        ]);

        try {
            $appartement = Appartement::create([
                'AdresseAppartement' => $request->AdresseAppartement,
                'Surface' => $request->Surface,
                'NombrePiece' => $request->NombrePiece,
                'Capacite' => $request->Capacite,
                'Disponiblite' => $request->Disponiblite ?? true,
                'nbrLocataire' => $request->nbrLocataire ?? 0,
                'IdProprietaire' => $request->IdProprietaire
            ]);

            return response()->json([
                'message' => 'Appartement créé avec succès.',
                'id' => $appartement->IdAppartement
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de l\'appartement: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la création de l\'appartement: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $appartement = DB::table('appartements')
                ->leftJoin('proprietaires', 'appartements.IdProprietaire', '=', 'proprietaires.IdPersonne')
                ->leftJoin('personnes', 'proprietaires.IdPersonne', '=', 'personnes.IdPersonne')
                ->select(
                    'appartements.*',
                    'personnes.Nom as NomProprietaire',
                    'personnes.Prenom as PrenomProprietaire'
                )
                ->where('appartements.IdAppartement', $id)
                ->first();

            if (!$appartement) {
                return response()->json(['erreur' => 'Appartement non trouvé'], 404);
            }

            return response()->json($appartement);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de l\'appartement: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la récupération de l\'appartement'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'AdresseAppartement' => 'sometimes|required|string|max:255',
            'Surface' => 'nullable|numeric|min:0',
            'NombrePiece' => 'nullable|integer|min:0',
            'Capacite' => 'sometimes|required|integer|min:1',
            'Disponiblite' => 'boolean',
            'nbrLocataire' => 'integer|min:0',
            'IdProprietaire' => 'nullable|exists:proprietaires,IdPersonne'
        ]);

        try {
            $appartement = Appartement::find($id);
            if (!$appartement) {
                return response()->json(['erreur' => 'Appartement non trouvé'], 404);
            }

            $appartement->update($request->all());

            return response()->json(['message' => 'Appartement mis à jour avec succès']);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de l\'appartement: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la mise à jour de l\'appartement'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $appartement = Appartement::find($id);
            if (!$appartement) {
                return response()->json(['erreur' => 'Appartement non trouvé'], 404);
            }

            $appartement->delete();

            return response()->json(['message' => 'Appartement supprimé avec succès']);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de l\'appartement: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la suppression de l\'appartement'], 500);
        }
    }

    /**
     * Obtenir les appartements disponibles
     */
    public function getDisponibles()
    {
        try {
            $appartements = DB::table('appartements')
                ->leftJoin('proprietaires', 'appartements.IdProprietaire', '=', 'proprietaires.IdPersonne')
                ->leftJoin('personnes', 'proprietaires.IdPersonne', '=', 'personnes.IdPersonne')
                ->select(
                    'appartements.*',
                    'personnes.Nom as NomProprietaire',
                    'personnes.Prenom as PrenomProprietaire'
                )
                ->where('appartements.Disponiblite', true)
                ->orderBy('appartements.AdresseAppartement')
                ->get();

            return response()->json($appartements);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des appartements disponibles: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la récupération des appartements disponibles'], 500);
        }
    }
} 