<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ville;
use Illuminate\Http\Request;

class VilleController extends Controller
{
    public function getAllVilles()
    {
        $villes=Ville::get();
        return response()->json($villes);
    }
}
