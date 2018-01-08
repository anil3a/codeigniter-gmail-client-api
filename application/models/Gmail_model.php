<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once 'Emails_model.php';

class Gmail_model extends Emails_model
{
    private $service;
    private $client;
    private $attachment = array( array( 'filename', 'filetype' ) );
    private $allowed_file_types = 'gif|jpg|png|pdf|doc|docx|xls|xlsx|ppt|pptx|csv';

    public function __construct()
    {
        if ($this->isAuthenticated()) {
            $this->client = $this->getClientAssessed();
        } else {
            $this->client = $this->getClient();
        }
    }

    /**
     * Get Gmail Client
     * @return  Object Client
     * @author Anil <anilprz3@gmail.com>
     * @version 1.0
     */
    private function getClient()
    {
        $clientSecretKey = $this->system->get_option('clientsecret');
        $clientClientID  = $this->system->get_option('clientid');


        $config = array( "redirect_uri"  => site_url( "google/oauthcallback" ),
                         "client_id"     => $clientClientID,
                         "client_secret" => $clientSecretKey,
        );

        $client = new Google_Client( $config );

        $client->setAccessType("offline");        // offline access
        //$client->setApprovalPrompt ("force");
        $client->setIncludeGrantedScopes(true);   // incremental auth
        $client->addScope(Google_Service_Gmail::GMAIL_READONLY);
        $client->addScope(Google_Service_Gmail::GMAIL_MODIFY);
        $client->addScope(Google_Service_Gmail::GMAIL_SEND);
        $client->addScope(Google_Service_Gmail::GMAIL_COMPOSE);

        return $client;
    }

    /**
     * Get Assessed Gmail Client with token
     * @return  Object Client
     * @author Anil <anilprz3@gmail.com>
     * @version 1.0
     */
    private function getClientAssessed()
    {
        $client = $this->getClient();
        $access_token = $this->system->get_option('google_access_token');
        if (empty($access_token)) {
            return $client;
        }

        $client->setAccessToken(json_decode($access_token, true));

        if ($client->isAccessTokenExpired()) {
            $v = $client->getRefreshToken();
            if (!empty($v)) {
                try {
                  $client->fetchAccessTokenWithRefreshToken($v);
                  $this->system->update_option('google_access_token', json_encode($client->getAccessToken()));
                } catch (Exception $e) {
                    log_message('error', 'ERROR TRYING TO UPDATE GOOGLE API. FIX IT ASAP - ' . $e->getMessage() );
                    set_alert('danger', 'ERROR TRYING TO UPDATE GOOGLE API. FIX IT ASAP - ' . $e->getMessage());
                }
            }
        }

        $this->service = new \Google_Service_Gmail($client);

        return $client;
    }

    /**
     * Get Url to Authenticate
     * @return  String url
     * @author Anil <anilprz3@gmail.com>
     * @version 1.0
     */
    public function get_url_auth()
    {
        $authUrl = $this->client->createAuthUrl();
        return $authUrl;
    }

    /**
     * Check if Authenticated
     * @return  boolean true or false for authentication
     * @author Anil <anilprz3@gmail.com>
     * @version 1.0
     */
    public function isAuthenticated()
    {
        $access_token = $this->system->get_option('google_access_token');

        if (empty($access_token)) {
            return false;
        }

        return true;
    }

    /**
     * Create authentication with google
     * @param   array   $code 
     * @return  array
     * @author Anil <anilprz3@gmail.com>
     * @version 1.0
     */
    public function create_authentication($code)
    {
        $client = $this->getClient();

        $accessToken = $client->fetchAccessTokenWithAuthCode($code);

        $this->system->update_option('google_access_token', json_encode($accessToken));
        
        return $accessToken;
    }

