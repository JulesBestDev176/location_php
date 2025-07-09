<?php

namespace App\Http\Controllers;

use App\Models\Personne;
use App\Models\Proprietaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProprietaireController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $proprietaires = DB::table('personnes')
                ->join('proprietaires', 'personnes.IdPersonne', '=', 'proprietaires.IdPersonne')
                ->select('personnes.*', 'proprietaires.Ninea', 'proprietaires.Rccm')
                ->orderBy('personnes.Nom')
                ->orderBy('personnes.Prenom')
                ->get();

            return response()->json($proprietaires);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des propriétaires: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la récupération des propriétaires'], 500);
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
            'Ninea' => 'required|string|max:50',
            'Rccm' => 'required|string|max:50'
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

            // Créer le propriétaire
            $proprietaire = Proprietaire::create([
                'IdPersonne' => $personne->IdPersonne,
                'Ninea' => $request->Ninea,
                'Rccm' => $request->Rccm
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Propriétaire créé avec succès.',
                'id' => $personne->IdPersonne
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du propriétaire: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la création du propriétaire: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $proprietaire = DB::table('personnes')
                ->join('proprietaires', 'personnes.IdPersonne', '=', 'proprietaires.IdPersonne')
                ->select('personnes.*', 'proprietaires.Ninea', 'proprietaires.Rccm')
                ->where('personnes.IdPersonne', $id)
                ->first();

            if (!$proprietaire) {
                return response()->json(['erreur' => 'Propriétaire non trouvé'], 404);
            }

            return response()->json($proprietaire);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du propriétaire: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la récupération du propriétaire'], 500);
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
            'Ninea' => 'sometimes|required|string|max:50',
            'Rccm' => 'sometimes|required|string|max:50'
        ]);

        DB::beginTransaction();

        try {
            $personne = Personne::find($id);
            if (!$personne) {
                return response()->json(['erreur' => 'Propriétaire non trouvé'], 404);
            }

            // Mettre à jour la personne
            $personne->update($request->only(['Nom', 'Prenom', 'Telephone', 'Email']));

            // Mettre à jour le propriétaire
            $proprietaire = Proprietaire::find($id);
            $proprietaire->update($request->only(['Ninea', 'Rccm']));

            DB::commit();

            return response()->json(['message' => 'Propriétaire mis à jour avec succès']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du propriétaire: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la mise à jour du propriétaire'], 500);
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
                return response()->json(['erreur' => 'Propriétaire non trouvé'], 404);
            }

            $personne->delete(); // Cela supprimera aussi le propriétaire grâce à la contrainte CASCADE

            DB::commit();

            return response()->json(['message' => 'Propriétaire supprimé avec succès']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du propriétaire: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la suppression du propriétaire'], 500);
        }
    }
} 