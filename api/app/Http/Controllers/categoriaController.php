<?php

namespace App\Http\Controllers;

use App\categoria;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class categoriaController extends Controller
{
    public function inicio()
    {
        $categoria = DB::table('categoria as c')
            ->select('c.Nombre', 'c.CodigoEmpresa', 'c.Descripcion')
            ->where('c.Vigencia', '=', 1)
            ->get();
        return response()->json($categoria, 200);
    }


    public function mostrarCategorias()
    {
        return categoria::all();
    }

    public function tablaCategoria()
    {
        return categoria::all('Codigo', 'CodigoEmpresa', 'Nombre', 'Descripcion', 'Vigencia');
    }

    public function mostrar($id)
    {
        return categoria::find($id);
    }

    public function registrar(Request $request)
    {
        $validacion = Validator::make($request->all(), [
            'CodigoEmpresa' => 'max:6',
            'Nombre' => 'required|max:100',
            'Descripcion' => 'required|max:100',
        ], [
            'required' => ':attribute es obligatorio',
            'max' => ':attribute llego al limite de letras'
        ]);
        if ($validacion->fails()) {
            return response()->json($validacion->errors()->first(), 400);
        }
        $categoria = new categoria();

        $categoria->CodigoEmpresa = $request->get('CodigoEmpresa');
        $categoria->Nombre = $request->get('Nombre');
        $categoria->Descripcion = $request->get('Descripcion');
        $categoria->save();


        return response()->json($categoria, 201);
    }

    public function actualizar(Request $request, $id)
    {
        $validacion = Validator::make($request->all(), [
            'CodigoEmpresa' => 'required|max:6',
            'Nombre' => 'required|max:100',
            'Descripcion' => 'required|max:100',
        ]);
        if ($validacion->fails()) {
            return response()->json($validacion, 400);
        }
        $categoria = categoria::findOrFail($id);
        $categoria->CodigoEmpresa = $request->get('CodigoEmpresa');
        $categoria->Nombre = $request->get('Nombre');
        $categoria->Descripcion = $request->get('Descripcion');
        $categoria->save();
        return response()->json($categoria, 200);
    }

    public function eliminar($id)
    {
        $categoria = categoria::findOrFail($id);
        $categoria->Vigencia = 0;
        $categoria->save();

        return response()->json($categoria, 200);
    }

    public function cambiarEstado($id)
    {
        $categoria = categoria::findOrFail($id);
        $categoria->Vigencia = !$categoria->Vigencia;
        $categoria->save();

        return response()->json($categoria, 200);
    }

    //public function index(Request $request)
    //{
    //    $name = $request->get('name');
    //    $name = $request->get('name');      
    //    $name = $request->get('name');   

    //    $categoria = categoria::orderBy('')
    //}
}
