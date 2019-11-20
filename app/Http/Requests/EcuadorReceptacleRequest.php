<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EcuadorReceptacleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'codigo_receptaculo' => 'required',
            'oficina_origen' => 'required',
            'oficina_destino' => 'required',
            'categoria' => 'required',
            'sub_clase' => 'required',
            'cantidad_paquetes' => 'required',
            'año' => 'required',
            'serie' => 'required',
            'posicion' => 'required',
            'peso' => 'required',
            'numero_guia_area' => 'required',
            'numero_despacho' => 'required',
            'fecha_despacho' => 'required',
            'lista_paquetes' => 'required|array'
        ];
    }
}
