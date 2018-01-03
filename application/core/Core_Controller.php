<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Core_Controller extends CI_Controller
{

	protected $header_scripts = array();
	protected $footer_scripts = array();
	protected $title = 'Default';
    protected $topBarData = array();
    protected $topBarView = 'templates/topbar';
    protected $topBar = false;

    function __construct()
    {
        parent::__construct();
        if ( empty( $header_scripts ) ) {
            $this->getHeaderFile();
        }
        if ( empty( $footer_scripts ) ) {
            $this->getFooterFile();
        }
    }

    /**
     * Get Header file and set class variable
     * @return  void
     * @author Anil <anilprz@gmail.com>
     * @version 1.0
     */
    public function getHeaderFile()
    {
    	require_once VIEWPATH .'templates'. DIRECTORY_SEPARATOR .'header_scripts.php';
    	$this->header_scripts = $header_scripts;
    }

    /**
     * Get Footer file and set class variable
     * @return  void
     * @author Anil <anilprz@gmail.com>
     * @version 1.0
     */
    public function getFooterFile()
    {
    	require_once VIEWPATH .'templates'. DIRECTORY_SEPARATOR .'footer_scripts.php';
        $this->footer_scripts = $footer_scripts;
    }

    /**
     * Set Header view to render
     * @return  void
     * @author Anil <anilprz@gmail.com>
     * @version 1.0
     */
    public function setHeader( $data = array(), $view = 'templates/header' )
    {
        ksort( $this->header_scripts );
        $data['header_scripts'] = implode( "\n", $this->header_scripts );
    	$this->load->view( $view , $data );
    }

    /**
     * Set Footer view to render
     * @return  void
     * @author Anil <anilprz@gmail.com>
     * @version 1.0
     */
    public function setFooter( $data = array(), $view = 'templates/footer' )
    {
        ksort( $this->footer_scripts );
        $data['footer_scripts'] = implode( "\n", $this->footer_scripts );
    	$this->load->view( $view, $data );
    }

    /**
     * Get Header scripts such as css and js
     * @return  array
     * @author Anil <anilprz@gmail.com>
     * @version 1.0
     */
    public function getHeaderScripts()
    {
        return $this->header_scripts;
    }

    /**
     * Set Header scripts such as css and js
     * @return  void
     * @author Anil <anilprz@gmail.com>
     * @version 1.0
     */
    public function setHeaderScripts( $scripts )
    {
        if ( empty( $scripts ) || !is_array( $scripts ) ) return false;

        $this->header_scripts = $scripts;
    }

    /**
     * Get Footer scripts such as css and js
     * @return  array
     * @author Anil <anilprz@gmail.com>
     * @version 1.0
     */
    public function getFooterScripts()
    {
        return $this->footer_scripts;
    }

    /**
     * Set Footer scripts such as css and js
     * @return  void
     * @author Anil <anilprz@gmail.com>
     * @version 1.0
     */
    public function setFooterScripts( $scripts )
    {
        if ( empty( $scripts ) || !is_array( $scripts ) ) return false;

        $this->footer_scripts = $scripts;
    }

    /**
     * Set Title of the page
     * @param string $title Title for the page
     * @return  void
     * @author Anil <anilprz@gmail.com>
     * @version 1.0
     */
    public function setTitle( $title = 'Default' )
    {
        $this->title = $title;
    }

    /**
     * Set Main Layout for View render
     * @param string   $view view file name and locations
     * @param array    $data varaibles to pass to views
     * @param boolean  $header Include header for current page
     * @param boolean  $footer Include footer for current page
     * @return  void
     * @author Anil <anilprz@gmail.com>
     * @version 1.0
     */
    public function setLayout( $view = 'home', $data = array(), $header = true, $footer = true )
    {
        $data['title']             = $this->title;

        if ( $header ) {
            $this->setHeader( $data );
        }

        $this->getTopBar();

        $this->load->view( $view, $data );

        if ( $footer ) {
            $this->setFooter( $data );
        }
    }

    /**
     * Set Top Bar as top Menu
     * @param boolean $showHide Show Topbar or not
     * @param array $topBarData pass view variables only for Top bar
     * @param string $topBarView View file location and name
     * @return  void
     * @author Anil <anilprz@gmail.com>
     * @version 1.0
     */
    public function setTopBar( $showHide = true, $topBarData = array(), $topBarView = 'templates/topbar' )
    {
        $this->topBar = $showHide;
        $this->topBarData = $topBarData;
        $this->topBarView = $topBarView;
    }

    /**
     * Get Top bar and render view for current page
     * @return  void
     * @author Anil <anilprz@gmail.com>
     * @version 1.0
     */
    public function getTopBar()
    {
        if ( $this->topBar ) {
           $this->load->view( $this->topBarView, $this->topBarData );
        }
    }

    /**
     * Return Json result to responses
     * @param array $data data for converting into json object
     * @param boolean|string $success True or false for result indication
     * @param boolean $print print instead of showing variables.
     * @return  array
     * @author Anil <anilprz@gmail.com>
     * @version 1.0
     */
    public function return_result($data, $success, $print = true) {
        global $CI;

        $data['success'] = $success;

        if( $print ){
             $CI->output->set_content_type('application/json')
                          ->set_output( json_encode( $data ) );
        }

        return $data;
    }

}
