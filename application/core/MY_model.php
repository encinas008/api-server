<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MY_model extends CI_Model{

	protected $table_name = '';

	public function __construct(){
        parent::__construct();

        //!$this->db = $this->load->database('atm', TRUE) ? $this->db = $this->load->database('atm', TRUE) : false;
        $this->db = $this->load->database('atm', TRUE);
    }

	public function create($values = array()){
        $values['created_at'] = date(DATETIME_FORMAT);
        $values['updated_at'] = date(DATETIME_FORMAT);
        $this->db->insert($this->table_name, $values);

        $query = $this->db  ->where('id', $this->db->insert_id())
                            ->get($this->table_name);
        return $query->row();
    }

    public function update($object){
        $object->updated_at = date(DATETIME_FORMAT);

        $this->db->where('id', $object->id)
                ->update($this->table_name, $object);

        $query = $this->db->where('id', $object->id)
                        ->get($this->table_name);
        return $query->row();
    }

    public function delete($object){
        $this->db->where('id', $object->id)
                 ->delete($this->table_name);
    }

    public function getById($id){
        $item = $this->db->where('id', $id)
                         ->from($this->table_name)
                         ->get()
                         ->row();
        return $item;
    }

    public function getAll(){
        $items = $this->db->from($this->table_name)->get()->result();
        return $items;
    }

    public function getCountAll(){
        $count = $this->db->count_all_results($this->table_name);
        return $count;
    }
    
    public function getItem($options = array(), $limit = null, $offset = null){
        /*
         * function will return either a singular object or an array of objects as appriopriate
         * options are the items in the 'where' clause. 
         * See http://ellislab.com/codeigniter/user-guide/database/active_record.html [get_where]
         */

        $query = $this->db->get_where($this->table_name, $options, $limit, $offset);
        
        if($query->num_rows() > 1)
                return $query->result();
         
        return $query->row();
    }

    public function executeQuery($string_query){
        $query = $this->db->query($string_query);
        return $query->result();
    }

    public function executeQuerySingle($string_query){
        $query = $this->db->query($string_query);
        return $query->row();
    }
}