    /**
     * Fetch emails from gmail passing "me" as self
     * @return array result details
     * @author Anil <anilprz3@gmail.com>
     * @version 1.0
     */
    public function get_emails( $userId = "me" )
    {
        if (empty($this->service)) {
            return array('success' => false, 'message' => 'Service not found.', 'errorCode' => '001');
        }

        $pageToken = NULL;
        $messages = array();
        $opt_param = array('labelIds' => array('UNREAD'), 'maxResults' => 30 );
        
        $return = array();
        $errors = array();

        do {
            try {
                if ($pageToken) {
                    $opt_param['pageToken'] = $pageToken;
                }
                $messagesResponse = $this->service->users_messages->listUsersMessages($userId, $opt_param);

                if ($messagesResponse->getMessages()) {
                    $messages = array_merge($messages, $messagesResponse->getMessages());
                    $pageToken = $messagesResponse->getNextPageToken();
                }
            } catch (Exception $e) {
                logActivity('ERROR: Getting gmail messages::: ' . $e->getMessage() );
                $errors[] = $e->getMessage();
            }
        } while ($pageToken);


        if (!empty($errors)) {
            return array('success' => false, 'errors' => $errors, 'message' => 'Errors were found.');
        }

        foreach ($messages as $message) {
            $message = $this->service->users_messages->get($userId, $message->getId(), array('format' => 'full'));

            $return[] = $this->sendMessageToDb($userId, $message->getId());

        }

        return array('success' => true, 'return' => $return );
    }

