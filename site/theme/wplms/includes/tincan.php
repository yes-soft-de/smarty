<?php

if ( !defined( 'ABSPATH' ) ) exit;

/* ===== TINCAN API COMPATIBILITY ====== */
class wplms_tincan{

    public static $endpoint;
    public static $auth;
    
    function __construct(){
        $on = vibe_get_option('tincan');
        if(isset($on) && $on){
            $this->initialize();
            add_action('bp_activity_add',array($this,'initialize_tincan_statements'),10,1);
            add_action('init', array($this, 'add_endpoint'));
            add_action( 'template_redirect', array($this,'record_articulate_statements'));
        }
    }

    function initialize(){
        $this->endpoint = vibe_get_option('tincan_endpoint');
        $user = vibe_get_option('tincan_user');
        $pass = vibe_get_option('tincan_pass');
        $cred = $user.':'.$pass;
        $this->auth = base64_encode($cred);
    }

    function open_connection($data, $url){ 

        $streamopt = array(
            'ssl' => array(
                'verify-peer' => false,
            ),
            'http' => array(
                'method' => 'POST',
                'ignore_errors' => true,
                'header' =>  array(
                    'Authorization: '.sprintf('Basic %s',$this->auth ),
                    'Content-Type: application/json',
                    'Accept: application/json, */*; q=0.01',
                ),
                'content' => json_encode($data),
            ),
        );
       
       /*$myFile = "tincan_course.txt";
        if (file_exists($myFile)) {
          $fh = fopen($myFile, 'a');
          fwrite($fh, print_r($streamopt, true)."\n");
        } else {
          $fh = fopen($myFile, 'w');
          fwrite($fh, print_r($streamopt, true)."\n");
        }
        fwrite($fh, print_r(json_encode($data, JSON_PRETTY_PRINT), true)."\n");
        fclose($fh);  */

        $context = stream_context_create($streamopt);
        $stream = fopen($url, 'rb', false, $context);
        $ret = stream_get_contents($stream);
        $meta = stream_get_meta_data($stream);
        if ($ret) {
            $ret = json_decode($ret);
        }
        return array($ret, $meta);
    }

    function make_request($args){
        $statements = $this->build_statements($args);
        return $this->open_connection($statements, $this->endpoint);
    }

    function record_statements($args){
        list($resp, $meta) =  $this->make_request($args);  
    }

