<?php 	
class Api extends CI_Controller
{
 function __construct(){
    parent::__construct();
    $this->load->model('Core_Model');
    $this->load->model('Common_Model');
    $this->load->model('MY_Model');
    $this->load->library('session');
    $this->res = new stdClass();
    // $request = json_decode(rtrim(file_get_contents('php://input'), "\0"));
 }
    public function signup() 
    {
        $request = json_decode(rtrim(file_get_contents('php://input'), "\0")); 
        // print_r($request);die();
        $f_name = $request->first_name;    
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
                    return false;
        }
        if (!$password) {
                    $this->_error('Form error', 'Password is not specified.');
                    return false;
        }
        if (!$username) {
                    $this->_error('Form error', 'User Name is not specified.');
                    return false;
        }
         if (!$phone) {
                    $this->_error('Form error', 'Contact No is not specified.');
                    return false;
        }
        
        if ($this->email_check($email)) {
                    $this->_error('Form error', 'Email already exists.');
                    return false;
        }
        
            $where = array('email'=>$email,'password'=>md5($password),'username'=>$username,'f_name'=>$f_name,'l_name'=>$l_name,'phone'=>$phone,'gender'=>$gender,'dob'=>$dob,'address'=>$address,'zip_code'=>$zip_code,'country'=>$country,'city'=>$city,'image'=>$image,'user_type'=>$user_type,'is_verified'=>$is_verified,'is_login'=>$is_login,'is_deleted'=>$is_deleted);
            // print_r($where);die;
            // $field=array('email');
            $get_email = $this->Core_Model->InsertRecord('users', $where);
            // print_r($get_email);die;
            
            if (!empty($get_email)) {
            $this->res->success = 'true';
                //echo"true";
                //return true;
            }
           $this->_output();
            exit();
        }
//---------------------*-------------------
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
    //---------------------*-------------------
    public function signin()
    {
        $request = json_decode(rtrim(file_get_contents('php://input'), "\0"));
        $email = $request->email;
        $password = $request->password;
        print_r($password);die();
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
         $array_login = $this->Core_Model->selectsinglerecord('users', '*', $where_login);
         //    print_r($array_login);die();
         if(empty($array_login)) {
            // $this->res->status = 'Failed';
            $this->_error('error', 'Incorrect Email Id & Password.');
        } else {
            // $id=$aray_login['id'];
            $accesstoken = base64_encode(random_bytes(32));
            // $accesstoken = base64_encode(uniqid()); //other type for getting random no
            $is_login='1';
            // print_r($accesstoken);die();
            //for accesstoken show
            //update access token
            $where_update = array('email' => $email);
            $field_update = array('accesstoken'=>$accesstoken,'is_login'=>$is_login);
            // print_r($field_update);die();
            $this->Core_Model->updateFields('users', $field_update, $where_update);
            $this->res->success = 'true';
            $array_login2 = $this->Core_Model->selectsinglerecord('users', '*', $where_login);
            $array_login2->image = base_url('upload/category/').$array_login2->image; //image url get code
            $this->res->data = $array_login2;
        }
        $this->_output();
        exit();
    }
