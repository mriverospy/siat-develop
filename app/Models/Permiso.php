<?php

namespace App\Models;

use Shanmuga\LaravelEntrust\Models\EntrustPermission;
use Venturecraft\Revisionable\RevisionableTrait;

class Permiso extends EntrustPermission
{
    use RevisionableTrait;

    protected $revisionCreationsEnabled = true;

    protected $fillable = ['name', 'display_name', 'description'];
}
