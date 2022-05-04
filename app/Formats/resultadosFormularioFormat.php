<?php

namespace App\Formats;

use App\Formats\MultiCellTable;
use App\Models\Empresa;
use App\Models\SFormulario;
use App\Models\SRespuestasUsuario;
use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Support\Facades\Log;
use NumberFormatter;

class resultadosFormularioFormat extends Fpdf {

    use MultiCellTable;

    protected $respuestas;
    protected $formatter;
    protected $formulario;
    protected $company;
    protected $MarginLeft = 15;
    protected $MarginRight = 15;
    protected $cellHeigth = 5;
    protected $fontSize = 8;
    protected $fontColor = [0, 0, 0];
    protected $fontFamily = 'Helvetica';

    public function __construct($idRespuesta)
    {
        $this->respuestas = SRespuestasUsuario::where('id',$idRespuesta)
        ->with(['detalles.pregunta.seccion','detalles_secciones.seccion'])->first();
        parent::__construct('P', 'mm', 'Letter');
        $this->make();
    }

    public function header(){

        $this->company = Empresa::where('id_usuario',$this->respuestas->id_usuario)
        ->with(['sector','trabajador'])->first();

        $this->formulario = SFormulario::where('id',$this->respuestas->id_formulario)
        ->first();

        $x = $this->MarginLeft;
        $this->cellHeigth = 4;
        $this->fontSize = 6;

        // Logo
        //$this->Image(public_path('/img/icon.png'), $this->MarginLeft, 9, 18);
        // Arial bold 15



        $this->SetFont($this->fontFamily, 'BI', 9);
        $this->SetX($this->MarginLeft);
        $this->SetDrawColor(130, 130, 130);
        $this->SetFillColor(222,222,222);
        $this->SetTextColor(0, 0, 0);

        $this->Cell(0, 5, utf8_decode("Respuestas Formulario : ".$this->formulario->nombre), 0, 1, "C", true);
        $this->Ln(2);

        $this->SetX($x);
        $this->Cell(
            0,
            $this->cellHeigth,
            utf8_decode('Razón Social : '. $this->company->nombre),
            0,
            1,
            'L',
            false
        );

        $this->SetTextColor(0, 0, 0);
        $this->SetX($x);
        $this->Cell(
            80,
            $this->cellHeigth,
            utf8_decode("NIT : {$this->company->nit}"),
            0,
            1,
            'L',
            false
        );

        $this->SetTextColor(0, 0, 0);
        $this->SetX($x);
        $this->Cell(
            80,
            $this->cellHeigth,
            utf8_decode("Dirección : {$this->company->direccion}"),
            0,
            1,
            'L',
            false
        );


        $this->SetTextColor(0, 0, 0);
        $this->SetX($x);
        $this->Cell(
            80,
            $this->cellHeigth,
            utf8_decode("Teléfono : {$this->company->telefono}"),
            0,
            1,
            'L',
            false
        );

        $this->SetTextColor(0, 0, 0);
        $this->SetX($x);
        $this->Cell(
            80,
            $this->cellHeigth,
            utf8_decode("Email : {$this->company->email}"),
            0,
            1,
            'L',
            false
        );

        $this->SetTextColor(0, 0, 0);
        $this->SetX($x);
        $this->Cell(
            80,
            $this->cellHeigth,
            utf8_decode("Representante legal : {$this->company->representante}"),
            0,
            1,
            'L',
            false
        );

        $this->Ln();
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 7);
        $this->Cell(0, 5, utf8_decode('Página ') . $this->PageNo() . ' de {nb}', 0, 0, 'C');
        $this->ln(3);
        $this->Cell(0, 5, utf8_decode("Impreso por ParquesoftSucre"), 0, 1, "C");
    }

    public function make(){

        $this->AliasNbPages();
        $this->AddPage('P');
        $this->Image(
            storage_path("app/{$this->respuestas->id}.png"),
            ($this->GetPageWidth() / 2) - (150/2),
            $this->GetY(),
            150,
            150
        );
        $this->SetY($this->GetY() + 140);
        $this->SetFont('Arial', '', 8);

        $this->SetFillColor(222, 222, 222);
        $this->SetDrawColor(188, 188, 188);
        $this->SetTextColor(0, 0, 0);

        $this->SetFont('Arial', '', 8);

        $categorizacion_nivel = [
            ['start' => 0 , 'end' => 1.95, 'cat' => 'BAJO'],
            ['start' => 1.95 , 'end' => 2.95, 'cat' => 'BAJO - MEDIO'],
            ['start' => 2.95, 'end' => 3.95, 'cat' => 'MEDIO'],
            ['start' => 3.95 , 'end' => 4.95, 'cat' => 'MEDIO - ALTO'],
            ['start' => 4.95 , 'end' => 5, 'cat' => 'ALTO'],
        ];

        $categorizacion_nivel = collect($categorizacion_nivel);

        foreach($this->respuestas->detalles_secciones as $seccion){

            $nivel = $categorizacion_nivel->filter(function($value) use ($seccion){
                return  doubleval($seccion->puntaje_total/$seccion->seccion->numero_preguntas) > $value['start']  && doubleval($seccion->puntaje_total/$seccion->seccion->numero_preguntas) <= $value['end'];
            })->first();

            $this->SetFont('Arial', '', 9);
            $this->ln();
            $this->Cell(0, 5, utf8_decode($seccion->seccion->nombre), 1, 0, "C", true);
            $this->ln();
            $this->Cell(101, 5, utf8_decode("Puntaje Obtenido"), 1);
            $this->Cell(95, 5, utf8_decode($seccion->puntaje_total." puntos de ".$seccion->seccion->puntaje_maximo." posibles"), 1);
            $this->ln();
            $this->Cell(101, 5, utf8_decode("Total de preguntas"), 1);
            $this->Cell(95, 5, utf8_decode($seccion->seccion->numero_preguntas), 1);
            $this->ln();
            $this->Cell(101, 5, utf8_decode("Promedio"), 1);
            $this->Cell(95, 5, utf8_decode(number_format($seccion->puntaje_total/$seccion->seccion->numero_preguntas,2)), 1);
            $this->ln();
            $this->Cell(101, 5, utf8_decode("Nivel"), 1);
            $this->Cell(95, 5, utf8_decode($nivel['cat']), 1);
            $this->ln();
        }
        $this->Ln(5);

        $tableWidths =  [146, 15, 15, 20];
        $alings = ['L', 'C', 'C', 'C'];
        $this->SetWidths($tableWidths);
        $this->SetAligns($alings);



        $tableHeaders = ['Pregunta', 'N. Act', 'N. Des', 'Estado'];

        $this->Ln();

        $categorizacion = [
            ['start' => 0 , 'end' => 1, 'cat' => 'BAJO'],
            ['start' => 1 , 'end' => 2, 'cat' => 'BAJO - MEDIO'],
            ['start' => 2, 'end' => 3, 'cat' => 'MEDIO'],
            ['start' => 3 , 'end' => 4, 'cat' => 'MEDIO - ALTO'],
            ['start' => 4 , 'end' => 5, 'cat' => 'ALTO'],
        ];

        $categorizacion = collect($categorizacion);

        foreach($this->respuestas->detalles_secciones as $seccion){

            $this->SetFont('Arial', 'B', 8);

            $this->Cell(0,5,utf8_decode($seccion->seccion->nombre), 'B', 1, 'L');
            $this->ln();

            for ($i = 0; $i < count($tableHeaders); $i++) $this->Cell($tableWidths[$i], 5, utf8_decode($tableHeaders[$i]), 1, 0, 'C', true);
            $this->ln();

            $detallesFiltrados = $this->respuestas->detalles->filter(function($value) use ($seccion){
                return $value['pregunta']['id_seccion'] === $seccion->seccion->id;
            });

            $details = [];

            foreach($detallesFiltrados as $detalle){

                $categoria = $categorizacion->filter(function($value) use ($detalle){
                    return  intval($detalle->respuesta) > $value['start']  && intval($detalle->respuesta) <= $value['end'];
                })->first();

                $details[] = [
                    utf8_decode($detalle->pregunta->nombre),
                    $detalle->respuesta,
                    utf8_decode($seccion->seccion->nivel_deseado),
                    $categoria['cat']
                ];
            }

            $this->SetFont('Arial', '', 8);

            foreach ($details as $d) {
                $this->Row($d);
            }

            $this->ln();
        }


        $this->Ln(28);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(0, 5, utf8_decode("INTERPRETACIONES "), 1, 1, "B", true);
        $this->MultiCell(0,5,utf8_decode($this->respuestas->interpretacion), 1);

       /*  $this->SetFont('Arial', '', 8);

        $this->Ln();
        $this->Cell(40, 10, "", 0, 0);
        $this->Cell(52, 10, "", "B", 0, "C");
        $this->Cell(20, 10, "", 0, 0);
        $this->Cell(52, 10, "", "B", 0, "C");
        $this->Ln(10);
        $this->Cell(40, 10, "", 0, 0);
        $this->Cell(52, 10, "Autorizado", 0, 0, "C");
        $this->Cell(20, 10, "", 0, 0);
        $this->Cell(52, 10, "Procesado por : " , 0, 0, "C");
        $this->Ln(10); */



    }

    public function print(){
        $this->Output();
        exit;
    }

}