//---------------------*-------------------
    public function logout()
    {
        $request = json_decode(rtrim(file_get_contents('php://input'), "\0"));
         // $user_id = $request->user_id;
         $id = $request->user_id;
         // print_r($id);die();
         $header = $this->input->request_headers();
         $accesstoken = $header['Accesstoken'];
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
     //---------------------*-------------------
    function update_profile()
    { 
        $request = json_decode(rtrim(file_get_contents('php://input'), "\0"));
        $email = $request->email;    
        $f_name = $request->first_name;    
        $l_name = $request->last_name;    
        $phone = $request->phone;
        $gender = $request->gender;    
        $dob = $request->dob;    
        $address = $request->address;    
        $zip_code = $request->zip_code;    
        $country = $request->country;    
        $city = $request->city;    
        $username = $request->username;    
        $image = $request->image;    
        $user_type = $request->user_type;
        // print_r($password);die();
        if (!$email) {
                    $this->_error('Form error', 'Email is not specified.');
        }
         if (!$f_name) {
                    $this->_error('Form error', 'First Name is not specified.');
        }
        if (!$l_name) {
                    $this->_error('Form error', 'Last Name is not specified.');
        }
        if (!$phone) {
                    $this->_error('Form error', 'Phone is not specified.');
        }
         if (!$gender) {
                    $this->_error('Form error', 'Gender is not specified.');
        }
         if (!$dob) {
                    $this->_error('Form error', 'Date of Birth is not specified.');
        }
         if (!$address) {
                    $this->_error('Form error', 'Address is not specified.');
        }
         if (!$zip_code) {
                    $this->_error('Form error', 'Zip Code is not specified.');
        }
         if (!$country) {
                    $this->_error('Form error', 'Country is not specified.');
        }
         if (!$city) {
                    $this->_error('Form error', 'City is not specified.');
        }
         if (!$username) {
                    $this->_error('Form error', 'User Name is not specified.');
        }
         if (!$image) {
                    $this->_error('Form error', 'Image is not specified.');
        }
         if (!$user_type) {
                    $this->_error('Form error', 'User Type is not specified.');
        }
        else 
        {
            $where_update = array('email' => $email);
            $field_update = array('f_name' => $f_name,'l_name' => $l_name,'phone' => $phone,'gender' => $gender,'dob' => $dob,'address' => $address,'zip_code' => $zip_code,'country' => $country,'city' => $city,'username' => $username,'image' => $image,'user_type' => $user_type);

            $result = $this->Core_Model->updateFields('users', $field_update, $where_update);
            // print_r($field_update);die();
            if (empty($result))
            {
                $this->res->success = 'false';
                $this->res->message = "Incorrect data";
            }
            else
            {
                $this->res->success = 'true';
                $this->res->message = 'record updated';
            }
        }
        $this->_output();
            exit();
    }
    //---------------------*-------------------
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
                    $parent_id = $result['id']; //we should check $cat_id=$result['id']
    
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
    //---------------------*-------------------
    function get_parent()
    {      
            $request = json_decode(rtrim(file_get_contents('php://input'), "\0"));
            $category_id = $request->category_id;
            // print_r($catid);die();
            // $html='';
            $CI =& get_instance();
            $this->db->select('*');            
            $this->db->where(array('id'=>$category_id));
            $query = $this->db->get('category');            
            $result = $query->row();
            $parent_id1 = $result->parent_id;
            $where = array('id' => $parent_id1);

            // image url get code
            // $iUrl = base_url('upload/category/');

            // $data = $this->db->select(
            //     "*,IF(image != '' , CONCAT('".$iUrl."',image) , 'False') as image"
            // )->get_where('category',$where)->row_array();
            // print_r($data);die();

            $array_data1 = $this->Core_Model->selectsinglerecord('category', '*', $where);
            // $array_data1->image = base_url('upload/category/').$array_data1->image; //image url get code
    
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
            if (empty($array_data1)) 
            {
                $this->res->success = 'false';
                $this->_error('error', 'Incorrect data.');
            } 
            else
            {
                if (empty($array_data2)) 
                {
                    $this->res->success = 'true';
                    $this->res->data = $array_data1;
                    $array_data1->image = base_url('upload/category/').$array_data1->image; //image url get code
                }
                else
                {
                  $this->res->success = 'true';
                  $this->res->data[] = $array_data2;   
                  $array_data2->image = base_url('upload/category/').$array_data2->image;
                  $this->res->data[] = $array_data1; 
                  $array_data1->image = base_url('upload/category/').$array_data1->image; //image url get code  
                }
            }
            $this->_output();
              exit();
    }
    //---------------------*-------------------
    function services()
    { 
        $request = json_decode(rtrim(file_get_contents('php://input'), "\0"));
        $category_id = $request->category_id;
        $where = array('category_id' => $category_id);
        $result = $this->Core_Model->SelectRecord('services', '*', $where, $order = '');
        $userdata1 = [];
        foreach ($result as $total)
         {
            $userdata[] = array('id' => $total['id'],'category_id' => $total['category_id'],'title' => $total['title'],'description' => $total['description'],'image' => base_url('upload/category/').$total['image'],'icon' => $total['icon']);
        // print_r($userdata);die();
         }
        if (empty($result))
        {
            $this->res->success = 'false';
            $this->res->message = "Incorrect data";
        }
        else
        {
            $this->res->success = 'true';
            $this->res->data = $userdata;   
        }
        $this->_output();
              exit();
    }
    //---------------------*-------------------
     function blogs()
    { 
        $request = json_decode(rtrim(file_get_contents('php://input'), "\0"));
        $category_id = $request->category_id;
        if (empty($category_id))
        {
            $result1 = $this->Core_Model->SelectRecord('blogs', 'id,category_id,title,description,image', $where, $order = '');

            $userdata1 = [];
            foreach ($result1 as $total)
             {
                $userdata1[] = array('id' => $total['id'],'category_id' => $total['category_id'],'title' => $total['title'],'description' => $total['description'],'image' => base_url('upload/category/').$total['image']);
            // print_r($total);die();
             }
            if (empty($result1))
            {
                $this->res->success = 'false';
                $this->res->message = "Incorrect data";
            }
            else
            {
                $this->res->success = 'true';
                $this->res->data = $userdata1;
            }
        }
        else
        {
            $where = array('category_id' => $category_id);
            $result2 = $this->Core_Model->SelectRecord('blogs', 'id,category_id,title,description,image', $where, $order = '');
            // print_r($result2);die();

            $userdata2 = [];
            foreach ($result2 as $total)
             {
            // print_r($total);die();
                $userdata2[] = array('id' => $total['id'],'category_id' => $total['category_id'],'title' => $total['title'],'description' => $total['description'],'image' => base_url('upload/category/').$total['image'] );
             }
            if (empty($result2))
            {
                $this->res->success = 'false';
                $this->res->message = "Incorrect data";
            }
            else
            {
                $this->res->success = 'true';
                $this->res->data = $userdata2;   
            }
        }
            $this->_output();
              exit();
    }
    //---------------------*-------------------
    function options()
    { 
        $request = json_decode(rtrim(file_get_contents('php://input'), "\0"));
        $service_id = $request->service_id;
        $where = array('service_id' => $service_id);
        $result = $this->Core_Model->SelectRecord('options', '*', $where, $order = '');
        // print_r($result);die();
        $userdata1 = [];
        foreach ($result as $total)
         {
            $userdata[] = array('id' => $total['id'],'service_id' => $total['service_id'],'field_key' => $total['field_key'],'field_value' => $total['field_value'],'field_type' => $total['field_type'],'field_icon' => $total['field_icon'],'field_position' => $total['field_position'],'list_name' => $total['list_name'],'is_multiple' => $total['is_multiple'],'is_radio' => $total['is_radio'],'is_required' => $total['is_required']);
        // print_r($userdata);die();
         }
        if (empty($result))
        {
            $this->res->success = 'false';
            $this->res->message = "Incorrect data";
        }
        else
        {
            $this->res->success = 'true';
            $this->res->data = $userdata;   
        }
        $this->_output();
              exit();
    }
    //---------------------*-------------------
    public function add_vendor_services() {
        $request = json_decode(rtrim(file_get_contents('php://input'), "\0")); 
        // print_r($request);die();
        $f_name = $request->first_name;    
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
        $vendor_services = $request->vendor_services;   
        $vendor_services_search = $request->vendor_services_search;   
        $vendor_status = $request->vendor_status;    
        // print_r($request);die(); 
        // echo $f_name; die;
         if (!$email) {
                    $this->_error('Form error', 'Email is not specified.');
                    return false;
        }
        if (!$password) {
                    $this->_error('Form error', 'Password is not specified.');
                    return false;
        }
        if (!$username) {
                    $this->_error('Form error', 'User Name is not specified.');
                    return false;
        }
        
        if ($this->email_check_vendor($email)) {
                    $this->_error('Form error', 'Email already exists.');
                    return false;
        }
        
            $where = array('email'=>$email,'password'=>md5($password),'username'=>$username,'f_name'=>$f_name,'l_name'=>$l_name,'phone'=>$phone,'gender'=>$gender,'dob'=>$dob,'address'=>$address,'zip_code'=>$zip_code,'country'=>$country,'city'=>$city,'image'=>$image,'user_type'=>$user_type,'is_verified'=>$is_verified,'is_login'=>$is_login,'is_deleted'=>$is_deleted);
            // print_r($where);die;
            $get_email = $this->Core_Model->InsertRecord('users', $where);
            // print_r($get_email);die;            
            if (!empty($get_email))
            {
                $email = $request->email;
                $where = array('email' => $email);
                $result = $this->Core_Model->SelectSingleRecord('users', 'id', $where, $order = '');
                $id = $result->id;
                $where = array('vendor_id'=>$id,'services'=>json_encode($vendor_services),'services_search'=>$vendor_services_search,'status'=>$vendor_status);
                $get_vendor_services = $this->Core_Model->InsertRecord('vendor_services', $where);
                // print_r($get_vendor_services);die;
                foreach ($vendor_services as $userServicesId) 
                {
                    // print_r($total);die();
                   $where =  array('userId'=>$id,'userServicesId'=>$userServicesId,'price'=>0,'weekPrice'=>0,'monthPrice'=>0,'yearPrice'=>0);
                   $get_vendor_price = $this->Core_Model->InsertRecord('vendor_services_price', $where);
                }

                $this->res->success = 'true';   
                    return true;
                $this->_output();
                exit();
            }
        }
//---------------------*-------------------
    function email_check_vendor($email) {
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
    //---------------------*-------------------
    public function update_vendor_services_price() 
    {
        $request = json_decode(rtrim(file_get_contents('php://input'), "\0")); 
        // print_r($request);die();
        $userId = $request->userId; 
        $userServicesId = $request->userServicesId; 
        $vendor_price = $request->vendor_price;         
        $weekPrice = $request->weekPrice;         
        $monthPrice = $request->monthPrice;         
        $yearPrice = $request->yearPrice;    
    // print_r($request);die(); 
        $where_update = array('userId' => $userId,'userServicesId' => $userServicesId);
        $field_update = array('price'=>$vendor_price,'weekPrice'=>$weekPrice,'monthPrice'=>$monthPrice,'yearPrice'=>$yearPrice);
        // print_r($field_update);die;
        $result = $this->Core_Model->updateFields('vendor_services_price', $field_update, $where_update);
        // print_r($result);die();
        $this->res->success = 'true';  
        $this->res->message = 'Successfull updated data'; 
            // return true;
       $this->_output();
        exit();
    }
    //---------------------*-------------------
    public function booking_payment()
    {
        $request = json_decode(rtrim(file_get_contents('php://input'), "\0"));
        $vendor_id = $request->vendor_id;
        $amount = $request->amount;
        $order_status = $request->order_status;
        $qty = $request->qty;
        $servicename = $request->servicename;
        $payment_method = $request->payment_method;
        $payment_price = $request->payment_price;
        $payment_type = $request->payment_type;
        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $time = $request->time;
        $payment_status = $request->payment_status;
        $review_status = $request->review_status;
        $services = $request->services;
        $order_no = $this->create_order_no();//"ORDER_".uniqid();
        // print_r($order_no);die();
        $location = $request->location;
        $schedule = $request->schedule;
        // print_r($request);die();
        // print_r($this->input->request_headers());die();
        //for accesstoken check
        // echo $password;die();
        if (!$vendor_id) {
            $this->_error('Form error', 'Vendor-Id is not specified.');
        }
        if (!$amount) {
            $this->_error('Form error', 'Amount is not specified.');
        }
        if (!$order_status) {
            $this->_error('Form error', 'Order Status is not specified.');
        }
        if (!$qty) {
            $this->_error('Form error', 'Quantity is not specified.');
        }
        if (!$servicename) {
            $this->_error('Form error', 'Service Name is not specified.');
        }
        if (!$payment_method) {
            $this->_error('Form error', 'Payment Method is not specified.');
        }
        if (!$payment_price) {
            $this->_error('Form error', 'Payment Price is not specified.');
        }
        if (!$payment_type) {
            $this->_error('Form error', 'Payment Type is not specified.');
        }
        if (!$startDate) {
            $this->_error('Form error', 'Start Date is not specified.');
        }
        if (!$endDate) {
            $this->_error('Form error', 'End Date is not specified.');
        }
        if (!$time) {
            $this->_error('Form error', 'Time is not specified.');
        }
        if (!$payment_status) {
            $this->_error('Form error', 'Payment Status is not specified.');
        }
        if (!$review_status) {
            $this->_error('Form error', 'Review Status is not specified.');
        }
        if (!$services) {
            $this->_error('Form error', 'Services is not specified.');
        }
        if (!$location) {
            $this->_error('Form error', 'Location is not specified.');
        }
        if (!$schedule) {
            $this->_error('Form error', 'Schedule is not specified.');
        }

        $where1 = array('vendor_id'=>$vendor_id,'order_id'=>$order_no,'qty'=>$qty,'amount'=>$amount,'servicename'=>$servicename,'payment_method'=>$payment_method,'payment_price'=>$payment_price,'payment_type'=>$payment_type,'startDate'=>$startDate,'endDate'=>$endDate,'time'=>$time,'payment_status'=>$payment_status,'review_status'=>$review_status,'services'=>json_encode($services),'location'=>json_encode($location),'schedule'=>$schedule);
        $result1 = $this->Core_Model->InsertRecord('order_detail', $where1);

              $udata['transaction_id'] = rand();
        $where2 = array('user_id'=>$vendor_id,'order_no'=>$order_no,'amount'=>$amount,'payment_type'=>$payment_type,'payment_status'=>1,'transaction_id'=>$udata['transaction_id']);
        $result2 = $this->Core_Model->InsertRecord('order', $where2);
            // print_r($result2);die;
        $this->res->success = 'true';  
        $this->res->message = 'Order has been booked and payment done';
        $this->_output();
        exit();
    }
    //---------------------*-------------------
    public function create_order_no()
        {
            $order = "ORDER_".uniqid();                   
            if($this->MY_Model->SelectRecord('order','*',array("order_no"=>$order),$orderby=array())){
                $this->create_order_no();
            }
            return $order;
        }
    //---------------------*-------------------
    function user_order_list()
    { 
        $request = json_decode(rtrim(file_get_contents('php://input'), "\0"));
        $user_id = $request->user_id;
        $where = array('order.user_id' => $user_id);
                //CONCAT('w3resource','.','com')
            $res = $this->Core_Model->joindataResult($place1 ='order.order_no', $place2 = 'order_detail.order_id',$where,'order.order_no,order.transaction_id,order.id,order.user_id,order_detail.id,order_detail.qty,order_detail.amount,order_detail.servicename,order_detail.payment_method,order_detail.payment_price,order_detail.payment_type,order_detail.startDate,order_detail.endDate,order_detail.time,order_detail.payment_status,order_detail.review_status,order_detail.services,order_detail.location,order_detail.schedule,order_detail.order_id','order_detail','order',$order='');
            // print_r($res);die();
            $userdata = [];
            foreach ($res as $total) 
            {
                $userdata[] = array('id' => $total['id'],'user_id' => $total['user_id'],'transaction_id' => $total['transaction_id'],'amount' => $total['amount'],'servicename' => $total['servicename'],'payment_method' => $total['payment_method'],'payment_price' => $total['payment_price'],'payment_type' => $total['payment_type'],'startDate' => $total['startDate'],'endDate' => $total['endDate'],'time' => $total['time'],'payment_status' => $total['payment_status'],'review_status' => $total['review_status'],'services' => json_decode($total['services']),'location' => json_decode($total['location']),'schedule' => $total['schedule']);
                // print_r($userdata);die();
            }
            if (empty($userdata)) 
            {
                $this->res->success = false;
                $this->_error('error', 'Incorrect id or data.');
            } else
             {
                $this->res->success = 'true';
                $this->res->data = $userdata;
            }
        $this->_output();
              exit();
    }
    //---------------------*-------------------
    function vendor_order_list()
    { 
        $request = json_decode(rtrim(file_get_contents('php://input'), "\0"));
        $vendor_id = $request->vendor_id;
        $where = array('order_detail.vendor_id' => $vendor_id);
                //CONCAT('w3resource','.','com')
            $res = $this->Core_Model->joindataResult($place1 ='order_detail.order_id', $place2 = 'order.order_no',$where,'order_detail.id,order_detail.qty,order_detail.amount,order_detail.servicename,order_detail.payment_method,order_detail.payment_price,order_detail.payment_type,order_detail.startDate,order_detail.endDate,order_detail.time,order_detail.payment_status,order_detail.review_status,order_detail.services,order_detail.location,order_detail.schedule,order_detail.order_id,order.order_no,order.transaction_id,order.id,order.user_id','order','order_detail',$order='');
            // print_r($res);die();
            $userdata = [];
            foreach ($res as $total) 
            {
                $userdata[] = array('id' => $total['id'],'user_id' => $total['user_id'],'transaction_id' => $total['transaction_id'],'amount' => $total['amount'],'servicename' => $total['servicename'],'payment_method' => $total['payment_method'],'payment_price' => $total['payment_price'],'payment_type' => $total['payment_type'],'startDate' => $total['startDate'],'endDate' => $total['endDate'],'time' => $total['time'],'payment_status' => $total['payment_status'],'review_status' => $total['review_status'],'services' => json_decode($total['services']),'location' => json_decode($total['location']),'schedule' => $total['schedule']);
                // print_r($userdata);die();
            }
            if (empty($userdata)) 
            {
                $this->res->success = false;
                $this->_error('error', 'Incorrect id or data.');
            } else
             {
                $this->res->success = 'true';
                $this->res->data = $userdata;
            }
        $this->_output();
              exit();
    }
     //---------------------*-------------------
    function notification()
    { 
        $request = json_decode(rtrim(file_get_contents('php://input'), "\0"));
        $user_id = $request->user_id;
        $where = array('notification.user_id' => $user_id);
                //CONCAT('w3resource','.','com')
            $res = $this->Core_Model->joindataResult($place1 ='notification.sender_id', $place2 = 'users.id',$where,'notification.notification_id,notification.sender_id,notification.notification_msg,notification.notification_connection_id,notification_connection_type,notification.notification_status,notification.is_read,users.f_name,users.l_name','users','notification',$order='');
            // print_r($res);die();
            $userdata = [];
            foreach ($res as $total) 
            {
                $userdata[] = array('id' => $total['notification_id'],'sender_id' => $total['sender_id'],'notification_connection_id' => $total['notification_connection_id'],'notification_msg' => $total['notification_msg'],'notification_connection_type' => $total['notification_connection_type'],'notification_status' => $total['notification_status'],'is_read' => $total['is_read'],'sender_name' => $total['f_name']." ".$total['l_name']);
                // print_r($userdata);die();
            }
            if (empty($userdata)) 
            {
                $this->res->success = false;
                $this->_error('error', 'Incorrect id or data.');
            } else
             {
                $this->res->success = 'true';
                $this->res->data = $userdata;
            }
        $this->_output();
              exit();
    }
     //---------------------*-------------------
    function promocode()
    { 
        $request = json_decode(rtrim(file_get_contents('php://input'), "\0"));
        $user_id = $request->user_id;
        $promocode = $request->promocode;
        $where = array('userId' => $user_id,'promoCode' => $promocode);
        $selectdata = 'discountPresent,startDate,endDate';
        $result = $this->Core_Model->SelectSingleRecord('promocode',$selectdata,$where,$order='');
        $startDate = $result->startDate;
        $endDate = $result->endDate;
        // print_r($endDate);die();
        date_default_timezone_set("Asia/Kolkata"); //current indian date/time
        $today = date("Y-m-d H:i:s");
        // echo $today;
        if ($startDate==$today or $endDate>=$today) {
            // echo"yes";
            $userdata[] = array('discountPercent' => $result->discountPresent);
            $this->res->success = 'true';
            $this->res->data = $userdata;   
        }
        else
        {
           $this->res->success = 'false';
           $this->res->message = "Invalid Promocode"; 
        }
        $this->_output();
              exit();
    }
    //---------------------*-------------------
    function vendor_rating()
    { 
        $request = json_decode(rtrim(file_get_contents('php://input'), "\0"));
        $service_id = $request->service_id;
        $where = "CONCAT(',', services_search, ',') 
            LIKE '%,".$service_id.",%' ";
            // print_r($where);die();
        $result = $this->Core_Model->SelectRecord('vendor_services', '*', $where, $order = '');

            // print_r($result);die();
        $vendor_services = [];
        foreach ($result as $value)
        {
            $vendor_services[] = array('vendor_id' => $value['vendor_id'],'charges' => $value['charges']);
        }
      
            $res = $this->Core_Model->joindataResult($place1 ='vendor_services.vendor_id', $place2 = 'users.id',$where,'vendor_services.id,vendor_services.vendor_id,vendor_services.charges,users.f_name,users.l_name','users','vendor_services',$order='');
            $userdata = [];
            foreach ($res as $key=>$total) 
            {
            // print_r($total);die();
                // print_r($userdata);die();
                $vendor_id = $total['vendor_id'];
                $where3 = array('receiverId' => $vendor_id);
                $result3 = $this->Core_Model->SelectRecord('review', '*', $where3, $order = '');

                $totalrating = 0;
                 foreach ($result3 as $total3) 
                {
                    $totalrating += $total3['rating'];
                }
                
                $rating = 0;
                if(count($result3)){
                    $rating = $totalrating/count($result3);    
                }
                   $userdata[] = array('service_id' => $total['id'],'vendor_id' => $total['vendor_id'],'charges' => $total['charges'],'vendor_name' => $total['f_name']." ".$total['l_name'],'rating' => $rating); 
            }
            // die;
            if (empty($userdata)) 
            {
                $this->res->success = false;
                $this->_error('error', 'Incorrect id or data.');
            } else
             {
                $this->res->success = 'true';
                $this->res->data = $userdata;
            }
        $this->_output();
              exit();
    }
    //---------------------*-------------------
    function _output() {
        header('Content-Type: application/json');
        //$this->res->request = $this->req->request;
        $this->res->datetime = date('Y-m-d\TH:i:sP');
        echo json_encode($this->res);
    }
    //---------------------*-------------------
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