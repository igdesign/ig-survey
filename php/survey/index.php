<?php
require_once('./configuration.php');
require_once('./survey.functions.php');
$config = new Configuration;


/**
 * CORS
 */
if (!isset($_SERVER['HTTP_ORIGIN'])) {
  $_SERVER['HTTP_ORIGIN'] = 'http://survey.igdesign.ca';
}
switch ($_SERVER['HTTP_ORIGIN']) {
  case 'http://'.$config->site_url:
  case 'https://'.$config->site_url:

    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Max-Age: 1000');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    break;
}





$method = $_SERVER['REQUEST_METHOD'];
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
  $survey->verifyPost();

  // store entry
  $survey->storePost();

  // send email response
  $survey->sendMail(); // Temporary comment out

  // respond that we have stored the data
  unset($survey->post['jsondata']);

  echo  stripslashes(json_encode($survey->post));

  return;
}


function processRequest()
{

  // return location value
  if (isset($_GET['location']))
  {
    $survey = new SurveyFunctions();
    $location = $survey->getLocation();

    echo $location;
    return;
  }

  // output entries list
  if (isset($_GET['entries']))
  {
    $survey = new SurveyFunctions();
    $entries = $survey->getEntries();

    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=survey_results-".date("Y-m-d H:i:s").".csv");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo $entries;
    return;
  }
}