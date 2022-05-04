<?php

namespace App\Http\Controllers;

use App\Models\DetallesRespuestasEtapas;
use App\Models\DetallesRespuestasUsuario;
use App\Models\RespuestasUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RespuestasUsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            DB::beginTransaction();

            $idUser=Auth::user()->id;

            $respuestas_usuario=RespuestasUsuario::create([
                'id_usuario'=>$idUser,
                'id_formulario'=>$request->id_formulario,
                'fecha'=>date('Y-m-d'),
            ]);

            foreach($request->puntaje_etapa as $item){
                DetallesRespuestasEtapas::create([
                    'id_etapa'=>$item['id_etapa'],
                    'id_respuesta'=>$respuestas_usuario->id,
                    'puntaje_total'=>$item['puntaje'],
                ]);
            }

            foreach($request->respuestas as $item){
                DetallesRespuestasUsuario::create([
                    'id_pregunta'=>$item['id_pregunta'],
                    'id_respuesta'=>$respuestas_usuario->id,
                    'respuesta'=>$item['respuesta'],
                    'puntaje'=>$item['puntaje'],
                ]);
            }


            DB::commit();

            return response()->json($respuestas_usuario);

        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage().PHP_EOL.$ex->getTraceAsString());
            return response()->json(['status' => 'fail', 'msg' => 'Ha ocurrido un error al procesar la solicitud'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    public function showByIdUser($id,$id_formulario)
    {
        $respuestas= RespuestasUsuario::where('id_usuario',$id)->where('id_formulario',$id_formulario)
        ->with('detalles.pregunta.etapa','detalles_etapas.etapa')
        ->orderBy('fecha','desc')->first();

        return response()->json($respuestas);
    }

    public function showByIdForm($id_formulario)
    {
        $respuestas= RespuestasUsuario::where('id_formulario',$id_formulario)
        ->with(['detalles.pregunta.etapa','detalles_etapas.etapa','usuario.empresa'])
        ->get();

        return response()->json($respuestas);
    }

    public function showByIdFormulario($id)
    {
        $idUser=Auth::user()->id;
        $respuestas= RespuestasUsuario::where('id_formulario',$id)->where('id_usuario',$idUser)->get();
        return response()->json($respuestas);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
