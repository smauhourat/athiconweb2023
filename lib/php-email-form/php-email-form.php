<?php

    class PHP_Email_Form
    {
        public $to = '';
        public $from_name = '';
        public $from_email = '';
        public $subject = '';
        public $message = '';
        public $smtp_host = '';
        public $smtp_username = '';
        public $smtp_password = '';
        public $smtp_port = '';

        public function send()
        {
            echo "call send()\n";
            echo "to: $this->to \n";
            echo "from_name: $this->from_name \n";
            echo "from_email: $this->from_email \n";
            echo "subject: $this->subject \n";
            echo "message: $this->message \n";
            echo "smtp_host: $this->smtp_host \n";
            echo "smtp_username: $this->smtp_username \n";
            echo "smtp_password: $this->smtp_password \n";
            echo "smtp_port: $this->smtp_port \n";
        }

    }
?>
