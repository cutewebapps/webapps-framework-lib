<?php


class App_CheckEnv_Gd 
{
    public function __construct()
    {
        App_CheckEnv::assert( extension_loaded("gd"), 'GD extension is not supported');
        
        // this is not enough because GD can be compiled without PNG/JPG/FreeType support
        
        App_CheckEnv::assert( function_exists( 'imagecreatefromjpeg' ), "GD doesnt have JPEG support" );
        App_CheckEnv::assert( function_exists( 'imagecreatefrompng' ), "GD doesnt have PNG support" );
        
        $modules = new App_CheckEnv_Phpinfo();
        App_CheckEnv::assert( $modules->getModuleSetting('gd', 'FreeType Support') == 'enabled',
                'GD is Compiled without FreeType support');
	 
    }
}