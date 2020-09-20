<?php

class WPLMS_Content_Templates {


	public static $instance;
    
    public static function init(){
        if ( is_null( self::$instance ) )
            self::$instance = new WPLMS_Content_Templates;
        return self::$instance;
    }

	private function __construct(){

	}

	function get_template($post_type,$type){
		switch($post_type){
			case 'question':
				$this->settings = array(
					'post_content'=>'',
					'meta_fields'=> array(
						'vibe_question_type'=>'',
						'vibe_question_options'=>'',
						'vibe_question_answer'=>'',
						'vibe_question_hint'=>'',
						'vibe_question_explaination'=>''
						)
					);
				self::get_question_templates($type);
			break;
		}

		return $this->settings;
	}

	function get_question_templates($type){

		switch($type){
			case 'single':
				$this->settings['post_content'] = __('Question Statement : Which is the largest continent in the World ?','wplms-front-end');
				$this->settings['meta_fields'] = array(
						'vibe_question_type'=> $type,
						'vibe_question_options'=>array(__('Asia','wplms-front-end'),__('America','wplms-front-end'),__('Europe','wplms-front-end'),__('Australia','wplms-front-end')),
						'vibe_question_answer'=>'1',
						'vibe_question_hint'=> __('Continent with Russia','wplms-front-end'),
						'vibe_question_explaination'=>__('A continent is one of several very large landmasses on Earth. They are generally identified by convention rather than any strict criteria, with up to seven regions commonly regarded as continents.','wplms-front-end')
					);
			break;
			case 'multiple':
				$this->settings['post_content'] = __('Question Statement : This question can have multiple answers.','wplms-front-end');
				$this->settings['meta_fields'] = array(
						'vibe_question_type'=> $type,
						'vibe_question_options'=>array(__('Option 1','wplms-front-end'),__('Option 2','wplms-front-end'),__('Option 3','wplms-front-end'),__('Option 4','wplms-front-end')),
						'vibe_question_answer'=>'2,3',
						'vibe_question_hint'=> __(' The answer to this quesiton is 2,3','wplms-front-end'),
						'vibe_question_explaination'=>__('Some explaination to this question.','wplms-front-end')
					);
			break;
			case 'truefalse':
				$this->settings['post_content'] = __('Question Statement : True and False question type.','wplms-front-end');
				$this->settings['meta_fields'] = array(
						'vibe_question_type'=> $type,
						'vibe_question_answer'=>'1',
						'vibe_question_hint'=> __(' True','wplms-front-end'),
						'vibe_question_explaination'=>__('Some explaination to this question.','wplms-front-end')
					);
			break;
			case 'select':
				$this->settings['post_content'] = __('Question Statement : Select correct answer out of the following','wplms-front-end').' [select options="1,2,3"] and another [select options="4,5,6"]';
				$this->settings['meta_fields'] = array(
						'vibe_question_type'=> $type,
						'vibe_question_options'=>array(__('Option 1','wplms-front-end'),__('Option 2','wplms-front-end'),__('Option 3','wplms-front-end'),__('Option 4','wplms-front-end'),__('Option 5','wplms-front-end'),__('Option 6','wplms-front-end')),
						'vibe_question_answer'=>'1|4',
						'vibe_question_hint'=> __(' Option 1 and Option 4','wplms-front-end'),
						'vibe_question_explaination'=>__('Some explaination to this question.','wplms-front-end')
					);
			break;
			case 'sort':
				$this->settings['post_content'] = __('Question Statement : Arrange the below options in following order: 4,3,2,1','wplms-front-end');
				$this->settings['meta_fields'] = array(
						'vibe_question_type'=> $type,
						'vibe_question_options'=>array(__('Option 1','wplms-front-end'),__('Option 2','wplms-front-end'),__('Option 3','wplms-front-end'),__('Option 4','wplms-front-end')),
						'vibe_question_answer'=>'4,3,2,1',
						'vibe_question_hint'=> '4,3,2,1',
						'vibe_question_explaination'=>__('Some explaination to this question.','wplms-front-end')
					);
			break;
			case 'match':
				$this->settings['post_content'] = __('Question Statement : Arrange the below options in following order: 4,3,2,1','wplms-front-end').'<br />[match]<ul><li>'.__('First Order','wplms-front-end').'</li><li>'.__('Second Order','wplms-front-end').'</li><li>'.__('Third order','wplms-front-end').'</li><li>'.__('Fourth Order','wplms-front-end').'</li></ul>[/match]';
				$this->settings['meta_fields'] = array(
						'vibe_question_type'=> $type,
						'vibe_question_options'=>array(__('Option 1','wplms-front-end'),__('Option 2','wplms-front-end'),__('Option 3','wplms-front-end'),__('Option 4','wplms-front-end')),
						'vibe_question_answer'=>'4,3,2,1',
						'vibe_question_hint'=> '4,3,2,1',
						'vibe_question_explaination'=>__('Some explaination to this question.','wplms-front-end')
					);
			break;
			case 'fillblank':
				$this->settings['post_content'] = __('Question Statement : Fill in the blank','wplms-front-end').' [fillblank] and another [fillblank]';
				$this->settings['meta_fields'] = array(
						'vibe_question_type'=> $type,
						'vibe_question_options'=>array(),
						'vibe_question_answer'=>'somevalue|anothervalue',
						'vibe_question_hint'=> __('some value','wplms-front-end'),
						'vibe_question_explaination'=>__('Some explaination to this question.','wplms-front-end')
					);
			break;
			case 'smalltext':
				$this->settings['post_content'] = __('Question Statement : Enter the answer in below text box','wplms-front-end');
				$this->settings['meta_fields'] = array(
						'vibe_question_type'=> $type,
						'vibe_question_options'=>'',
						'vibe_question_answer'=> __('some answer','wplms-front-end'),
						'vibe_question_hint'=> __('some hint','wplms-front-end'),
						'vibe_question_explaination'=>__('Some explaination to this question.','wplms-front-end')
					);
			break;
			case 'survey':
				$this->settings['post_content'] = __('Survey Question : How likely are you to going to pick the number 7 out of 0 to 10 ?','wplms-front-end');
				$this->settings['meta_fields'] = array(
						'vibe_question_type'=> $type,
						'vibe_question_options'=>array('1','2','3','4','5'),
						'vibe_question_answer'=>'4',
						'vibe_question_hint'=> __('Help text for survey question. Each choice has marks used to end result','wplms-front-end'),
						'vibe_question_explaination'=>__('Scores are connected with the marked answer','wplms-front-end')
					);
			break;
			case 'largetext':
				$this->settings['post_content'] = __('Question Statement :Enter the answer in below text area','wplms-front-end');
				$this->settings['meta_fields'] = array(
						'vibe_question_type'=> $type,
						'vibe_question_options'=>'',
						'vibe_question_answer'=> __('some answer','wplms-front-end'),
						'vibe_question_hint'=> 'some hint',
						'vibe_question_explaination'=>__('Some explaination to this question.','wplms-front-end')
					);
			break;
			default:
	            $this->settings = apply_filters('wplms_front_end_question_template',$this->settings,$type);
	        break;
		}
	}
}