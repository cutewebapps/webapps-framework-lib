<?php

/**
 * Basic class for displaying PHP class
 */
class App_DocPage
{
    public $page = '';
    public $content = '';
    public $static = '';
    
    
    /**
     * Get Content of generated page
     * 
     * @return string
     */
    public function getContent()
    {
        if ( !@$this->static ) 
            return '<pre>'.htmlspecialchars( $this->content ).'</pre>';
        
        $strContent = $this->content;
        preg_match_all( '@\[\[(.+)\]\]@simU', $strContent, $arrMatches );
        for ( $i = 0; $i< count( $arrMatches[0] ); $i ++ ) {
            /// [[Article][Article Text]] or [[Article]] or [[Main Page|different text]] means links?
            $strInside =  $arrMatches[1][$i];
            $strLink  = $strInside;
            $strTitle = $strInside;
            
            if ( strstr( $strInside, '|' ) ) {
                list( $strLink, $strTitle ) =  explode( '|', $strInside );
            } else if ( strstr( $strInside, '][' ) ) {
                list( $strLink, $strTitle ) =  explode( '][', $strInside );
            }
            $strContent = str_replace( $arrMatches[0][$i], $this->link( $strLink, $strTitle ), $strContent );
        }
        
        return $strContent;
    }
    /**
     * load static page into contents variable
     * 
     * @param string $strPage
     * @param string $strPath
     */
    public function render( $strPage, $strPath )
    {
        $this->page = $strPage;
        $this->static = true;
        
        ob_start();
            require $strPath;
            $this->content = ob_get_contents();
        ob_end_clean();
    }
    
    /**
     * Render class structure
     * 
     * @param string $strClassName
     * @param string $strPath
     */
    public function renderClass ( $strClassName, $strPath )
    {
        $this->page = $strClassName;
        $this->static = false;
        $this->content = file_get_contents( $strPath );
        //@TODO: standard class rendering here 
    }
    
    /**
     * Get Path to the article by name 
     * @todo: needs to look at application configuration
     * 
     * @param string $strPath
     * @return string
     */
    public function path( $strName )
    {
        return '/wiki/'.$strName;
    }
    
    /**
     * @todo: remove hardcoded target
     * @todo: check for missing pages
     * 
     * @param string $strName
     * @param string $strTitle
     * @return string
     */
    public function link( $strName, $strTitle = ''  )
    {
        if ( $strTitle == '' ) $strTitle = $strName;
        return '<a href="'.$this->path( $strName ).'" target="doc">'.$strTitle.'</a>';
    }
    
    /**
     * Get Path to the image from the article
     * @todo: needs to look at application configuration
     * 
     * @param string $strPath
     * @return string
     */
    public function imgpath( $strPath )
    {
        return '/static/docs/images/'.preg_replace( '^/', '', $strPath );
    }
    
    /**
     * Get HTML for centered image 
     * 
     * @param string $strPath
     * @param string $strAlt
     * @return string
     */
    public function img( $strPath, $strAlt = '' )
    {
        return '<div class="image-wrapper" style="margin:0px auto"><img src="'
            .$this->path( $strPath ). '" alt="'.$strAlt.'" /></div>';
    }
    
}