<?php
class SurveyFunctions
{
  private $config; // object

  public  $post;

  public function __construct()
  {
    require_once('./configuration.php');

    $this->config = new Configuration;



  }




  public function escapeData($data, $connection = false)
  {
    if (!$connection) {
      // create connection
      $connection =  new mysqli($this->config->db_host
                              , $this->config->db_user
                              , $this->config->db_pass
                              , $this->config->db_file
                              );

      // check connection
      if ($connection->connect_error) {
        trigger_error('Database connection failed: '  . $connection->connect_error, E_USER_ERROR);
      }
    }


  	if (is_array($data)) {
  		foreach ($data as $elem) {
  			$this->escapeData($elem, $connection);
  		}
  	} else {
  		$invalid_characters = array("$", "%", "#", "<", ">", "|");
      $data = str_replace($invalid_characters, "", $data);
      $data = $connection->real_escape_string($data);
  	}

    return $data;

    // close connection
    $connection->close();
  }





  /**
   * verifyPost
   *
   * check data submitted
   */
  public function verifyPost()
  {
    $post = $_POST;

    if (!isset($post['email']))
    {
      $post['email'] = '';
    }

    $data = array();

    $data['datetime'] = date("Y-m-d H:i:s");
    $data['ipaddress'] = $_SERVER['REMOTE_ADDR'];
    $data['state'] = $_POST['state'];

    if (isset($post['email'])) {
      $data['email'] = $post['email'];
      unset($post['email']);
    }


    $post = $this->escapeData($post);

    $data['jsondata'] = json_encode($post);

    return $this->post = $data;
  }





  /**
   * storePost
   *
   */
  public function storePost()
  {
    // create connection
    $connection =  new mysqli($this->config->db_host
                            , $this->config->db_user
                            , $this->config->db_pass
                            , $this->config->db_file
                            );

    // check connection
    if ($connection->connect_error) {
      trigger_error('Database connection failed: '  . $connection->connect_error, E_USER_ERROR);
    }


    $data = $this->post;
    $data['jsondata'] = $connection->real_escape_string($data['jsondata']);

    $sql = 'INSERT INTO `entries_json` (`jsondata`
                                      , `ipaddress`
                                      , `dateTime`
                                      , `email`
                                      , `state`
                                      )
                                VALUES ("'.$data['jsondata'].'"
                                      , "'.$data['ipaddress'].'"
                                      , "'.$data['datetime'].'"
                                      , "'.$data['email'].'"
                                      , "'.$data['state'].'"
                                      );';

