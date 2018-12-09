<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pulso extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Your own constructor code
        header('Access-Control-Allow-Origin: *');
        $this->load->model('modelo');
    }

    public function index()
    {

        $this->load->view('inicio');
    }
    public function recarga()
    {

        $this->load->view('respuesta');
    }
    public function leer()
    {
        #  Se lee el archivo 'r'
        $archivo = fopen('d:\\pulso_v2.txt', 'r');

        #  Se juntan los datos en un solo string
        //$mostrar = fgets($leer);
        #  Se separan los datos por medio de la condicion puesta ',' en este caso
        //  $datos = explode(",", $mostrar);
        //  var_dump($datos);
        $data = array();
        $salida = '';
        while (!feof($archivo)) {

            $linea = fgets($archivo);
            if (strlen($linea) > 2) {
                $id = 0;
                $fila = array();
                $fila['fecha'] = substr($linea, 0, 17);
                $fila['SN'] = substr($linea, 21, 10);
                $fila['saturacion'] = substr($linea, 37, 3);
                if ($fila['saturacion'] == '---') {
                    $fila['saturacion'] = '';
                }
                $fila['BPM'] = substr($linea, 46, 3);
                if ($fila['BPM'] == '---') {
                    $fila['BPM'] = '';
                }

                $fila['PI'] = substr($linea, 53, 5);
                if ($fila['PI'] == '--.--') {
                    $fila['PI'] = '';
                }
                $fila['SPCO'] = substr($linea, 65, 4);
                if ($fila['SPCO'] == '--.-') {
                    $fila['SPCO'] = '';
                }
                $fila['SPMET'] = substr($linea, 77, 4);
                if ($fila['SPMET'] == '--.-') {
                    $fila['SPMET'] = '';
                }
                $fila['DESAT'] = substr($linea, 89, 2);
                if ($fila['DESAT'] == '--') {
                    $fila['DESAT'] = '';
                }
                $fila['PIDELTA'] = substr($linea, 100, 3);
                if ($fila['PIDELTA'] == '+--') {
                    $fila['PIDELTA'] = '';
                }
                $fila['ALARM'] = substr($linea, 110, 4);

                $fila['EXC'] = substr($linea, 119, 6);
                $data[] = $fila;
                $id = $this->modelo->guardar($fila);
                if ($id > 1) {
                    $salida = $salida . "$id -- ";
                }

            }

        }

        fclose($archivo);
        echo time() . "ID:" . $salida;
        // print_r($data);
        $this->escribir();
        $this->procesar();
    }

    public function escribir()
    {
        $nombre_archivo = "d:\\pulso_v2.txt";
        $hora = date("d/m/y H:i:s");
        $sn = '0000000000';
        $spo2 = substr('000' . rand(80, 100), -3);
        $bpm = substr('000' . rand(60, 80), -3);
        $pi = substr('00000' . rand(5, 7) . '.' . rand(10, 99), -4);
        $datos = file($nombre_archivo);
        $mensaje = "$hora SN=$sn SPO2=$spo2% BPM=$bpm PI=0$pi% SPCO=--.-% SPMET=--.-% DESAT=-- PIDELTA=+-- ALARM=0000 EXC=000800";
        if (count($datos) < 10) {
            if ($archivo = fopen($nombre_archivo, "a")) {
                if (fwrite($archivo, $mensaje . "\n")) {
                    echo "$hora Se ha ejecutado correctamente";
                } else {
                    echo "Ha habido un problema al crear el archivo";
                }

                fclose($archivo);
            }
        } else {
            array_splice($datos, 0, 1);
            $datos[] = $mensaje . "\n";
            if ($archivo = fopen($nombre_archivo, "w+")) {
                foreach ($datos as $linea) {
                    fwrite($archivo, $linea);
                }

                fclose($archivo);
            }

        }

    }
    public function obtenerDatos()
    {
        $data = $this->modelo->obtenerDatos();
        $bpm = $data[0]['BPM'];
        $jsonBpm[] = array('label' => 'BPM', 'data' => $bpm, 'color' => '#0073b7');
        $jsonBpm[] = array('label' => '', 'data' => 100 - $bpm, 'color' => '#3c8dbc');
        $json['bpm'] = $jsonBpm;

        $saturacion = $data[0]['saturacion'];
        $jsonSaturacion[] = array('label' => 'BPM', 'data' => $saturacion, 'color' => '#0073b7');
        $jsonSaturacion[] = array('label' => '', 'data' => 100 - $saturacion, 'color' => '#3c8dbc');
        $json['saturacion'] = $jsonSaturacion;
        $json['time'] = time() * 1000;

        header('Content-type: application/json; charset=utf-8');
        echo json_encode($json);
        exit();
    }

    public function procesar()
    {
        $data = $this->modelo->promediar();
    }

    public function notificar($estadoPaciente)
    {
        //Load email library
        //$this->load->library('email');

//SMTP & mail configuration
        $config = array(
            'protocol' => 'smtp',
            'smtp_host' => 'ssl://smtp.googlemail.com',
            'smtp_port' => 465,
            'smtp_user' => 'medifacil.uni@gmail.com',
            'smtp_pass' => 'sedcaemc',
            'mailtype' => 'html',
            'charset' => 'utf-8',
        );
        $this->email->initialize($config);
        $this->email->set_mailtype("html");
        $this->email->set_newline("\r\n");

//Email content
        $htmlContent = '<h1>Notificacion</h1>';
        $htmlContent .= '<p>El paciente requiere su atencion.</p>';

        $this->email->to('gianpascal@gmail.com');
        $this->email->cc('ycieza@pucp.pe');
        $this->email->from('medifacil.uni@gmail.com', 'Medifacil');
        $hora = date("d/m/y H:i:s");
        if($estadoPaciente['estado']=='ACTIVO'){
            $this->email->subject("$hora - Alerta: El paciente se está activo");
        }else{
            $this->email->subject("$hora - Alerta: El paciente se está inactivo");
        }
        
        $this->email->message($htmlContent);

//Send email
        $this->email->send();
    }

    public function recibir_data($numero,$linea){
        $linea=base64_decode($linea);
        echo $linea.'<br>';
        $salida='';
        if (strlen($linea) > 2) {
            $id = 0;
            $fila = array();
            $fila['fecha'] = substr($linea, 0, 17);
            $fila['SN'] = substr($linea, 21, 10);
            $fila['saturacion'] = substr($linea, 37, 3);
            if ($fila['saturacion'] == '---') {
                $fila['saturacion'] = '';
            }
            $fila['BPM'] = substr($linea, 46, 3);
            if ($fila['BPM'] == '---') {
                $fila['BPM'] = '';
            }

            $fila['PI'] = substr($linea, 53, 5);
            if ($fila['PI'] == '--.--') {
                $fila['PI'] = '';
            }
            $fila['SPCO'] = substr($linea, 65, 4);
            if ($fila['SPCO'] == '--.-') {
                $fila['SPCO'] = '';
            }
            $fila['SPMET'] = substr($linea, 77, 4);
            if ($fila['SPMET'] == '--.-') {
                $fila['SPMET'] = '';
            }
            $fila['DESAT'] = substr($linea, 89, 2);
            if ($fila['DESAT'] == '--') {
                $fila['DESAT'] = '';
            }
            $fila['PIDELTA'] = substr($linea, 100, 3);
            if ($fila['PIDELTA'] == '+--') {
                $fila['PIDELTA'] = '';
            }
            $fila['ALARM'] = substr($linea, 110, 4);

            $fila['EXC'] = substr($linea, 119, 6);
            $data[] = $fila;
            $id = $this->modelo->guardar($fila);
            if ($id > 1) {
                $salida = $salida . "$id -- ";
            }
           

        }
       // $this->procesar();
        $estadoPaciente=$this->modelo->detectarEstadoPaciente();
        print_r($estadoPaciente);
        if($estadoPaciente['cambio']=='SI'){
            $this->notificar($estadoPaciente);
            echo 'notificado';
        }
        
        echo $salida;
        
    }
    function vista_correo(){
        $this->load->view('correo.html');
    }
}
