<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Role extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */

    /**
     * Este método se llama cuando se inicia el modelo de usuario.
     * Establece el role_id del usuario en función de la existencia de un usuario con role_id 1.
     * Si ya existe un usuario con role_id 1, al nuevo usuario se le asignará role_id 2.
     * En caso contrario, al nuevo usuario se le asignará role_id 1.
     *
     * @return void
     */
}
