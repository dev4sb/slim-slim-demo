<?php



ini_set('memory_limit','256M');

require '../vendor/autoload.php';
require 'helpers.php';
require 'config.php';
require 'Database.php';

/* ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);    */


date_default_timezone_set('Asia/Kolkata');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\UploadedFile;

function connectdb(){
    $db = new Database("krishna", "root", "", "localhost"); 
    return  $db;
}

 $dotenv = new Dotenv\Dotenv(__DIR__ . str_repeat(DIRECTORY_SEPARATOR . '..', 2));
 $dotenv->load();

/*$config = [
    'apiKey' => $_ENV['API_KEY'],
    'secret' => $_ENV['SECRET'],
    'host' => $_ENV['HOST']
];*/
$config = [
    'apiKey' => $_ENV['API_KEY'],
    'secret' => $_ENV['SECRET'],
    'host' => $_ENV['HOST'],
    'settings' => [
    // Slim Settings
    'determineRouteBeforeAppMiddleware' => true,
    'displayErrorDetails' => true,
    'addContentLengthHeader' => false]
];
$app = new \Slim\App($config);

$container = $app->getContainer();
$container['upload_directory'] = __DIR__ . '/uploads';




// function getAccessTokenfromDB($shop){

//     $db = connectdb();
//     $db->select("csp_sf-stores",array("domain_name"=>$shop));
//     $shop =$db->result_array();
//     $access_token = $shop[0]['access_token'];
//     return $access_token;
// }

 
// $app->get('/redirectInLiuqidfile', function ($request, $response) {
//     echo "test";
       
//        $db = connectdb();  //connectdb
//    $db->select("krisha");  //select query
//    $shop_data =$db->result_array(); 
//    print_r($shop_data);
//     $params = $request->getQueryParams();
    
//     });


// INSERT DATA

    $app->post('/insert', function ($request, $response) {
         $db = connectdb();
         $params = $request->getParsedBody();
         print_r($_FILES);
          $data=json_decode($params['data']);
          $firstname=$data->firstname;
          $email=$data->email;
          $dob=$data->dob;
          $gender=$data->gender;
          $hobby=implode(',', $data->hobby);
          $education=$data->education;
          $FilePath = "./img/". $firstname . ".png";
          $profile=$data->firstname.".png";
          move_uploaded_file($_FILES["pimage"]["tmp_name"], $FilePath);
         $insert=$db->insert( 'krisha',  array( "firstname"=> $firstname,"email"=>$email,"dob"=>$dob,"gender"=>$gender,"hobby"=>$hobby,"education"=>$education,"profile"=>$profile ) );
          if($insert)
          {
            echo json_encode(array("statusCode"=>200));
          }
          else
          {
            echo json_encode(array("statusCode"=>201));
          }
             
        });


        
// DELETE DATA

        $app->post('/delete', function ($request, $response) {
            $db = connectdb();
         $params = $request->getParsedBody();
          $id=$params['id'];
          $select=$db->select('krisha',array("id"=>$id));
          $shop_data =$db->result_array();
         // print_r($shop_data);
         foreach($shop_data as $x=>$x_value)
          {
           $row = array_values($x_value);
          // print_r($row);
          }
         // echo $row[9];
          $delete=$db->delete('krisha',array("id"=>$id));
         // print_r($delete);
          if($delete)
          {
            unlink('./img/'.$row[9]);
            echo json_encode(array("statusCode"=>200));
          }
          else
          {
            echo json_encode(array("statusCode"=>201));
         }
            
            });

//DELETE ALL
         
        $app->post('/deleteall',function($request,$response){
            $db =connectdb();
            $params = $request->getParsedBody();
            $post_ids = $params['post_id'];
            foreach($post_ids as $id)
            {
                $delete=$db->delete('krisha',array("id"=>$id));
            }
        });


