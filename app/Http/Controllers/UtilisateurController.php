<?php

namespace App\Http\Controllers;

use App\Models\Personne;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UtilisateurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $utilisateurs = DB::table('personnes')
                ->join('utilisateurs', 'personnes.IdPersonne', '=', 'utilisateurs.IdPersonne')
                ->select('personnes.*', 'utilisateurs.Identifiant', 'utilisateurs.profil', 'utilisateurs.Statut')
                ->orderBy('personnes.Nom')
                ->orderBy('personnes.Prenom')
                ->get();

            return response()->json($utilisateurs);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des utilisateurs: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la récupération des utilisateurs'], 500);
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
            'MotDePasse' => 'required|string|min:8|regex:/^(?=.*[A-Z])(?=.*\d)/',
            'profil' => 'nullable|string|max:50',
            'Statut' => 'nullable|string|max:50'
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
                'MotDePasse' => Hash::make($request->MotDePasse),
                'profil' => $request->profil ?? 'Utilisateur',
                'Statut' => $request->Statut ?? 'Actif'
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Utilisateur créé avec succès.',
                'id' => $personne->IdPersonne
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de l\'utilisateur: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la création de l\'utilisateur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $utilisateur = DB::table('personnes')
                ->join('utilisateurs', 'personnes.IdPersonne', '=', 'utilisateurs.IdPersonne')
                ->select('personnes.*', 'utilisateurs.Identifiant', 'utilisateurs.profil', 'utilisateurs.Statut')
                ->where('personnes.IdPersonne', $id)
                ->first();

            if (!$utilisateur) {
                return response()->json(['erreur' => 'Utilisateur non trouvé'], 404);
            }

            return response()->json($utilisateur);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de l\'utilisateur: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la récupération de l\'utilisateur'], 500);
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
            'Identifiant' => 'sometimes|required|string|max:50|unique:utilisateurs,Identifiant,' . $id . ',IdPersonne',
            'profil' => 'nullable|string|max:50',
            'Statut' => 'nullable|string|max:50'
        ]);

        DB::beginTransaction();

        try {
            $personne = Personne::find($id);
            if (!$personne) {
                return response()->json(['erreur' => 'Utilisateur non trouvé'], 404);
            }

            // Mettre à jour la personne
            $personne->update($request->only(['Nom', 'Prenom', 'Telephone', 'Email']));

            // Mettre à jour l'utilisateur
            $utilisateur = Utilisateur::find($id);
            $utilisateur->update($request->only(['Identifiant', 'profil', 'Statut']));

            DB::commit();

            return response()->json(['message' => 'Utilisateur mis à jour avec succès']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de l\'utilisateur: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la mise à jour de l\'utilisateur'], 500);
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
                return response()->json(['erreur' => 'Utilisateur non trouvé'], 404);
            }

            $personne->delete(); // Cela supprimera aussi l'utilisateur grâce à la contrainte CASCADE

            DB::commit();

            return response()->json(['message' => 'Utilisateur supprimé avec succès']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de l\'utilisateur: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la suppression de l\'utilisateur'], 500);
        }
    }

    /**
     * Rechercher un utilisateur par email
     */
    public function getByEmail($email)
    {
        try {
            $utilisateur = DB::table('personnes')
                ->join('utilisateurs', 'personnes.IdPersonne', '=', 'utilisateurs.IdPersonne')
                ->select('personnes.*', 'utilisateurs.Identifiant', 'utilisateurs.profil', 'utilisateurs.Statut')
                ->where('personnes.Email', $email)
                ->first();

            if (!$utilisateur) {
                return response()->json(['erreur' => 'Utilisateur non trouvé pour cet email'], 404);
            }

            return response()->json($utilisateur);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la recherche par email: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la recherche par email'], 500);
        }
    }

    /**
     * Rechercher des utilisateurs par identifiant (recherche partielle)
     */
    public function getByIdentifiant($identifiant)
    {
        try {
            $utilisateurs = DB::table('personnes')
                ->join('utilisateurs', 'personnes.IdPersonne', '=', 'utilisateurs.IdPersonne')
                ->select('personnes.*', 'utilisateurs.Identifiant', 'utilisateurs.profil', 'utilisateurs.Statut')
                ->where('utilisateurs.Identifiant', 'LIKE', '%' . $identifiant . '%')
                ->get();

            return response()->json($utilisateurs);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la recherche par identifiant: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la recherche par identifiant'], 500);
        }
    }

    /**
     * Rechercher un utilisateur par identifiant exact
     */
    public function getUserByIdentifiant($identifiant)
    {
        try {
            $utilisateur = DB::table('personnes')
                ->join('utilisateurs', 'personnes.IdPersonne', '=', 'utilisateurs.IdPersonne')
                ->select('personnes.*', 'utilisateurs.Identifiant', 'utilisateurs.profil', 'utilisateurs.Statut')
                ->where('utilisateurs.Identifiant', $identifiant)
                ->first();

            if (!$utilisateur) {
                return response()->json(['erreur' => 'Utilisateur non trouvé'], 404);
            }

            return response()->json($utilisateur);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la recherche par identifiant exact: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la recherche par identifiant exact'], 500);
        }
    }

    /**
     * Obtenir le nombre total d'utilisateurs
     */
    public function count()
    {
        try {
            $count = Utilisateur::count();
            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
            Log::error('Erreur lors du comptage des utilisateurs: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors du comptage des utilisateurs'], 500);
        }
    }

    /**
     * Obtenir l'administrateur
     */
    public function getAdmin()
    {
        try {
            $admin = DB::table('personnes')
                ->join('utilisateurs', 'personnes.IdPersonne', '=', 'utilisateurs.IdPersonne')
                ->select('personnes.*', 'utilisateurs.Identifiant', 'utilisateurs.profil', 'utilisateurs.Statut')
                ->where('utilisateurs.profil', 'Administrateur')
                ->first();

            if (!$admin) {
                return response()->json(['erreur' => 'Administrateur non trouvé'], 404);
            }

            return response()->json($admin);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de l\'administrateur: ' . $e->getMessage());
            return response()->json(['erreur' => 'Erreur lors de la récupération de l\'administrateur'], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'Identifiant' => 'required|string',
            'MotDePasse' => 'required|string',
        ]);

        $utilisateur = DB::table('utilisateurs')
            ->join('personnes', 'utilisateurs.IdPersonne', '=', 'personnes.IdPersonne')
            ->where('utilisateurs.Identifiant', $request->Identifiant)
            ->select('utilisateurs.*', 'personnes.Nom', 'personnes.Prenom', 'personnes.Email', 'personnes.Telephone')
            ->first();

        if (!$utilisateur) {
            Log::info('Login échoué : identifiant inexistant', ['identifiant' => $request->Identifiant]);
            return response()->json(['erreur' => 'Identifiant ou mot de passe incorrect'], 401);
        }

        if (!Hash::check($request->MotDePasse, $utilisateur->MotDePasse)) {
            Log::info('Login échoué : mauvais mot de passe', ['identifiant' => $request->Identifiant]);
            return response()->json(['erreur' => 'Identifiant ou mot de passe incorrect'], 401);
        }

        Log::info('Login réussi', ['identifiant' => $request->Identifiant]);
        return response()->json(['message' => 'Connexion réussie', 'user' => $utilisateur]);
    }
} 