    if ($connection->query($sql) === false) {
      trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $connection->error, E_USER_ERROR);
    } else {
      $last_inserted_id = $connection->insert_id;
      $affected_rows = $connection->affected_rows;
    }

    // close connection
    $connection->close();

  }






  public function sendMail()
  {
    $email = $this->_createEmail();
    $email = json_encode($email);

    $api_url = $this->config->md_base_url.'messages/send.json';

    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$api_url);
    curl_setopt($ch,CURLOPT_POST,count($email));
    curl_setopt($ch,CURLOPT_POSTFIELDS, $email);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //execute post
    $result = curl_exec($ch);

    //close connection
    curl_close($ch);

    return;
  }






  private function _createEmail()
  {

    $data = $this->post;

    if (isset($data['email'])) {
    	$email_address = '<br /><p>Email entered: '.$data['email'];
    }


    $email = array(
      'key' => $this->config->md_api_key,
      'message' => array(
        'subject' => 'Thank you for participating in Inside Golf\'s Top Tracks Survey',
        'from_email'    => $this->config->md_from_email,
        'from_name'     => $this->config->md_from_name,
        'to'      => array(
          array(
            'email' => $this->post['email']
          )
        , array(
            'email' => $this->config->md_bcc_email,
            'name' => $this->config->md_bcc_name
          )
        ),
        'html'  => '<h2>Thank you for participating&hellip;</h2>
                    <p>If you would like to see the results of the survey, you can do so by signing up for Inside Golf\'s top rated newsletter&hellip;</p>
                    <a href="http://eepurl.com/bkZRb">Sign up for our free newsletter!</a>'
      )
    );

    return $email;
  }






  /**
   * Lookup location
   */

  public function getLocation() {

    // get ip address
    $ipAddress = $_SERVER['REMOTE_ADDR'];
//     $ipAddress = '96.54.0.215'; // TEMPORARY

    // split ipAddress into usable chunks
    $ipAddress = explode('.', $ipAddress);

    // maths for equation
    $ipAddress[0] = 16777216 * $ipAddress[0];
    $ipAddress[1] = 65536 * $ipAddress[1];
    $ipAddress[2] = 256 * $ipAddress[2];

    $ipAddress = $ipAddress[0] + $ipAddress[1] + $ipAddress[2] + $ipAddress[3];



    // create connection
    $connection =  new mysqli($this->config->db_host
                            , $this->config->db_user
                            , $this->config->db_pass
                            , $this->config->db_file
                            );

    // check connection
    if ($connection->connect_error) {
      trigger_error('Database connection failed: '  . $connection->connect_error, E_USER_ERROR);
    }


    $query='SELECT locId'
         .' FROM blocks'
         .' WHERE '. $ipAddress .' BETWEEN startIpNum AND endIpNum'
         .' LIMIT 1';

    $response = $connection->query($query);


    if($response === false) {
      trigger_error('Wrong SQL: ' . $query . ' Error: ' . $connection->error, E_USER_ERROR);
      return false;
    }


    while($row = $response->fetch_assoc()){
      $location = $row['locId'];
    }

    if (!$location) {
      return false;
    }

    // lookup details for location
    $query = ' SELECT country'
            .', region'
            .', city'
            .', latitude'
            .', longitude'
            .' FROM locations'
            .' WHERE locId = '.$location
            ;

    $response = $connection->query($query);

    if($response === false) {
      trigger_error('Wrong SQL: ' . $query . ' Error: ' . $connection->error, E_USER_ERROR);
      return false;
    }

    while($row = $response->fetch_assoc()){
      $results = $row;
    }

    // close connection
    $connection->close();

    return (string) $results['city'].', '.$results['region'];;
  }


  // http://stackoverflow.com/questions/526556/how-to-flatten-a-multi-dimensional-array-to-simple-one-in-php
  public function flatten($array, $prefix = '') {
    $result = array();
    foreach($array as $key=>$value) {
        if(is_array($value)) {
            $result = $result + $this->flatten($value, $prefix . $key . '.');
        }
        else {
            $result[$prefix . $key] = $value;
        }
    }
    return $result;
  }




  public function getEntries()
  {

    // password restriction
    if (!isset($_GET[$this->config->getPassword])) {
      return;
    }

    // create connection
    $connection =  new mysqli($this->config->db_host
                            , $this->config->db_user
                            , $this->config->db_pass
                            , $this->config->db_file
                            );

    // check connection
    if ($connection->connect_error) {
      trigger_error('Database connection failed: '  . $connection->connect_error, E_USER_ERROR);
    }


    $query = ' SELECT *'
            .' FROM entries_json'
            ;

    $response = $connection->query($query);

    if($response === false) {
      trigger_error('Wrong SQL: ' . $query . ' Error: ' . $connection->error, E_USER_ERROR);
      return false;
    }


    $column_header = array();
    $rows = array();

    while ($row = $response->fetch_object()) {

      $row->jsondata = (array) json_decode($row->jsondata);
      $row->jsondata = $this->flatten($row->jsondata);

      foreach($row->jsondata as $field => $value) {
        $row->$field = $value;
        $column_header[$field] = $field;
      }
      unset($row->jsondata);
      $rows[] = implode(', ', (array) $row);



    }
    echo implode(', ', $column_header)."\n";

    foreach($rows as $row) {
      echo $row."\n";
    }
  }
}
