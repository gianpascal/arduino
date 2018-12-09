<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Modelo extends CI_Model
{

    public function guardar($data)
    {
        //verificamos si ya esta insertado
        $fecha = $data['fecha'];
        $consulta = "select *from registro where fecha='$fecha' ";
        //echo $consulta.'<br>';
        $query = $this->db->query($consulta);
        $registro = $query->result_array();
        if (count($registro) == 0) {
            $this->db->insert('registro', $data);
            $id= $this->db->insert_id();
            $consulta="update registro 
            set fechaRegistro=STR_TO_DATE(fecha,'%d/%m/%y %H:%i:%s')
            where idregistro=$id
            ";
            $query = $this->db->query($consulta);
            return $id;
        }

    }

    public function obtenerDatos()
    {
        $consulta = "select * from registro order by idregistro desc limit 1,1 ";
        $query = $this->db->query($consulta);
        $registro = $query->result_array();
        return $registro;
    }

    public function promediar()
    {
        $hitos=$this->obtenerHitos();
        
        $fec_inicio=$hitos['inicio'];
        $fec_fin=$hitos['fin'];
        $estadisticas=$this->obtenerEstadisticas($hitos['inicio'],$hitos['fin']);
       // var_dump($estadisticas);

        extract($estadisticas);
        if ($this->obtenerIntervalo($hitos['inicio'],$hitos['fin'])==0){
            $queryInserta="
        INSERT INTO resumen
        (tipo,
        max,
        min,
        promedio,
        cantidad,
        inicio,
        fin,
        indProcesado,
        frecuencia)
        VALUES
        ('saturacion',
        $max_sat,
        $min_sat,
        $avg_sat,
        $cantidad,
        STR_TO_DATE('$fec_inicio','%d/%m/%Y %H:%i:%s'),
        STR_TO_DATE('$fec_fin','%d/%m/%Y %H:%i:%s'),
        0,
         'minuto'),
         ('bpm',
        $max_bpm,
        $min_bpm,
        $avg_bpm,
        $cantidad,
        STR_TO_DATE('$fec_inicio','%d/%m/%Y %H:%i:%s'),
        STR_TO_DATE('$fec_fin','%d/%m/%Y %H:%i:%s'),
        0,
         'minuto')
         
         ";
         $query = $this->db->query($queryInserta);
        }else{
            $consultaUdateResumen=" update resumen
            set max=$max_sat, min=$min_sat, promedio=$avg_sat,
            cantidad=$cantidad
            where inicio = STR_TO_DATE('$fec_inicio','%d/%m/%Y %H:%i:%s')
            and fin = STR_TO_DATE('$fec_fin','%d/%m/%Y %H:%i:%s')
            and tipo='saturacion'
            "; 
            $query = $this->db->query($consultaUdateResumen);

            $consultaUdateResumen=" update resumen
            set max=$max_bpm, min=$min_bpm, promedio=$avg_bpm,
            cantidad=$cantidad
            where inicio = STR_TO_DATE('$fec_inicio','%d/%m/%Y %H:%i:%s')
            and fin = STR_TO_DATE('$fec_fin','%d/%m/%Y %H:%i:%s')
            and tipo='bpm'
            "; 
            $query = $this->db->query($consultaUdateResumen);
        }

        

         
         /*$consultaUpdate="update registro
         set indProcesadoSpo2=1,
         indProcesadoBPM=1
         where fechaRegistro >= STR_TO_DATE('$fec_inicio','%d/%m/%Y %H:%i:%s')
         and fechaRegistro < STR_TO_DATE('$fec_fin','%d/%m/%Y %H:%i:%s')";*/
         $consultaUpdate="update registro
         set indProcesadoSpo2=1,
         indProcesadoBPM=1
         where idregistro>=$idmin
         and idregistro<=$idmax";
         $query = $this->db->query($consultaUpdate);
    }

    public function obtenerHitos()
    {
        $consulta = "SELECT
        MIN(idregistro) min_id,
        MAX(idregistro) max_id,
        DATE_FORMAT(MIN(fechaRegistro), '%d/%m/%Y %H:%i:00') inicio,
        DATE_FORMAT(DATE_ADD(MIN(fechaRegistro),
                    INTERVAL 1 MINUTE),
                '%d/%m/%Y %H:%i:00') fin
        FROM
            registro a
        WHERE
            a.indProcesadoSpo2 = 0";
        $query = $this->db->query($consulta);
        $datosIniciales = $query->result_array();
        return $datosIniciales[0];
    }
    public function obtenerEstadisticas($min,$max){
        $consulta="select IFNULL(avg(a.saturacion),0) avg_sat, IFNULL(min(a.saturacion),0) min_sat, IFNULL(max(a.saturacion),0) max_sat,
         count(a.idregistro) cantidad, IFNULL(avg(a.bpm),0) avg_bpm, IFNULL(min(a.bpm),0) min_bpm, IFNULL(max(a.bpm),0) max_bpm,
         min(a.idregistro) idmin, max(a.idregistro) idmax
         
        from registro a
        where a.fechaRegistro >= STR_TO_DATE('$min','%d/%m/%Y %H:%i:%s')
        and a.fechaRegistro < STR_TO_DATE('$max','%d/%m/%Y %H:%i:%s')";
        echo "<br> $consulta <br>";
        $query = $this->db->query($consulta);
        $datos = $query->result_array();
        return $datos[0];

    }

    public function obtenerIntervalo($min,$max){
        $consulta="select *         
        from resumen a
        where inicio= STR_TO_DATE('$min','%d/%m/%Y %H:%i:%s')
        and fin= STR_TO_DATE('$max','%d/%m/%Y %H:%i:%s')";
        $query = $this->db->query($consulta);
        $datos = $query->result_array();
        if (count($datos)>1){
            return 1;
        }else{
            return 0;
        }
    }
    public function actualizaEstadoPaciente($estado){
        $consulta="update paciente
        set estado= '$estado'";
        $query = $this->db->query($consulta);
    }

    public function detectarEstadoPaciente(){
        $consulta="select sum(bpm) sum_bpm,sum(saturacion)  sum_saturacion from registro a
        where idregistro>(select max(idregistro)-10 from registro)";
        $query = $this->db->query($consulta);
        $datos = $query->result_array();
        $paciente=$this->datosPaciente();
        $estado='';
        $estadoPaciente=array();
        print_r($datos);
        echo "<br>";
        if($datos[0]['sum_bpm']=='0'){
            $estado='INACTIVO';
        }else{
            $estado='ACTIVO';          
        }
        $estadoPaciente['cambio']='NO';
        if($estado!=$paciente['estado']){
            $estadoPaciente['cambio']='SI';
        }
        $estadoPaciente['estado']=$estado;
        $this->actualizaEstadoPaciente($estado);
        return ($estadoPaciente);
    }
    public function datosPaciente(){
        $consulta="select *from paciente";
        $query = $this->db->query($consulta);
        $datos = $query->result_array();
        return $datos[0];
    }

}
