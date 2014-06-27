<?php
require_once('./configuration.php');
require_once('./survey.functions.php');
$config = new Configuration;


/**
 * CORS
 */
if (!isset($_SERVER['HTTP_ORIGIN'])) {
  $_SERVER['HTTP_ORIGIN'] = null;
}

if ($config->dev === true) {
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
  header('Access-Control-Max-Age: 1000');
  header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');


} else {
  switch ($_SERVER['HTTP_ORIGIN']) {
    case 'http://'.$config->site_url:
    case 'https://'.$config->site_url:

      header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
      header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
      header('Access-Control-Max-Age: 1000');
      header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
      break;
  }
}





$method = $_SERVER['REQUEST_METHOD'];






// TEMPORARY
/* $method = 'GET'; */
/*
$_POST = array('favourite_three' => array(
                 'apples'
               , 'oranges'
               , 'bananas'
               )

             , 'local_two' => array(
                 'cliffs over maple bay'
               , 'cowichan'
               )

             , 'hidden_gem' => array(
                 'quartz'
               , 'amethyst'
               , 'garnet'
               )

             , 'best_manicure' => 'Matisse'
             , 'location' => 'Duncan, BC'
             , 'pga_id'   => '1234567890'
             , 'email'    => 'gregdaynes@gmail.com'
             , 'state'    => 'BC'

             );
*/
// TEMPORARY







switch ($method)
{
  case 'POST':
    recordData();
    break;

  case 'GET':
    processRequest();
    break;
}

function recordData()
{

  /**
   * Setup Survey
   */

  $survey = new SurveyFunctions();

  // check post data
  $survey->verifyPost($_POST);

  // store entry
  $survey->storePost();

  // send email response
/*   $survey->sendMail(); */ // Temporary comment out
}


function processRequest()
{
  // return location value
  if (isset($_GET['location']))
  {
    $survey = new SurveyFunctions();
    $location = $survey->getLocation();

    echo json_encode($location);
    return;
  }

  // output entries list
  if (isset($_GET['entries']))
  {
    $survey = new SurveyFunctions();
    $entries = $survey->getEntries();
    return;
  }
}