<?php

namespace App\Http\Controllers;

use App\Models\Personne;
use App\Models\Utilisateur;
use App\Models\Administrateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdministrateurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $administrateurs = DB::table('personnes')
                ->join('utilisateurs', 'personnes.IdPersonne', '=', 'utilisateurs.IdPersonne')
                ->join('administrateurs', 'personnes.IdPersonne', '=', 'administrateurs.IdPersonne')
                ->select('personnes.*', 'utilisateurs.Identifiant', 'utilisateurs.profil', 'utilisateurs.Statut')
                ->orderBy('personnes.Nom')
                ->orderBy('personnes.Prenom')
                ->get();

            return response()->json($administrateurs);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des administrateurs: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la récupération des administrateurs'], 500);
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
            'Identifiant' => 'required|string|max:50|unique:utilisateurs,Identifiant',
            'MotDePasse' => 'required|string|min:8|regex:/^(?=.*[A-Z])(?=.*\d)/'
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

            // Créer l'utilisateur
            $utilisateur = Utilisateur::create([
                'IdPersonne' => $personne->IdPersonne,
                'Identifiant' => $request->Identifiant,
                'MotDePasse' => bcrypt($request->MotDePasse),
                'profil' => 'Administrateur',
                'Statut' => 'Actif'
            ]);

            // Créer l'administrateur
            $administrateur = Administrateur::create([
                'IdPersonne' => $personne->IdPersonne
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Administrateur créé avec succès.',
                'id' => $personne->IdPersonne
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de l\'administrateur: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la création de l\'administrateur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $administrateur = DB::table('personnes')
                ->join('utilisateurs', 'personnes.IdPersonne', '=', 'utilisateurs.IdPersonne')
                ->join('administrateurs', 'personnes.IdPersonne', '=', 'administrateurs.IdPersonne')
                ->select('personnes.*', 'utilisateurs.Identifiant', 'utilisateurs.profil', 'utilisateurs.Statut')
                ->where('personnes.IdPersonne', $id)
                ->first();

            if (!$administrateur) {
                return response()->json(['erreur' => 'Administrateur non trouvé'], 404);
            }

            return response()->json($administrateur);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de l\'administrateur: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la récupération de l\'administrateur'], 500);
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
            'Identifiant' => 'sometimes|required|string|max:50|unique:utilisateurs,Identifiant,' . $id . ',IdPersonne'
        ]);

        DB::beginTransaction();

        try {
            $personne = Personne::find($id);
            if (!$personne) {
                return response()->json(['erreur' => 'Administrateur non trouvé'], 404);
            }

            // Mettre à jour la personne
            $personne->update($request->only(['Nom', 'Prenom', 'Telephone', 'Email']));

            // Mettre à jour l'utilisateur
            $utilisateur = Utilisateur::find($id);
            $utilisateur->update($request->only(['Identifiant']));

            DB::commit();

            return response()->json(['message' => 'Administrateur mis à jour avec succès']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de l\'administrateur: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la mise à jour de l\'administrateur'], 500);
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
                return response()->json(['erreur' => 'Administrateur non trouvé'], 404);
            }

            $personne->delete(); // Cela supprimera aussi l'administrateur grâce à la contrainte CASCADE

            DB::commit();

            return response()->json(['message' => 'Administrateur supprimé avec succès']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de l\'administrateur: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la suppression de l\'administrateur'], 500);
        }
    }
} 