    function build_statements($args){
        
        $defaults = array(
            'id'                => false,
            'action'            => '',   
            'content'           => '',   
            'component'         => false,
            'type'              => false,
            'primary_link'      => '',
            'user_id'           => '',
            'item_id'           => false,
            'secondary_item_id' => false,
            'recorded_time'     => ''
        );

        $params = wp_parse_args( $args, $defaults );
        extract( $params, EXTR_SKIP );
        $tincan_statement = array();
        $recorded_time = str_replace(' ','T',$recorded_time);
        //VERB LIST : “experienced”, “attended”, “attempted”, “completed”, “passed”, “failed”, “answered”, “interacted”, “imported”, “created”, “shared”, and “voided”
        $actor=array(
                            'name' =>  array(get_the_author_meta('display_name', $user_id)),
                            'mbox'  => array('mailto:'.get_the_author_meta('user_email',$user_id)),
                            'objectType' => 'Person',
                        );
            
        switch($type){
            case 'unit_complete':
            $instructor =get_post_field('post_author',$secondary_item_id);
            $tincan_statement=array(
                array(
                    'actor' => $actor,
                    'verb' => 'experienced',
                    'object' => array(
                        'objectType' => 'Activity',
                        'id' => $primary_link,
                        'definition' => array(
                            'name' => array('en-US'=>get_the_title($item_id)),
                            'description' => array('en-US'=> $action),
                        ),
                    ),
                    'context' => array(
                        'instructor' => array(
                            'name' => array(get_the_author_meta('display_name', $instructor)),
                            'mbox' => array('mailto:'.get_the_author_meta('user_email',$instructor)),
                            'objectType'=>'Person'
                        ),
                        'contextActivities' =>array(
                            'parent' => array(
                                'id' => get_permalink($secondary_item_id),
                                ),
                            ),

                    ),
                    'result' => array(
                        'completion' => 'true',
                        'success' => 'true',
                    ),
                    'timestamp' => $recorded_time,
                )
            );
            break;
            case 'assignment_started':
            case 'start_quiz':
            case 'start_course':
            $tincan_statement=array(
                array(
                    'actor' => $actor,
                    'verb' => 'attempted',
                    'object' => array(
                        'objectType' => 'Activity',
                        'id' => $primary_link,
                        'definition' => array(
                            'name' => array('en-US'=>get_the_title($item_id)),
                            'description' => array('en-US'=> $action),
                        ),
                    ),
                    'timestamp' => $recorded_time
                )
            );
            break;
            case 'assignment_submitted':
            case 'submit_quiz':
            case 'submit_course':
            $tincan_statement=array(
                array(
                    'actor' => $actor,
                    'verb' => 'experienced',
                    'object' => array(
                        'objectType' => 'Activity',
                        'id' => $primary_link,
                        'definition' => array(
                            'name' => array('en-US'=>get_the_title($item_id)),
                            'description' => array('en-US'=> $action),
                        ),
                    ),
                    'timestamp' => $recorded_time,
                )
            );
            break;
            case 'evaluate_quiz':
            case 'quiz_evaluated':
            $marks_content = explode('= ',$content);
            $marks = explode('/',$marks_content[1]);
            $marks[0] = intval($marks[0]);$marks[1] = intval($marks[1]);
            if(!isset($marks[1]) || !$marks[1])$marks[1]=1;
            $tincan_statement=array(
                array(
                    'actor' => $actor,
                    'verb' => 'completed',
                    'object' => array(
                        'objectType' => 'Activity',
                        'id' => $primary_link,
                        'definition' => array(
                            'name' => array('en-US'=>get_the_title($item_id)),
                            'description' => array('en-US'=> $action),
                        ),
                    ),
                    'result' => array(
                        'score'=> array(
                            'raw'=> $marks[0],
                            'min'=> 0,
                            'max' => $marks[1]
                        ),
                        'success' => 'true',
                        'completion' => 'true',
                    ),
                    'timestamp' => $recorded_time,
                )
            );            
            break;
            case 'course_evaluated':
            
            $marks_content = explode('=',$content);
            $marks = explode('/',$marks_content[3]);
            $marks[0] = intval($marks[0]);
            $course_marks=$marks[0];
            if(!isset($course_marks) || !$course_marks)
                $course_marks=1;

            $pass = get_post_meta($item_id,'vibe_course_passing_percentage',true);
            $verb='completed';
            if($course_marks < $pass)
                $verb='failed';
            
            $tincan_statement=array(
                array(
                    'actor' => $actor,
                    'verb' => $verb,
                    'object' => array(
                        'objectType' => 'Activity',
                        'id' => $primary_link,
                        'definition' => array(
                            'name' => array('en-US'=>get_the_title($item_id)),
                            'description' => array('en-US'=> $action),
                        ),
                    ),
                    'result' => array(
                        'completion' => true,
                        'success' => true,
                        'score'=> array(
                            'raw' => $course_marks,
                            'min' => 0,
                            'max' => 100
                        ),
                    ),
                    'timestamp' => $recorded_time,
                )
            );
            break;
            case 'evaluate_assignment':
            $marks_content = explode('=',$content);
            $marks = explode('/',$marks_content[3]);
            $marks[0] = intval($marks[0]);
            $assignment_marks=$marks[0];
            if(!isset($assignment_marks) || !$assignment_marks)
                $assignment_marks=1;

            $tincan_statement=array(
                array(
                    'actor' => $actor,
                    'verb' => 'completed',
                    'object' => array(
                        'objectType' => 'Activity',
                        'id' => $primary_link,
                        'definition' => array(
                            'name' => array('en-US'=>get_the_title($item_id)),
                            'description' => array('en-US'=> $action),
                        ),
                    ),
                    'result' => array(
                        'completion' => true,
                        'success' => true,
                        'score'=> array(
                            'raw'=>$assignment_marks,
                            'min' => 0,
                            'max' => intval($marks[0])
                        ),
                    ),
                    'timestamp' => $recorded_time,
                )
            );
            break;
            case 'student_certificate':
            $tincan_statement=array(
                array(
                    'actor' => $actor,
                    'verb' => 'passed',
                    'object' => array(
                        'objectType' => 'Activity',
                        'id' => $primary_link,
                        'definition' => array(
                            'name' => array('en-US'=>get_the_title($item_id)),
                            'description' => array('en-US'=> $action),
                        ),
                    ),
                    'timestamp' => $recorded_time,
                )
            );
            break;
            case 'student_badge':
            $tincan_statement=array(
                array(
                    'actor' => $actor,
                    'verb' => 'passed',
                    'object' => array(
                        'objectType' => 'Activity',
                        'id' => $primary_link,
                        'definition' => array(
                            'name' => array('en-US'=>get_the_title($item_id)),
                            'description' => array('en-US'=> $action),
                        ),
                    ),
                    'timestamp' => $recorded_time,
                )
            );
            break;
            case 'activity_comment': //BIG ISSUE in BUDDYPRESS does not Collect Comment ID or POST ID
            $post_id=url_to_postid( $primary_link);
            $title=get_the_title($post_id);
            $tincan_statement=array(
                array(
                    'actor' => $actor,
                    'verb' => 'answered',
                    'object' => array(
                        'objectType' => 'Activity',
                        'id' => $primary_link,
                        'definition' => array(
                            'name' => array('en-US'=>$title),
                            'description' => array('en-US'=> $title),
                        ),
                    ),
                    'timestamp' => $recorded_time,
                )
            );

            break;
            case 'review_course':
                $tincan_statement=array(
                    array(
                        'actor' => $actor,
                        'verb' => 'review',
                        'object' => array(
                            'objectType' => 'Activity',
                            'id' => $primary_link,
                            'definition' => array(
                                'name' => array('en-US'=>get_the_title($item_id)),
                                'description' => array('en-US'=> $action),
                            ),
                        ),
                        'timestamp' => $recorded_time,
                    )
                );
            break;
            default:
            break;
        }
       
        return $tincan_statement;
    }

