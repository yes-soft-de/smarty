<?php

if ( !defined( 'ABSPATH' ) ) exit;

get_header(vibe_get_header());

$request_body = file_get_contents('php://input');
$request_body = urldecode($request_body);
/*

$pattern = '
/
\{              # { character
    (?:         # non-capturing group
        [^{}]   # anything that is not a { or }
        |       # OR
        (?R)    # recurses the entire pattern
    )*          # previous group zero or more times
\}              # } character
/x
';

preg_match_all($pattern, $request_body, $matches);

$object = json_decode($matches[0][0]);
$array = json_decode(json_encode($object), true);

print_r('Verb '.$array['verb']['display']['en-US']);
print_r('Object '.$array['object']['objectType']);
print_r('Result '.$array['result']['score']['raw']);

print_r('Verb '.$array['verb']['display']['en-US']);
print_r('Object '.$array['object']['objectType']);

//For quizzes
print_r('Completion '.$array['result']['completion']);  
print_r('Result '.$array['result']['score']['scaled']);

*/
$malformed_jsons = explode('"',$request_body);
$record=array();
foreach($malformed_jsons as $key=>$value){
    if(strstr($value,'course_id')){
        $record['courseid'] = $value;
    }else if($value == 'verb'){
        $record['verb'] = $malformed_jsons[$key+2];
    }else if($value == 'object'){
        $record['object'] = $malformed_jsons[$key+4];
    }
}
//print_r($record);

$wplms_tincan = new wplms_tincan();

$wplms_tincan->articulate_payload($record,$_SERVER['HTTP_REFERER']);
get_footer(vibe_get_footer());
?>
