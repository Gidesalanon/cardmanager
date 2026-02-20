<?php

namespace App\Services\Import\Models;

class StudentData
{
    public function __construct(
    public ?string $photo = null,
    public ?string $matricule = null,
    public ?string $nom = null,
    public ?string $prenom = null,
    public ?string $sexe = null,
    public ?string $dateNaissance = null,
    public ?string $lieuNaissance = null,
    public ?string $nationalite = null,
    public ?string $option = null,
    public ?string $centre = null,
    public ?string $etablissement = null,
    public ?string $salle = null,
    public ?string $numeroTable = null,
    public ?string $eps = null,
    public ?string $observation = null,
    public ?array $collegesChoisis = null,
    public ?string $telephoneTuteur = null, 
) {}


    public function toArray(): array
    {
        return [
            'photo' => $this->photo,
            'matricule' => $this->matricule,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'sexe' => $this->sexe,
            'date_naissance' => $this->dateNaissance,
            'lieu_naissance' => $this->lieuNaissance,
            'nationalite' => $this->nationalite,
            'option' => $this->option,
            'centre' => $this->centre,
            'etablissement' => $this->etablissement,
            'salle' => $this->salle,
            'numero_table' => $this->numeroTable,
            'eps' => $this->eps,
            'observation' => $this->observation,
            'colleges_choisis' => $this->collegesChoisis,
            'telephone_tuteur' => $this->telephoneTuteur,
        ];
    }

    public function toDatabaseFormat(): array
    {
        return [
            'photo' => $this->photo,
            'matricule' => $this->matricule,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'sexe' => $this->sexe,
            'date_naissance' => $this->dateNaissance,
            'lieu_naissance' => $this->lieuNaissance,
            'telephone_tuteur' => null, 
        ];
    }
}
