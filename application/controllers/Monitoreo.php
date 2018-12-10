<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Monitoreo extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Your own constructor code
        //header('Access-Control-Allow-Origin: *');
        $this->load->model('monitoreo_model');
    }

    public function index()
    {

        $this->load->view('monitoreo');
    }
    
    public function medicion()
    {

        //test url: http://localhost/arduino/monitoreo/medicion?bpm=2&spo2=2&psi=2&volumen=2&estado=1
        $bpm = $this->input->get('bpm');
        $spo2 = $this->input->get('spo2');
        $psi = $this->input->get('psi');
        $volumen = $this->input->get('volumen');
        $estado = $this->input->get('estado');
        $valido = 0;
        $valido += $this->validaEntero($bpm);
        $valido += $this->validaEntero($spo2);
        $valido += $this->validaEntero($psi);
        $valido += $this->validaEntero($volumen);
        $valido += $this->validaEntero($estado);
        $valido += ( $value = 0 || $value = 1 ? 0 : 1 );
        if( $valido == 0 ){
            $medicion = array(
                'bpm' => (integer) $bpm,
                'spo2' => (integer) $spo2,
                'psi' => (integer) $psi,
                'volumen' => (integer) $volumen,
                'estado' => (integer) $estado
            );
            $this->monitoreo_model->addMedicion($medicion);
            $parametros = $this->monitoreo_model->getParametro();
            $trama = '';
            $trama .= 'X' . $parametros->bpmmin;
            $trama .= 'X' . strval($parametros->bpmmax);
            $trama .= 'X' . strval($parametros->spo2min);
            $trama .= 'X' . strval($parametros->spo2max);
            $trama .= 'X' . strval($parametros->volumenmax);
            $trama .= 'X' . $estado;
            echo $trama; 
        }else{
            echo "Trama incorrecta";   
        }        
    }

    public function validaEntero($value)
    {
        $value = (integer) $value;
        return ( $value >= 0 ? 0 : 1 );
        
    }
}