// VIEW DATA

        $app->post('/view',function($request,$response){
          $db =connectdb();
          $params = $request->getParsedBody();
          $record_per_page=5;
          $page =1;
          $sort ='desc';
          $name ='id';
           if(isset($params['sort']))
           {
            $sort = $params['sort'];
           }
          if(isset($params['column_name']))
          {
            $name = $params['column_name'];
            //echo $name;
          }
          
          if(isset($params['page']))
          {
            $page = $params['page'];
          }
          

          $start_from =($page-1)*$record_per_page;

          $value="";
          $value='
          <table class="table table-bordered">
              <tr>
                <th><input type="checkbox" class="form-check-input" id="select_all"></th>
                <th class="column_sort" id="id ">ID<br/><input type="button" class="sort" id="desc" data-name="id" value="↓"><input type="button" class="sort" data-name="id" id="asc" value="↑"></th>
                <th class="column_sort" id="firstname "> Name<br/><input type="button" class="sort" id="desc" data-name="firstname" value="↓"><input type="button" class="sort" data-name="firstname" id="asc" value="↑"></th>
                <th class="column_sort" id="Email ">Email<br/> <input type="button" class="sort" id="desc" data-name="email"value="↓"><input type="button" class="sort" data-name="email" id="asc" value="↑"></th>
                <th class="column_sort" id="DOB ">DOB<br/> <input type="button" class="sort" id="desc" data-name="dob" value="↓"><input type="button" class="sort" data-name="dob" id="asc" value="↑"></th>
                <th class="column_sort" id="Gender ">Gender <br/> <input type="button" class="sort" id="desc" data-name="gender" value="↓"><input type="button" class="sort" data-name="gender" id="asc" value="↑"></th>
                <th class="column_sort" id="Hobby ">Hobby <br/><input type="button" class="sort" id="desc" data-name="hobby" value="↓"><input type="button" class="sort" data-name="hobby" id="asc" value="↑"></th>
                <th class="column_sort" id="Education ">Education <br/><input type="button" class="sort" id="desc" data-name="education" value="↓"><input type="button" class="sort" data-name="education" id="asc"value="↑"></th>
                <th>Profile</th>
                <th>Action</th>
              </tr>
          ';

          $view=$db->query("SELECT * FROM krisha ORDER BY $name $sort LIMIT $start_from,$record_per_page");
         
          $shop_data= $db->result_array();
          //print_r($shop_data);
          foreach($shop_data as $x=>$x_value)
          {
            $row = array_values($x_value);
            //print_r ($row[9]);
            $value.='<tr id="tr_'.$row[0].'">
            <td><input type="checkbox" class="form-check-input std_checkbox" id="del_'.$row[0].'"></td>
            <td>'.$row[0].'</td>
            <td>'.$row[1].'</td>
            <td>'.$row[3]. '</td>
            <td>'.$row[4].'</td>
            <td>'.$row[5].'</td>
            <td>'.$row[6].'</td>
            <td>'.$row[7].'</td>
            <td><img src="http://localhost/slim/slim/app/src/public/img/'.$row[9].'" style="height: 50px;width: 50px;"></td>
        <td><button class="btn btn-success"  id="btn_edit"  data-id2='.$row[0].'>Edit</button>
        <button type="button" class="btn btn-danger delete" data-id3='. $row[0].' name="delete">Delete</button></td></tr>';
            
            
          }
          $value.='</table><br/><div align="center">';
          $page_result=$db->query("SELECT * FROM krisha ORDER BY $name $sort");
          $total_records = sizeof($db->result_array());
          $total_pages = ceil($total_records/$record_per_page);
          //echo $total_pages;

          for($i=1;$i<=$total_pages;$i++)
          {
              $value.='<button class="pagination_link" style="cursor:pointer;padding:6px;border:1px solid #ccc;" id="'.$i.'">'.$i.'</button>';
          }

 $value.='</div><br/>';

          return json_encode(array('statusCode'=>'200','html'=>$value));

        });





//GET ALL DATA

        $app->post('/get',function($request,$response){
            $db =connectdb();
            $params = $request->getParsedBody();
            $id  = $params['id'];
            $select = $db->select('krisha',array("id"=>$id));
            $shop_data =$db->result_array();
            //print_r($shop_data);
            foreach($shop_data as $x => $x_value) {
                
                  $row = array_values($x_value);
                 // print_r ($row);
                  $Std_data = [];
                  $Std_data['id']=$row[0];
                  $Std_data['Firstname']=$row[1];
                  $Std_data['Email']=$row[3];
                  $Std_data['DOB']=$row[4]; 
                  $Std_data['Gender']=$row[5];
                  $Std_data['Hobby']=$row[6];
                  $Std_data['Education']=$row[7];  

              }
              echo json_encode($Std_data); 
            
        });

// UPDATE DATA

        $app->post('/update',function($request,$response){
          $db =connectdb();
          $params = $request->getParsedBody();
          print_r($_FILES);

    $data=json_decode($params['data']);
    $id=$data->id;
    $firstname=$data->firstname;
    $email=$data->email;
    $dob=$data->dob;
    $gender=$data->gender;
    $hobby=implode(',', $data->hobby);
    $education=$data->education;
    $profile=$data->firstname.".png";
          $up=$db->update( 'krisha', array("Firstname"=>$firstname,"Email"=>$email,"DOB"=>$dob,"Gender"=>$gender,"Hobby"=>$hobby,"Education"=>$education,"profile"=>$profile), array( "id"=> $id) );
          if($up)
          {
            echo "Update";
          }
          else{
            echo "not";
          }
          $FilePath = "./img/". $firstname . ".png";
        if(file_exists($FilePath)){
            unlink($FilePath);
        }
        move_uploaded_file($_FILES["pimage"]["tmp_name"], $FilePath);
        });



// SEARCH DATA

