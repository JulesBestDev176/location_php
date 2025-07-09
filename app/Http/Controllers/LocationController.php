<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Appartement;
use App\Models\Locataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $locations = DB::table('locations')
                ->leftJoin('appartements', 'locations.IdAppartement', '=', 'appartements.IdAppartement')
                ->leftJoin('locataires', 'locations.IdLocataire', '=', 'locataires.IdPersonne')
                ->leftJoin('personnes as locataire_personne', 'locataires.IdPersonne', '=', 'locataire_personne.IdPersonne')
                ->select(
                    'locations.*',
                    'appartements.AdresseAppartement',
                    'locataire_personne.Nom as NomLocataire',
                    'locataire_personne.Prenom as PrenomLocataire'
                )
                ->orderBy('locations.DateCreation', 'desc')
                ->get();

            return response()->json($locations);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des locations: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la récupération des locations'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'NumeroLocation' => 'required|string|max:50|unique:locations,NumeroLocation',
            'MontantLocation' => 'required|integer|min:0',
            'DateDebut' => 'required|date',
            'DateFin' => 'nullable|date|after:DateDebut',
            'DateCreation' => 'required|date',
            'Statut' => 'boolean',
            'IdAppartement' => 'required|exists:appartements,IdAppartement',
            'IdLocataire' => 'required|exists:locataires,IdPersonne'
        ]);

        try {
            $location = Location::create([
                'NumeroLocation' => $request->NumeroLocation,
                'MontantLocation' => $request->MontantLocation,
                'DateDebut' => $request->DateDebut,
                'DateFin' => $request->DateFin,
                'DateCreation' => $request->DateCreation,
                'Statut' => $request->Statut ?? true,
                'IdAppartement' => $request->IdAppartement,
                'IdLocataire' => $request->IdLocataire
            ]);

            // Mettre à jour le nombre de locataires de l'appartement
            $appartement = Appartement::find($request->IdAppartement);
            $appartement->increment('nbrLocataire');

            return response()->json([
                'message' => 'Location créée avec succès.',
                'id' => $location->IdLocation
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de la location: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la création de la location: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $location = DB::table('locations')
                ->leftJoin('appartements', 'locations.IdAppartement', '=', 'appartements.IdAppartement')
                ->leftJoin('locataires', 'locations.IdLocataire', '=', 'locataires.IdPersonne')
                ->leftJoin('personnes as locataire_personne', 'locataires.IdPersonne', '=', 'locataire_personne.IdPersonne')
                ->select(
                    'locations.*',
                    'appartements.AdresseAppartement',
                    'locataire_personne.Nom as NomLocataire',
                    'locataire_personne.Prenom as PrenomLocataire'
                )
                ->where('locations.IdLocation', $id)
                ->first();

            if (!$location) {
                return response()->json(['erreur' => 'Location non trouvée'], 404);
            }

            return response()->json($location);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de la location: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la récupération de la location'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'NumeroLocation' => 'sometimes|required|string|max:50|unique:locations,NumeroLocation,' . $id . ',IdLocation',
            'MontantLocation' => 'sometimes|required|integer|min:0',
            'DateDebut' => 'sometimes|required|date',
            'DateFin' => 'nullable|date|after:DateDebut',
            'DateCreation' => 'sometimes|required|date',
            'Statut' => 'boolean',
            'IdAppartement' => 'sometimes|required|exists:appartements,IdAppartement',
            'IdLocataire' => 'sometimes|required|exists:locataires,IdPersonne'
        ]);

        try {
            $location = Location::find($id);
            if (!$location) {
                return response()->json(['erreur' => 'Location non trouvée'], 404);
            }

            $location->update($request->all());

            return response()->json(['message' => 'Location mise à jour avec succès']);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de la location: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la mise à jour de la location'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $location = Location::find($id);
            if (!$location) {
                return response()->json(['erreur' => 'Location non trouvée'], 404);
            }

            // Décrémenter le nombre de locataires de l'appartement
            $appartement = Appartement::find($location->IdAppartement);
            if ($appartement && $appartement->nbrLocataire > 0) {
                $appartement->decrement('nbrLocataire');
            }

            $location->delete();

            return response()->json(['message' => 'Location supprimée avec succès']);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de la location: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la suppression de la location'], 500);
        }
    }
} 