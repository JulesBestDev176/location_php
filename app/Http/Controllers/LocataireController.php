<?php

namespace App\Http\Controllers;

use App\Models\Personne;
use App\Models\Locataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LocataireController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $locataires = DB::table('personnes')
                ->join('locataires', 'personnes.IdPersonne', '=', 'locataires.IdPersonne')
                ->select('personnes.*', 'locataires.CNI')
                ->orderBy('personnes.Nom')
                ->orderBy('personnes.Prenom')
                ->get();

            return response()->json($locataires);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des locataires: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la récupération des locataires'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'Nom' => 'required|string|max:50',
            'Prenom' => 'required|string|max:80',
            'Telephone' => 'required|string|max:20|unique:personnes,Telephone',
            'Email' => 'required|email|max:100|unique:personnes,Email',
            'CNI' => 'required|string|max:50|unique:locataires,CNI'
        ]);

        DB::beginTransaction();

        try {
            // Créer la personne
            $personne = Personne::create([
                'Nom' => $request->Nom,
                'Prenom' => $request->Prenom,
                'Telephone' => $request->Telephone,
                'Email' => $request->Email
            ]);

            // Créer le locataire
            $locataire = Locataire::create([
                'IdPersonne' => $personne->IdPersonne,
                'CNI' => $request->CNI
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Locataire créé avec succès.',
                'id' => $personne->IdPersonne
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du locataire: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la création du locataire: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $locataire = DB::table('personnes')
                ->join('locataires', 'personnes.IdPersonne', '=', 'locataires.IdPersonne')
                ->select('personnes.*', 'locataires.CNI')
                ->where('personnes.IdPersonne', $id)
                ->first();

            if (!$locataire) {
                return response()->json(['erreur' => 'Locataire non trouvé'], 404);
            }

            return response()->json($locataire);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du locataire: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la récupération du locataire'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'Nom' => 'sometimes|required|string|max:50',
            'Prenom' => 'sometimes|required|string|max:80',
            'Telephone' => 'sometimes|required|string|max:20|unique:personnes,Telephone,' . $id . ',IdPersonne',
            'Email' => 'sometimes|required|email|max:100|unique:personnes,Email,' . $id . ',IdPersonne',
            'CNI' => 'sometimes|required|string|max:50|unique:locataires,CNI,' . $id . ',IdPersonne'
        ]);

        DB::beginTransaction();

        try {
            $personne = Personne::find($id);
            if (!$personne) {
                return response()->json(['erreur' => 'Locataire non trouvé'], 404);
            }

            // Mettre à jour la personne
            $personne->update($request->only(['Nom', 'Prenom', 'Telephone', 'Email']));

            // Mettre à jour le locataire
            $locataire = Locataire::find($id);
            $locataire->update($request->only(['CNI']));

            DB::commit();

            return response()->json(['message' => 'Locataire mis à jour avec succès']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du locataire: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la mise à jour du locataire'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $personne = Personne::find($id);
            if (!$personne) {
                return response()->json(['erreur' => 'Locataire non trouvé'], 404);
            }

            $personne->delete(); // Cela supprimera aussi le locataire grâce à la contrainte CASCADE

            DB::commit();

            return response()->json(['message' => 'Locataire supprimé avec succès']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du locataire: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la suppression du locataire'], 500);
        }
    }
} 