    /**
     * Mark Message read
     * @param   string $userId      gmail user id
     * @param   string $messageId   gmail's emails's message id
     * @return  array               result
     * @author Anil <anilprz3@gmail.com>
     * @version 1.0
     */
    public function markMessageRead($userId, $messageId)
    {
        if (empty($this->service)) {
            return array('success' => false, 'message' => 'Service not found.', 'errorCode' => '003');
        }

        try {
            $mods = new Google_Service_Gmail_ModifyMessageRequest();
            $mods->setRemoveLabelIds(array('UNREAD'));
            $message = $this->service->users_messages->modify($userId, $messageId, $mods);
            return array(
                'success' => true,
                'message' => $message
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * Mark Message unread
     * @param   string $userId      gmail user id
     * @param   string $messageId   gmail's emails's message id
     * @return  array               result
     * @author Anil <anilprz3@gmail.com>
     * @version 1.0
     */
    public function markMessageUnread($userId, $messageId)
    {
        if (empty($this->service)) {
            return array('success' => false, 'message' => 'Service not found.');
        }

        try {
            $mods = new Google_Service_Gmail_ModifyMessageRequest();
            $mods->setAddLabelIds(array('UNREAD'));
            $message = $this->service->users_messages->modify($userId, $messageId, $mods);
            return array(
                'success' => true,
                'message' => $message
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }

    // Fancy decode body of email Copied from stackoverflow & blogs & gmail client api docs
    private function decodeBody($body) {
        $rawData = $body;
        $sanitizedData = strtr($rawData,'-_', '+/');
        $decodedMessage = base64_decode($sanitizedData);
        if(!$decodedMessage){
            $decodedMessage = FALSE;
        }
        return $decodedMessage;
    }

    // Fancy readParts of email Copied from stackoverflow & blogs & gmail client api docs
    private function readParts($parts)
    {
        $attachments = array();
        $realParts = array();
        foreach ($parts as $part) {
            if (!empty($part['filename'])) {
                $attachments[] = $part;
            } elseif (!empty($part->getParts())) {
                $newParts = $this->readParts($part->getParts());
                foreach ($newParts['attachments'] as $a) {
                    $attachments[] = $a;
                }
                foreach ($newParts['parts'] as $p) {
                    $realParts[] = $p;
                }
            } else {
                $realParts[] = $part;
            }
        }

        return array(
            'attachments' => $attachments,
            'parts'       => $realParts
        );
    }

    // Fancy decode message of email Copied from stackoverflow & blogs & gmail client api docs
    public function decodeMessage($part, $messageId) {
        $mimeType = $part->getMIMEType();
        $result = array();

        if (in_array($mimeType, array('text/html', 'text/plain'))) {
            $finalMessage = base64_decode(str_replace(array('-', '_'), array('+', '/'), $part->getBody()->getData()));
            $result[] = array(
                'message' => $finalMessage,
                'mime' => $mimeType,
                'id' => $messageId
            );
        } elseif ($mimeType === 'multipart/mixed') {
            $readParts = $this->readParts($part->getParts());
            foreach ($readParts['parts'] as $p) {
                $res = $this->decodeMessage($p, $messageId);
                foreach ($res as $r) {
                    $result[] = $r;
                }
            }
        } elseif ( in_array( $mimeType , array('multipart/alternative','multipart/related') ) ) {
            $readParts = $this->readParts($part->getParts());
            foreach ($readParts['parts'] as $p) {
                $res = $this->decodeMessage($p, $messageId);
                foreach ($res as $r) {
                    $result[] = $r;
                }
            }
        } else {
            $result[] = array(
                'message' => '[Message MIME Type not recognize, verify message with ID : ' . $messageId . ' on Gmail]',
                'mime' => $mimeType,
                'id' => $messageId
            );

        }
        log_message( "error", json_encode( $result ) );
        return $result;
    }

    /**
     * Save Gmail recieved message to Database
     * @param   string $userId      User id as "me" or user's email address to retrieve email
     * @param   string $messageId   Message id from gmail client api
     * @return  array               result of success or failure of this method code
     * @author Anil <anilprz3@gmail.com>
     * @version 1.0
     */
    public function sendMessageToDb( string $userId, string $messageId)
    {
        if (empty($this->service)) {
            return 'Service not found.';
        }

        $return = array();
        $newAtts = array();

        try {
            $message = $this->service->users_messages->get($userId, $messageId, array('format' => 'full'));
            $messageReceivedDate = mdate( "%Y-%m-%d %H:%i:%s", floatval( $message->getInternalDate() / 1000 ) );
     
            $payload = $message->getPayload();
            $parts = $payload->getParts();
            $newParts = $parts;

            $result = $this->decodeMessage($payload, $messageId);

            $result = end( $result );
            
            $mimeType = $payload->getMIMEType();

            $name = '';
            $subject = 'No subject.';
            $from = 'No from.';
            foreach ($payload->headers as $header) {

                if ($header->name === 'Subject') {
                    $subject = $header->value;
                }
                if ($header->name === 'From') {

                    $pos = strpos($header->value, '<');

                    $name = substr($header->value, 0, $pos);

                    if ($pos !== false) {
                        $pos2 = strpos($header->value, '>', $pos);
                        if ($pos2 !== false) {
                            $pos += 1;
                            $from = substr($header->value, $pos, $pos2 - $pos);
                        }
                    } else {
                        $from = $header->value;
                    }
                }
            }


            $userid = 0;
            $contact_id = 0;

            if ( !empty( $from ) )
            {
                $this->load->model('users_model');

                $user = $this->users_model->getByColumn( array( "email" => $from ) )->row();

                if ( empty( $user ) )
                {
                    $user['id'] = null;

                    $names = explode( " ", $name, 2 );
                    $firstName = $this->system->cleanString( trim($names[0]) );
                    $user['username'] = ( !empty( $firstName ) ? $firstName : 'user' ) . rand(100, 999);
                    $user['firstname'] =  $firstName;
                    $user['lastname'] = null;
                    if ( !empty( $names[1] ) )
                    {
                        $user['lastname'] =  $this->system->cleanString( trim($names[1]) );
                    }

                    $user['email'] = trim($from);
                    
                    $user_id = $this->users_model->save( $user );

                } else {
                    $user_id = $user->id;
                }

            }

            $result['subject'] = $subject;
            $result['from'] = $from;
            
            
            if ( !empty($result) ) {
                
                $data = array( 
                  'id' => null,
                  'user_id' => $user_id,
                  'email' => $from,
                  'subject' => $result['subject'],
                  'content' => $result['message'],
                  'messageid' => $messageId,
                  'mime' => 'text/html',
                  'reply_of' => null,
                  'threadID' => $message->getThreadId(),
                  'created_at' => $messageReceivedDate,
                  'updated_at' => mdate( "%Y-%m-%d %H:%i:%s", now() ),
                );

                $this->load->model('emails_model');

                $email_id = $this->emails_model->save( $data );

                if ( !empty( $email_id ) ) {
                
                    if($mimeType === 'multipart/mixed') {
                        /**
                         * Verify the type of parts
                         */
                        $readParts = $this->readParts($newParts); 
                        $attachments = $readParts['attachments'];

                        /**
                         * Save attatchments in the folder uploads/attachments....
                         */
                        $this->load->model('attachments_model');

                        foreach ( $attachments as $att ) { 
                            
                            if (!empty($att->getBody()['attachmentId'])) {
                                $newAtt = $this->service->users_messages_attachments->get($userId, $messageId,$att->getBody()['attachmentId']);

                                $data = $newAtt['data'];
                            } else {
                                $data = $att->getBody()->getData();
                            }

                            $data = $this->decodeBody($data);
                            $newname = $messageId . date('YmdHis') .'-' . $att['filename'];

                            $attachmentFolder = FCPATH . DIRECTORY_SEPARATOR . 'uploadz'. DIRECTORY_SEPARATOR . 'emails' . DIRECTORY_SEPARATOR . $email_id;

                            if (!file_exists( $attachmentFolder )) {
                                mkdir ( $attachmentFolder , 0775 );
                            }

                            file_put_contents( $attachmentFolder .'/'. $newname, $data );

                            $attachment['id']       = null;
                            $attachment['email_id'] = $tid;
                            $attachment['filename'] = $newname;
                            $attachment['filetype'] = $att['mimeType'];
                            
                            $this->attachments_model->save( $attachment );
                        }
                    }
                }

                $this->markMessageRead($userId, $messageId);
            }

            return array('success' => true, 'return' => $return);
          
          //return $message;
        } catch (Exception $e) {
            log_message( 'error', 'ERROR: Import email messages from gmail to database::: ' . $e->getMessage() );
            return array('success' => false, 'error' => $e->getMessage(), 'error_json' => json_decode($e->getMessage()), 'message' => 'Error trying to update message.');
        }
    }

    /**
     * Add attachment to property to check before an email is send
     * @param array $attachment
     * @return none
     * @author Anil <anilprz3@gmail.com>
     * @version 1.0
     */
    public function add_attachment($attachment)
    {
        $this->attachment[] = $attachment;
    }

    /**
     * @return none
     * Clear all attachment properties
     */
    public function clear_attachments()
    {
        $this->attachment = array( array( 'filename', 'filetype' ) );
    }

    /**
     * Send Email with Gmail Api
     * @param   string          $email_to   Email to send to
     * @param   string          $subject    Email Subject
     * @param   string          $message    Email main message content
     * @param   string          $email_from Email sender email
     * @param   string          $email_name Email sender name
     * @param   string|array    $Cc         Email CC email to send to
     * @param   string|array    $Bcc        Email BCC email to send to
     * @param   array           $extras     Email related datas/extra variables
     * @return  void
     * @author  Anil <anilprz3@gmail.com>
     * @version 1.0
     */
    public function send_email_new( string $email_to, string $subject, string $message, $email_from = false, $email_name = false, $Cc = false, $Bcc = false, $extras = array() )
    {
        $send = false;

        if ( empty($this->service) ) {
            return array('success' => false, 'message' => 'Service not found.', 'errorCode' => '004');
        }

        if ( empty($email_from) ) {
            $email_from = $this->system->get_option('google_email_from');
        }

        if ( empty($email_name) ) {
            $email_name = $this->system->get_option('google_name_from');
        }

        try {
            
            $mime = new Mail_mime();

            $mime->setSubject( $subject );
            $mime->setTXTBody( strip_tags( $message ) );
            $mime->setHTMLBody( $message );
            $mime->setFrom( $email_name. ' <'.$email_from.'>' );
            $mime->addTo( $email_to );

            if ( !empty( $Cc ) )
            {
                if ( is_array( $Cc ) )
                {
                    foreach ( $Cc as $ccEmail )
                    {
                        $mime->addCc( $ccEmail );
                    }
                } else 
                {
                    $mime->addCc( $Cc );
                }
            }

            if ( !empty( $Bcc ) )
            {
                if ( is_array( $Bcc ) )
                {
                    foreach ( $Bcc as $BccEmail )
                    {
                        $mime->addBcc( $BccEmail );
                    }
                } else 
                {
                    $mime->addCc( $Bcc );
                }
            }

            if ( !empty( $this->attachment ) && is_array( $this->attachment ) )
            {
                foreach ( $this->attachment as $attachment )
                {
                    if ( !empty( $attachment['filename'] ) && !empty( $attachment['filetype'] ) )
                    {
                        $mime->addAttachment( $attachment['filename'], $attachment['filetype'] );
                    }
                }
            }
            
            $message_body = $mime->getMessage();

            $encodeMessage = $this->base64url_encode( $message_body );

            $msg = new Google_Service_Gmail_Message();
            $msg->setRaw( $encodeMessage );

            $send = $this->service->users_messages->send("me", $msg);

            if( !empty( $send->getId() ) && !empty( $extras['email_id'] ) )
            {
                $this->save( array( 'id' => $extras['email_id'], 'messageid' => $send->getId() ) );
            }
            else
            {
                log_message( "error", "Error: Sending email with gmail API." );
                log_message( "error", json_encode( $send ) );
            }

        } catch (Exception $e) {
            log_message( "error", "Error: Sending email with gmail API." );
            log_message( "error", $e->getMessage() );
        }
    }

    /**
     * Save Sent email to database
     * @param   string $email_to Email to send to
     * @param   string $subject  Email subject to send
     * @param   string $message  Email message to send
     * @return  array            Contains email id, attachment id and file uploaded details
     * @author  Anil <anilprz3@gmail.com>
     * @version 1.0
     */
    public function storeSentEmail( string $email_to, string $subject, string $message )
    {
        $this->load->model('users_model');

        $user = $this->users_model->getByColumn( array( "email" => $email_to ) )->row();

        if ( empty( $user ) )
        {
            $user['id'] = null;

            if ( !empty( $name ) ) {
                $names = explode( " ", $name, 2 );
                $firstName = $this->system->cleanString( trim($names[0]) );
                $user['username'] = ( !empty( $firstName ) ? $firstName : 'user' ) . rand(100, 999);
                $user['firstname'] =  $firstName;
                $user['lastname'] = null;
                if ( !empty( $names[1] ) )
                {
                    $user['lastname'] =  $this->system->cleanString( trim($names[1]) );
                }
            } else {
                $user['username'] = 'user' . rand( 1000, 9999 );
                $user['firstname'] =  null;
            }

            $user['email'] = trim( $email_to );
            
            $user_id = $this->users_model->save( $user );

        } else {
            $user_id = $user->id;
        }

        $data = array( 
          'id' => null,
          'user_id' => $user_id,
          'email' => $email_to,
          'subject' => $subject,
          'content' => $message,
          'messageid' => null,
          'mime' => 'text/html',
          'reply_of' => 0,
          'threadID' => null,
          'created_at' => mdate( "%Y-%m-%d %H:%i:%s", now() ),
          'updated_at' => null,
        );

        $result_email_id = $this->save( $data );

        $data = array( 'email_id' => false );

        if ( $result_email_id !== true )
        {
            $config['upload_path']          = FCPATH . DIRECTORY_SEPARATOR . 'uploadz'. DIRECTORY_SEPARATOR . 'emails' . DIRECTORY_SEPARATOR . $result_email_id;
            $config['allowed_types']        = $this->allowed_file_types;
            $config['max_size']             = 6000;
            $config['max_width']            = 2024;
            $config['max_height']           = 2068;

            if (!file_exists( $config['upload_path'] ))
            {
                mkdir ( $config['upload_path'] , 0775 );
            }

            $this->load->library( 'upload', $config );

            $data = array( 'email_id' => $result_email_id );

            if ( !$this->upload->do_upload('file') )
            {
                $error = array('error' => $this->upload->display_errors());
                log_message( 'error', json_encode( $error ) );
            }
            else
            {
                $data['upload_data'] = $this->upload->data();

                $this->load->model('attachments_model');

                log_message( 'error', json_encode( $data ) );

                $result_attach_id = $this->attachments_model->save( 
                        array(      'id' => null,
                                    'email_id' => $result_email_id,
                                    'filename' => $data['upload_data']['file_name'],
                                    'filetype' => $data['upload_data']['file_type']
                        ) 
                );

                if ( $result_attach_id !== true )
                {
                    $data['attachment_id'] = $result_attach_id;
                }
            }

            return $data;
        }
        return false;
    }

    // Fancy Base encoding copied from stackoverflow
    public function base64url_encode( $data )
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // Fancy Base dencoding copied from stackoverflow
    public function base64url_decode( $data )
    {
        return base64_decode(strtr($b64url, '-_', '+/'));
    }

    /**
     * Debug email message while APIing from gmail
     * @param   string $userId      User id as "me" or user's email address to retrieve email
     * @param   string $messageId   Message id from gmail client api
     * @return  array               decoded message of email
     * @author Anil <anilprz3@gmail.com>
     * @version 1.0
     */
    public function debugMessage( string $userId, string $messageId )
    {
        $message = $this->service->users_messages->get($userId, $messageId, array('format' => 'full'));
        $payload = $message->getPayload();
        $parts = $payload->getParts();

        $result = $this->decodeMessage($payload, $messageId);

        $mimeType = $payload->getMIMEType();

        return  $result;
    }

    /**
     * Depreciated Function to send email
     * @param   string  $email_to   Email send to
     * @param   string  $subject    Email subject to send
     * @param   string  $message    Email messages to send
     * @param   string  $email_from Email From email address
     * @param   string  $email_name Email from Name
     * @param   string  $mime       MIME type
     * @return  void
     * @author Anil <anilprz@gmail.com>
     * @deprecated 1.0 not using traditional or manual way to setup
     * @version 1.0
     */
    private function send_gemail($email_to, $subject, $message, $email_from = false, $email_name = false, $mime = 'text/html')
    {
        if (empty($this->service)) {
            return array('success' => false, 'message' => 'Service not found.', 'errorCode' => '004');
        }

        if (empty($email_from)) {
            $email_from = $this->system->get_option('google_email_from');
        }

        if (empty($email_name)) {
            $email_name = $this->system->get_option('google_name_from');
        }

        try {
            $strRawMessage = "MIME-Version: 1.0\r\n";
            $strRawMessage .= "From: " . $email_name . " <" . $email_from . ">\r\n";
            $strRawMessage .= "To: " . $email_to . "\r\n";
            $strRawMessage .= 'Subject: =?utf-8?B?' . base64_encode($subject) . "?=\r\n";
            $strRawMessage .= "Date: ".date(DATE_RFC2822)."\r\n";
            $strRawMessage .= "Content-Type: " . $mime . "; charset=utf-8\r\n\r\n";
            $strRawMessage .= $message . "\r\n";

            $mime = strtr(base64_encode($strRawMessage), '+/', '-_');
            $msg = new Google_Service_Gmail_Message();
            $msg->setRaw($mime);

            $send = $this->service->users_messages->send("me", $msg);
        } catch (Exception $e) {
            log_message( 'error', 'ERROR: Sending email with gmail::: ' . $e->getMessage() );
            return array('success' => false, 'message' => $e->getMessage());
        }

        return array('success' => true, 'message' => $message, 'send' => $send);
    }

    // Fancy encode email address of to send email Copied from stackoverflow & blogs & gmail client api docs
    // @deprecated 1.0 was used for sending email traditionally or manually.
    private function encodeEmailAddresss( $recipient ){
        $recipientsCharset = 'utf-8';
        if (preg_match("/(.*)<(.*)>/", $recipient, $regs)) {
            $recipient = '=?' . $recipientsCharset . '?B?'.base64_encode($regs[1]).'?= <'.$regs[2].'>';
        }
        return $recipient;
    }

}
