<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class PMS_Success {

    /**
     * Success messages
     *
     * @access public
     * @var array
     */
    public $messages = array();


    public function __construct( $code = '', $message = '' ) {
        if ( empty($code) )
            return;

        $this->messages[$code][] = $message;
    }


    public function get_message_codes() {
        if ( empty($this->messages) )
            return array();

        return array_keys($this->messages);
    }


    public function get_message_code() {
        $codes = $this->get_message_codes();

        if ( empty($codes) )
            return '';

        return $codes[0];
    }


    public function get_messages($code = '') {
        // Return all messages if no code specified.
        if ( empty($code) ) {
            $all_messages = array();
            foreach ( (array) $this->messages as $code => $messages )
                $all_messages = array_merge($all_messages, $messages);

            return $all_messages;
        }

        if ( isset($this->messages[$code]) )
            return $this->messages[$code];
        else
            return array();
    }


    public function get_message($code = '') {
        if ( empty($code) )
            $code = $this->get_message_code();
        $messages = $this->get_messages($code);
        if ( empty($messages) )
            return '';
        return $messages[0];
    }


    public function add($code, $message) {
        $this->messages[$code][] = $message;
    }


    public function remove( $code ) {
        unset( $this->messages[ $code ] );
    }
}