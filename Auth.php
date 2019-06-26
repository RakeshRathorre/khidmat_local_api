<?php 	
class Auth extends CI_Controller{

 function __construct() {
    parent::__construct();
    $this->load->model('Core_Model');
    $this->load->model('Common_Model');
    $this->load->model('MY_Model');
    $this->load->library('session');
    $this->res = new stdClass();
    // $request = json_decode(rtrim(file_get_contents('php://input'), "\0"));
    }
// 
    public function signup() {
        $request = json_decode(rtrim(file_get_contents('php://input'), "\0")); 
        // print_r($request);die();
        $f_name = $request->full_name;    
        $l_name = $request->last_name;    
        $email = $request->email;
        $phone = $request->phone;
        $gender = $request->gender;    
        $dob = $request->dob;    
        $address = $request->address;    
        $zip_code = $request->zip_code;    
        $country = $request->country;    
        $city = $request->city;    
        $username = $request->username;    
        $password = $request->password;    
        $image = $request->image;    
        $user_type = $request->user_type;    
        $is_verified = $request->is_verified;    
        $is_login = $request->is_login;    
        $is_deleted = $request->is_deleted;    
        // echo $f_name; die;
        if (!$email) {
                    $this->_error('Form error', 'Email is not specified.');
        }
        if (!$password) {
                    $this->_error('Form error', 'Password is not specified.');
        }
        if (!$username) {
                    $this->_error('Form error', 'User Name is not specified.');
        }
        
        if ($this->email_check($email)) {
                    $this->_error('Form error', 'Email already exists.');
        }
        else {
            $where = array('email'=>$email,'password'=>md5($password),'username'=>$username,'f_name'=>$f_name,'l_name'=>$l_name,'phone'=>$phone,'gender'=>$gender,'dob'=>$dob,'address'=>$address,'zip_code'=>$zip_code,'country'=>$country,'city'=>$city,'image'=>$image,'user_type'=>$user_type,'is_verified'=>$is_verified,'is_login'=>$is_login,'is_deleted'=>$is_deleted);
            // print_r($where);die;
            // $field=array('email');
            $get_email = $this->Core_Model->InsertRecord('users', $where);
            // print_r($get_email);die;
            }
            if (!empty($get_email)) {
            $this->res->success = 'true';
                return true;
            }
            $this->res->success = 'false';
            return false;
        }
    function email_check($email) {
        $where = array('email' => $email);
        $field = 'email';
        // print_r($where);die();
        $get_email = $this->Core_Model->SelectSingleRecord('users', $field, $where);
    // print_r($get_email);die;
        if (!empty($get_email)) {
             return true;
        }
         return false;
    }
    public function signin()
    {
        $request = json_decode(rtrim(file_get_contents('php://input'), "\0"));
        $email = $request->email;
        $password = $request->password;
        // print_r($this->input->request_headers());die();
        //for accesstoken check
        // echo $password;die();
        if (!$email) {
            $this->_error('Form error', 'Email-Id is not specified.');
        }
        if (!$password) {
            $this->_error('Form error', 'Password is not specified.');
        }
         $where_login = array('email' => $email, 'password' => md5($password));
         $aray_login = $this->Core_Model->selectsinglerecord('users', '*', $where_login);
         if(empty($aray_login)) {
            // $this->res->status = 'Failed';
            $this->_error('error', 'Incorrect Email Id & Password.');
        } else {
            // $id=$aray_login['id'];
            $accesstoken = base64_encode(random_bytes(32));
            $is_login='1';
            // print_r($accesstoken);die();
            //for accesstoken show
            //update access token
            $where_update = array('email' => $email);
            $field_update = array('accesstoken'=>$accesstoken,'is_login'=>$is_login);
            // print_r($field_update);die();
            $this->Core_Model->updateFields('users', $field_update, $where_update);
            $this->res->success = 'true';
            $aray_login2 = $this->Core_Model->selectsinglerecord('users', '*', $where_login);
            $this->res->data = $aray_login2;
        }
        $this->_output();
        exit();
    }

