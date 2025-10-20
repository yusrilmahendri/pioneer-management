<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AddBusines;
use App\Http\Resources\Bisnis\BisnisCollection; 

class BusinesController extends Controller
{
    public function __construct(){
        $this->middleware('auth:sanctum');
    }  

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);

        $query = AddBusines::with(['categoryBusines', 'statusBusines']);

        if ($search = $request->query('search')) {
            $query->where('name_busines', 'like', "%{$search}%");
        }

        $paginator = $query->paginate($perPage);

        return new BisnisCollection($paginator);
    }
}
