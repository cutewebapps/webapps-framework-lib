<?php

class App_PagerHelper extends App_ViewHelper_Abstract
{
    public function pager()
    {
        if ( $this->getView()->totalCount < 10 ) return;


        $arrParams = $this->getView()->inflection;
        $arrParams['sort'] = $this->getView()->sort;
        $arrParams['dir'] = $this->getView()->dir;
        if(isset($this->getView()->template))
            $arrParams['template'] = $this->getView()->template;
        if ($this->getView()->page > 1) {
            $arrParams['page'] = $this->getView()->page;
        }

        if ( is_object($this->getView()->formFilter) )
        {
            foreach ($this->getView()->formFilter->getValues() as $strName => $strValue) {

                if(is_array($strValue) && count($strValue)>0) {
                    $arrValue = $strValue;
                    foreach($arrValue as $intSubKey => $strSubVal)  {
                        $arrValue[$intSubKey] = urlencode($strSubVal);
                    }
                    $arrParams += array($strName => $arrValue);
                    continue;
                }
                if (!strlen($strValue)) {
                    continue;
                }
                $arrParams += array($strName => urlencode($strValue));
            }
        }

        if (isset($this->getView()->params)){
            $arrParams = array_merge($this->getView()->params, $arrParams);
        }

        ob_start();
?>
<div class="pagination">
    <?php if ($this->getView()->totalCount) { ?>

	<script type="text/javascript">
	function changePagerResults(objSelect, strBaseUrl)
	{
<?php if (defined( 'WC_NO_REWRITE' )) : ?>
	    window.location = strBaseUrl + '=' + objSelect.value;
<?php else: ?>
	    window.location = strBaseUrl + '/' + objSelect.value;
<?php endif; ?>
	}
	</script>

    <div class="limit">
        Per Page #
        <?php
        $arrParamsNoResults = $arrParams;
        unset( $arrParamsNoResults['results'] );
        
        if ( defined( 'WC_NO_REWRITE' ))
            $strBaseUrl = $this->getView()->url( $arrParamsNoResults ) . '&amp;results';
        else
            $strBaseUrl = $this->getView()->url( $arrParamsNoResults ) . '/results';
        ?>
        <select class="inputbox" onchange="changePagerResults(this, '<?php echo $strBaseUrl ?>');">
        <?php
            $arrayItemsCount = array( '10', '15', '20', '25', '50', '75', '100', '200', '500', '1000');
            foreach($arrayItemsCount as $numberItemsCount) {
                echo '<option ' .($numberItemsCount == $this->getView()->results ? 'selected="selected" ' : '')
                    . 'value="' . $numberItemsCount . '">' . $numberItemsCount . '</option>';
            }
        ?>
        </select>
    </div>
    <?php } ?>
    <?php if ($this->getView()->totalCount > $this->getView()->results) {?>
        <div class="button2-right<?php if ($this->getView()->page != 1){  }else{ ?> off <?php } ?>">
            <div class="start">
                <?php if ($this->getView()->page != 1) { ?>
                    <a title="Start" href="<?php echo $this->getView()->url( array_merge($arrParams, array('page' => 1)) ); ?>">Start</a>
                <?php } else { ?>
                    <span>Start</span>
                <?php } ?>
            </div>
        </div>
        <div class="button2-right<?php if ($this->getView()->page > 1){ }else{ ?> off <?php } ?>">
            <div class="prev">
                <?php if ($this->getView()->page > 1) { ?>
                    <a title="Prev" href="<?php echo $this->getView()->url(array_merge($arrParams, array('page' => ($this->getView()->page - 1))) ); ?>">Prev</a>
                <?php } else { ?>
                    <span>Prev</span>
                <?php } ?>
            </div>
        </div>

        <div class="button2-left">
            <div class="page">
            <?php
            if ($this->getView()->pagesCount < 10 || $this->getView()->page <= 5) {
                $maxCount = $this->getView()->pagesCount < 10 ? $this->getView()->pagesCount : 10;
                $intStartPage = 1;
                $intEndPage = $maxCount;
            } else if ($this->getView()->page + 5 > $this->getView()->pagesCount) {
                $intStartPage = $this->getView()->pagesCount - 10;
                $intEndPage = $this->getView()->pagesCount;
            } else {
                $intStartPage = $this->getView()->page - 5;
                $intEndPage = $this->getView()->page + 5;
            }
            ?>

            <?php for ($i = $intStartPage; $i <= $intEndPage; $i++ ) {?>
                <?php if ($i == $this->getView()->page) { ?>
                    <span><?php echo $i ?></span>
                <?php } else { ?>
                    <a title="<?php echo $i ?>" href="<?php echo $this->getView()->url(array_merge($arrParams,
                            array('page' => $i)) ); ?>"><?php echo $i; ?></a>
                <?php } ?>
            <?php } ?>
                <span style="display:none; clear:both;"></span>
            </div>
        </div>

        <div class="button2-left<?php if ($this->getView()->page < $this->getView()->pagesCount){ }else{ ?> off <?php } ?>">
            <div class="next">
                <?php if ($this->getView()->page < $this->getView()->pagesCount) { ?>
                    <a title="Next" href="<?php echo $this->getView()->url(array_merge($arrParams,
                            array('page' => ($this->getView()->page + 1))) ); ?>">Next</a>
                <?php } else { ?>
                    <span>Next</span>
                <?php } ?>
            </div>
        </div>
        <div class="button2-left<?php if ($this->getView()->page != $this->getView()->pagesCount) {  } else { ?> off <?php } ?>">
            <div class="end">
                <?php if ($this->getView()->page != $this->getView()->pagesCount) { ?>
                    <a title="End" href="<?php echo $this->getView()->url(array_merge($arrParams,
                            array('page' => $this->getView()->pagesCount)) ); ?>">End</a>
                <?php } else { ?>
                    <span>End</span>
                <?php } ?>
            </div>
        </div>
        <div class="limit">
            Page <?php echo $this->getView()->page ?> of <?php echo $this->getView()->pagesCount; ?>
        </div>
    <?php }?>
</div>
<?php
        $strContents = ob_get_contents();
        ob_end_clean();

        return $strContents;
    }
}