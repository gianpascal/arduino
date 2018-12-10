<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Monitoreo_model extends CI_Model
{
    public function __construct() {
        parent::__construct();
    }

    public function addMedicion($data)
    {
        //verificamos si ya esta insertado
        
        $this->db->insert('monitoreo', $data);

    }

    public function getParametro()
    {
        //verificamos si ya esta insertado
        
        $query = $this->db->get('parametro');

        return $query->row();

    }

}