    public function logout()
    {
        $request = json_decode(rtrim(file_get_contents('php://input'), "\0"));
         // $user_id = $request->user_id;
         $id = $request->user_id;
         // print_r($id);die();
         $header = $this->input->request_headers();
         $accesstoken = $header['accesstoken'];
         // print_r($accesstoken);die();
        if($this->check_accesstoken($id,$accesstoken)){
            // $where_update = array('id' => $user_id);
            $where_update = array('id' => $id);
            // $field_update = array('accesstoken'=>0,'is_user_login'=>0);
            $field_update = array('accesstoken'=>0,'is_login'=>0);
            $this->Core_Model->updateFields('users', $field_update, $where_update);
            $this->res->success = 'true';
            $this->res->message = 'Successfull updated/removed accesstoken';
        }else{
            // $this->res->status = 'Failed';
            $this->_error('error', 'Invalid accesstoken.');
        }
        $this->_output();
          exit();
    }
    public function check_accesstoken($id,$accesstoken)
    {
        // $where = array('id'=>$user_id,'accesstoken'=>$accesstoken);
        $where = array('id'=>$id,'accesstoken'=>$accesstoken);
        $selectdata = 'id,accesstoken';
        $res = $this->Core_Model->SelectSingleRecord('users',$selectdata,$where,$order='');
       if($res){
        return true;
       }else
       return false;
    }
    
