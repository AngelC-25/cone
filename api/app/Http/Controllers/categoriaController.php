<?php

namespace App\Http\Controllers;

use App\Categoria;
use Illuminate\Http\Request;
//use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;



class CategoriaController extends Controller
{
    public function listar()
    {
        return Categoria::all ('Codigo', 'CodigoEmpresa', 'Nombre', 'Descripcion','Vigencia');

    }
    public function leer($id)
    {
        return Categoria::find($id);
    }


    public function mostrarCategorias()
    {
        return Categoria::all();
    }

    //public function tablaCategoria()
    //{
    //    return categoria::all('Codigo', 'CodigoEmpresa', 'Nombre', 'Descripcion', 'Vigencia');
    //}
   

    public function registrar(Request $request)
    {
        /* return response()->json($request->get("Nombre")); */
        $validacion = Validator::make($request->all(), [
            //'CodigoEmpresa' => 'max:6',
            'Nombre' => 'required|max:100',
            'Descripcion' => 'required|max:100',
        ], [
            'required' => ':attribute es obligatorio',
            'max' => ':attribute llego al limite de letras'
        ]);
        if ($validacion->fails()) {
            return response()->json($validacion->errors()->first(), 400);
        }
        $categoria = new Categoria();

        $categoria->CodigoEmpresa = $request->get("CodigoEmpresa");
        $categoria->Nombre = $request->get('Nombre');
        $categoria->Descripcion = $request->get('Descripcion');
        $categoria->Vigencia = 1;
        $categoria->save();


        return response()->json($categoria, 201);
    }

    public function actualizar(Request $request, $id)
    {
        $validacion = Validator::make($request->all(), [
            //'CodigoEmpresa' => 'required|max:6',
            'Nombre' => 'required|max:100',
            'Descripcion' => 'required|max:100',
        ]);
        if ($validacion->fails()) {
            return response()->json($validacion, 400);
        }
        $categoria = Categoria::findOrFail($id);
        //$categoria->CodigoEmpresa = $request->get('CodigoEmpresa');
        $categoria->Nombre = $request->get('Nombre');
        $categoria->Descripcion = $request->get('Descripcion');
        $categoria->update();
        return response()->json($categoria, 200);
    }

    public function cambiarVigencia($id)
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->Vigencia = !$categoria->Vigencia;
        $categoria->save();

        return response()->json($categoria, 200);
    }
}