    function initialize_tincan_statements($args){
        $allowed_array= apply_filters('wplms_tincan_allowed_bp_type',array('start_course',
            'unit_complete','submit_course','start_quiz','submit_quiz','quiz_evaluated',
            'assignment_started','assignment_submitted','evaluate_assignment','course_evaluated','review_course',
            'student_badge','student_certificate','activity_comment','evaluate_quiz',
            'experienced','attempted','attended','completed','passed','failed','interacted','answered','imported','created',
            'shared','voided'));
        if(in_array($args['type'],$allowed_array))
            $statements = $this->record_statements($args);
    }

    function articulate_payload($record,$referer){
        $user_id = get_current_user_id();
        $object = $this->parse_xml($record['object'],$referer);
        $course = $this->parse_xml($record['courseid'],$referer);
        bp_course_record_activity(array(
          'action' => __('Student ','vibe').$record['verb'].' '.$object,
          'content' => __('Student ','vibe').bp_core_get_userlink( $user_id ).' '.$record['verb'].' '.$object .__(' in ','vibe').$course,
          'type' => $record['verb'],
          'item_id' => $record['courseid'],
          'primary_link'=> $referer
        ));   
    }

    function parse_xml($object,$referer){
        $grab_xml=explode('?',$referer);
        $tincanxml = str_replace('story.html','tincan.xml',$grab_xml[0]);
        $string = file_get_contents($tincanxml);
        $string = str_replace('xmlns="http://projecttincan.com/tincan.xsd"','',$string);
        $xml = simplexml_load_string($string); 
        $result = $xml->xpath("//activity[@id='$object']"); 
        return $result[0]->name;
    }

    public function add_endpoint(){
        add_rewrite_endpoint( 'statements', EP_ALL);
    }

    function check_statements($vars){
        $vars['statements'] = true;
        return $vars;
    }

    public function record_articulate_statements() {
        global $wp_query;
        if ( ! isset( $wp_query->query_vars['statements'] ) ){
           return;
        }
        include dirname( __FILE__ ) . '/tincanapi.php';
        exit;
    }
}    

$wplms_tincan = new wplms_tincan();

?>