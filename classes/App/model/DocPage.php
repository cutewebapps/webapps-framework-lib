<?php

/**
 * Basic class for displaying PHP class
 * 
 * @todo: inclusion of other pages
 * 
 */
class App_DocPage
{
    public $page = '';
    public $content = '';
    public $generated = '';
    public $static = '';
    
    
    /**
     * Get Content of generated page
     * 
     * @return string
     */
    public function getContent()
    {
        if ( !@$this->static ) {
            return $this->generated . '<div><a href="javascript:void(0);" onclick=\'$("pre.source").removeClass("hidden")\'>View Source</a></div>'
            .'<pre class="source hidden">'.htmlspecialchars( $this->content ).'</pre>';
        }
        
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
        
        // autoloading classes with to work with reflections further
        ob_start();
        require_once $strPath;
        ob_end_clean();
        
        ob_start();
        // walking through each token and extracting classes
        $nStart = 0;
        $arrTokens = token_get_all ( $this->content );
        while ( $nStart < count( $arrTokens ) ) {
            $class = new App_DocPage_Class();
            $nStart = $class->parseTokens( $arrTokens, $nStart );
            echo '<h3>'. $class->getName() .'</h3>';
            echo '<hr style="margin-top:0px;margin-bottom:5px" /><pre class="comment">'.$class->getDocBlock().'</pre>';
            
            $strParent = $class->parseParent();
            if ( $strParent )
                echo '<p>Extends <strong>'. $strParent .'</strong></p>';
            $arrImplements = $class->parseImplements();
            if ( count( $arrImplements ) > 0 )
                echo '<p>Implements inferfaces <strong>'. implode( $arrImplements ).'</strong></p>';
            
           // Sys_Debug::dump( $class->getTokens());
            
            $strTable = $class->getTable();
            
            if ( $strTable ) {
                echo '<p>Wraps logic of the table: <strong>'. $strTable .'</strong></p>';
                echo '<p>Primary Key: <strong>'. $class->getPrimaryKey() .'</strong></p>';
                
                // display database schema!
                $arrTables = $class->getSchemaTables();
                if ( count( $arrTables ) > 0 ) {
                    $arrConnections = $class->getSchemaConnections();
                    $arrResult = array( 'tables' => $arrTables, 'connections' => $arrConnections );
                    echo '<script type="text/JavaScript">'."\n";
                    echo 'jQuery(document).ready( function() { dbschema.data = ' . json_encode( $arrResult ) . '; dbschema.render(); } );';
                    echo '</script>'."\n";
                    echo '<div id="canvas"></div>';
                }
                
            }
            //Sys_Debug::dump( $class->getTokens() );
            $class->parseMethodsAndProperties();    
            if ( count( $class->getControllerActions() ) > 0 ) {
                echo '<h4>Actions</h4>';
                echo '<ol>';
                foreach( $class->getControllerActions() as $arrAction ) {
                    
                    $docblock = new App_DocPage_Docblock( $arrAction['docblock'] );
                    echo '<li>';
                    echo '<pre class="comment">'.$docblock->getRawText().'</pre>';
                    echo '<h5>'.$arrAction['shortname'].'<span style="color:lightgray">Action()</span></h5>';
                    if ( isset( $arrAction['views'] ) && count( $arrAction['views'] ) > 0 ) {
                        // Sys_Debug::dump( $arrAction );
                        echo '<div style="color:gray">Templates: </div>';
                        echo '<ul style="margin-bottom:20px">';
                        foreach ( $arrAction['views'] as $strView )  {
                            echo '<li>'.str_replace( '/', '<span style="color:gray"> / </span>', $strView).'</li>';
                        }
                        echo '</ul>';
                    }
                    echo '</li>';
                }
                echo '</ol>';
            }
            if ( count( $class->getMethods() ) > 0 ) {
                echo '<h4>Methods</h4>';
                echo '<ol>';
                foreach( $class->getMethods() as $arrMethod ) {
                //Sys_Debug::dump ( $class->getMethods() );
                    $docblock = new App_DocPage_Docblock( $arrMethod['docblock'] );
                    echo '<li>';
                    echo '<h5> <span style="color:gray">'.$arrMethod['scope'].'</span> '
                            .$arrMethod['name'].'(<span style="color:gray">'.$arrMethod['args'].'</span>)</h5>';
                    echo '<pre class="comment">'.$docblock->getRawText().'</pre>';
                    //Sys_Debug::dump( $arrMethod );
                    echo '</li>';
                }
                echo '</ol>';
            }
            if ( count( $class->getProperties() ) > 0 ) {
                echo '<h4>Properties</h4>';
                //Sys_Debug::dump ( $class->getProperties() );
                echo '<ul>';
                foreach( $class->getProperties() as $arrProps ) {
                //Sys_Debug::dump ( $class->getMethods() );
                    $docblock = new App_DocPage_Docblock( $arrProps['docblock'] );
                    echo '<li>';
                    echo '<h5>'.'<span style="color:gray">'.$arrProps['scope'].'</span> '.$arrProps['name'].'</h5>';
                    echo '<pre class="comment">'.$docblock->getRawText().'</pre>';
                    
                   // Sys_Debug::dump( $arrProps );
                    echo '</li>';
                }
                echo '</ul>';
            }
            
            echo '<div style="margin:30px"></div>';
            
            $nStart ++;
        }
        $strContent = ob_get_contents();
        ob_end_clean();
        
        $this->generated = $strContent;
        return $strContent;
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
        
        $strTargetFrame = App_Application::getInstance()->getConfig()->documentation->iframe;
        if  ( !$strTargetFrame ) 
            $strTargetFrame = 'doc';
        
        return '<a href="'.$this->path( $strName ).'" target="'.$strTargetFrame.'">'.$strTitle.'</a>';
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
            .$this->imgpath( $strPath ). '" alt="'.$strAlt.'" /></div>';
    }
    
}