$app->post('/search',function($request,$response){
  $db =connectdb();
  $params = $request->getParsedBody();
  $record_per_page=5;
  $page ='';
  if(isset($params['page']))
  {
    $page = $params['page'];
  }
  else
  {
    $page = 1;
  }

  $start_from =($page-1)*$record_per_page;

  $value="";
  $value='<table class="table table-bordered">
      <tr>
        <th><input type="checkbox" class="form-check-input" id="select_all"></th>
        <th class="column_sort" id="id ">ID<br/><input type="button" class="sort" id="desc" data-name="id" value="↓"><input type="button" class="sort" data-name="id" id="asc" value="↑"></th>
                <th class="column_sort" id="firstname ">Firstname<br/><input type="button" class="sort" id="desc" data-name="firstname" value="↓"><input type="button" class="sort" data-name="firstname" id="asc" value="↑"></th>
                <th class="column_sort" id="Email ">Email<br/> <input type="button" class="sort" id="desc" data-name="email"value="↓"><input type="button" class="sort" data-name="email" id="asc" value="↑"></th>
                <th class="column_sort" id="DOB ">DOB<br/> <input type="button" class="sort" id="desc" data-name="dob" value="↓"><input type="button" class="sort" data-name="dob" id="asc" value="↑"></th>
                <th class="column_sort" id="Gender ">Gender <br/> <input type="button" class="sort" id="desc" data-name="gender" value="↓"><input type="button" class="sort" data-name="gender" id="asc" value="↑"></th>
                <th class="column_sort" id="Hobby ">Hobby <br/><input type="button" class="sort" id="desc" data-name="hobby" value="↓"><input type="button" class="sort" data-name="hobby" id="asc" value="↑"></th>
                <th class="column_sort" id="Education ">Education <br/><input type="button" class="sort" id="desc" data-name="education" value="↓"><input type="button" class="sort" data-name="education" id="asc"value="↑"></th>
                <th>Profile</th>
                <th>Action</th>
      </tr>
  ';
  if(isset($params['search_param'])){
    $search_param = mysqli_real_escape_string(mysqli_connect("localhost","root","","krishna"),$params['search_param']);
  $db->query("SELECT * FROM krisha WHERE firstname Like '%$search_param%' OR Lastname Like '%$search_param%' OR Email Like '%$search_param%' OR DOB Like '%$search_param%' OR Gender Like '$search_param%' OR Hobby Like '%$search_param%' OR Education Like '%$search_param%'  ORDER BY id DESC LIMIT $start_from,$record_per_page");
  $shop_data= $db->result_array();
  foreach($shop_data as $x=>$x_value)
  {
    $row = array_values($x_value);
    //print_r ($row[1]);
    $value.='<tr id="tr_'.$row[0].'">
    <td><input type="checkbox" class="form-check-input std_checkbox" id="del_'.$row[0].'"></td>
    <td>'.$row[0].'</td>
    <td>'.$row[1].'</td>
    <td>'.$row[3]. '</td> 
    <td>'.$row[4].'</td>
    <td>'.$row[5].'</td>
    <td>'.$row[6].'</td>
    <td>'.$row[7].'</td>
    <td><img src="http://localhost/slim/slim/app/src/public/img/'.$row[9].'" style="height: 50px;width: 50px;"></td>
<td><button class="btn btn-success"  id="btn_edit"  data-id2='.$row[0].'>Edit</button>
<button type="button" class="btn btn-danger delete" data-id3='. $row[0].' name="delete">Delete</button></td></tr>';
    
    
  }
  $value.='</table><br/><div align="center">';
  $page_result=$db->query("SELECT * FROM krisha WHERE firstname Like '%$search_param%'  OR Email Like '%$search_param%' OR DOB Like '%$search_param%' OR Gender Like '$search_param%' OR Hobby Like '%$search_param%' OR Education Like '%$search_param%'  ORDER BY id DESC");
  $total_records = sizeof($db->result_array());
  $total_pages = ceil($total_records/$record_per_page);
  //echo $total_pages;

  for($i=1;$i<=$total_pages;$i++)
  {
      $value.='<button class="pagination_link" style="cursor:pointer;padding:6px;border:1px solid #ccc;" id="'.$i.'">'.$i.'</button>';
  }

$value.='</div><br/>';

  echo $value;

}
});




//LOGIN 

$app->post('/login',function($request,$response){
  $db =connectdb();
  $params = $request->getParsedBody(); 
  $email=$params['email'];
  $password=$params['password'];
  if($password=="krishna@123")
  {
    $db->select('krisha',array("Email"=>$email));
    $shop_data=$db->result_array();
    
    $total=sizeof($shop_data);
    
  
    echo json_encode(array("statusCode"=>200));
  }
  else
  {
    echo json_encode(array("statusCode"=>201));
   
  }

});


//Logout
$app->post('/logout',function($request,$response){
  $db =connectdb();
  session_start();  
session_destroy();  
});


$app->run();