    public function category() 
    {
        $categories2 = $this->MY_Model->SelectRecord('category','*',$udata=array("is_deleted"=>"0","parent_id"=>"0"),'order_id asc');            
            $cname = [];
            $level = 1; 
    
            foreach ($categories2 as $key => $value)
            {
                $cname[$value['title']][] = ['id'=>$value['id'], 'cname'=>$value['title'],'level'=>$value['level']];
    
                $arr[] = ['id'=>$value['id'], 'parent_id'=>$value['parent_id'], 'cname'=>$value['title'],'level'=>$value['level'],'order_id'=>$value['order_id'],'description'=>$value['description'],'image' => base_url('upload/category/').$value['image'],'icon'=>$value['icon']];
    
                $cat = $this->MY_Model->SelectRecord('category','*',$udata=array("is_deleted"=>"0","parent_id"=>$value['id']),'order_id asc');  

                foreach ($cat as $key => $result) 
                {
                    $parent_id = $result['id']; 
    
                    $cname[$value['title']][$result['id']][] = ['id'=>$result['id'], 'parent_id'=>$result['parent_id'],'cname'=>$result['title'],'level'=>$result['level'],'order_id'=>$result['order_id']];
                    
                    $arr[] = ['id'=>$result['id'], 'parent_id'=>$result['parent_id'], 'cname'=>$result['title'],'level'=>$result['level'],'order_id'=>$result['order_id'],'description'=>$result['description'],'image' => base_url('upload/category/').$value['image'],'icon'=>$result['icon']];
    
                    while (1) 
                    {
                        $data = $this->MY_Model->SelectRecord('category','*',$udata=array("is_deleted"=>"0","parent_id"=>$parent_id),'order_id asc');
                        // print_r($data);die;
                        if(count($data)>1)
                        {
                            foreach ($data  as $key => $data) 
                            {
                                if($data)
                                {
                                    $level++;
                                    $parent_id = $data['id'];
    
                                    $cname[$value['title']][$result['id']][$parent_id][] = ['id'=>$data['id'],'parent_id'=>$data['parent_id'],'cname'=>$data['title'],'level'=>$data['level'],'order_id'=>$data['order_id'],'description'=>$data['description'],'image'=>$data['image'],'icon'=>$data['icon']];
    
                                     $arr[]  = ['id'=>$data['id'], 'parent_id'=>$data['parent_id'],'cname'=>$data['title'],'level'=>$data['level'],'order_id'=>$data['order_id'],'description'=>$data['description'],'image' => base_url('upload/category/').$value['image'],'icon'=>$data['icon']];                     
                                        // print_r($arr);die();
                                }
                                else{ break; }
                            }
                        }
                        else
                        {
                            $data = $this->MY_Model->SelectSingleRecord('category','*',$udata=array("is_deleted"=>"0","parent_id"=>$parent_id),'order_id asc'); 

                        if(!empty($data))
                        {
                            // print_r($data); die;
                            $level++;
                            $parent_id = $data->id;
    
                            $cname[$value['title']][$result['id']][$parent_id][] = ['id'=>$data->id,'parent_id'=>$data->parent_id,'cname'=>$data->title,'level'=>$data->level,'order_id'=>$data->order_id,'description'=>$data->description,'image'=>$data->image,'icon'=>$data->icon];
    
                             $arr[]  = ['id'=>$data->id, 'parent_id'=>$data->parent_id,'cname'=>$data->title,'level'=>$data->level,'order_id'=>$data->order_id,'description'=>$data->description,'image'=>$data->image,'icon'=>$data->icon];
                            // print_r($arr);die();           
                        }
                        else
                        { break; }
                    }
                } 
            }
            $result_set[]  = $arr; 
            $arr    = []; 
         }
            if (empty($result_set)) 
            {
                // $this->res->status = 'Failed';
                $this->res->success = 'false';
                $this->_error('error', 'Incorrect data.');
            } else 
            {
                $this->res->success = 'true';
                $this->res->data = $result_set;
            }
            $this->_output();
              exit();
            // print_r($result_set); die;
            // $datas->categories = $result_set;
    }
    ////////////////////
    function get_parent()
    {      
            $request = json_decode(rtrim(file_get_contents('php://input'), "\0"));
            $catid = $request->catid;
            // print_r($catid);die();
            // $html='';
            $CI =& get_instance();
            $this->db->select('*');            
            $this->db->where(array('id'=>$catid));
            $query = $this->db->get('category');            
            $result = $query->row();
            $parent_id1 = $result->parent_id;
            $where = array('id' => $parent_id1);
            $array_data1 = $this->Core_Model->selectsinglerecord('category', '*', $where);        
            if (!empty($array_data1)) 
            {
                $parent_id2 = $array_data1->parent_id;

                $where = array('id' => $parent_id2);
                $array_data2 = $this->Core_Model->selectsinglerecord('category', '*', $where);
            }
            else
            {
                $array_data1 = $this->Core_Model->selectsinglerecord('category', '*', $where);
            }
            // $html .= ';<li class="breadcrumb-item"><a href="'.site_url('catalog/'.$result->parent_id.'/'.$result->id).'">'.$result->title.'</a></li>';
            // print_r($html); die; 
            if (empty($array_data1)) 
            {
                // $this->res->status = 'Failed';
                $this->res->success = 'false';
                $this->_error('error', 'Incorrect data.');
            } 
            else
            {
                //echo 'hello'; die;
                if (empty($array_data2)) 
                {
                    $this->res->success = 'true';
                    $this->res->data = $array_data1;
                }
                else
                {
                  $this->res->success = 'true';
                  $this->res->data[] = $array_data2;   
                  $this->res->data[] = $array_data1;   
                }
            }
            $this->_output();
              exit();
    }
    /////////////////////////////
    function _output() {
        header('Content-Type: application/json');
        //$this->res->request = $this->req->request;
        $this->res->datetime = date('Y-m-d\TH:i:sP');
        echo json_encode($this->res);
    }
    function _error($error, $reason, $code = null) {
        header('Content-Type: application/json');
        // $this->res->status = 'false';
        $this->res->success = 'false';
        if (isset($this->req->request)) {
            $this->res->request = $this->req->request;
        }
        $this->res->message = $reason;
        $this->res->datetime = date('Y-m-d\TH:i:sP');
        echo json_encode($this->res);
        die();
    }
}

?>