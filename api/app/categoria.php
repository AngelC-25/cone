<?php

namespace App;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class categoria extends Model
{
    //use Has Factory;
    protected $table = "categoria";
    protected $primaryKey = 'Codigo';
    public $timestamps = false;
    protected $fillable = [
        'Codigo',
        'CodigoEmpresa',
        'Nombre',
        'Descripcion',
        'Vigencia'
    ];
}
