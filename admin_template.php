<?php
/**
 * @var $this MUShoowreelRSS
 * @var $blog BlogShowreelPresentation
 * @var $updated bool
 */
?>
<script type="text/javascript">
    jQuery('input[type=checkbox]').live('change', function() {
        if(jQuery(this).attr('checked')) {
            jQuery(this).parent().css({
                background : 'white',
                opacity : 1
            });
        }
        else {
            jQuery(this).parent().css({
                background : '#DDD',
                opacity : 0.7
            });
        }
    });

	function fixForm(f) {
		var fileInputs = f.find('input[type=file]');
		var message = '';
		var approvedFileInputs = 0;
		fileInputs.each(function() {
				input = jQuery(this);
				if(approvedFileInputs == 15 && input.val() != '') {
					message = 'You can not upload more then 15 files at once';
					input.remove();
				}
				else if(input.val() == '')
					input.remove();
				else
					approvedFileInputs++;
			});

		if(message != '')
			alert(message);
		
		f.submit();
		return false;
	}	
    
</script>
<div class="wrap">
    <div class="box_content">
        <h2>Showreel RSS</h2>

        <? if($updated): ?>
            <div id="message" class="updated below-h2">
                <p>Configuration saved!</p>
            </div>
        <? endif; ?>
        
        <p>
            RSS-feed: <a href="<?=$this->UrlToRSS()?>" target="_blank"><?=$this->UrlToRSS()?></a>
        </p>
        <form action="" method="post" enctype="multipart/form-data" id="showreel_rss_form">
            <? foreach($this->getBlogs() as $blog): ?>
                <p style="padding:10px; border:#CCC solid 1px; <?=( $blog->isActive() ? 'background:white':'background:#DDD; opacity:0.7')?>">
                    <input type="checkbox" value="<?=$blog->id()?>" name="blogs[]" <?=( $blog->isActive() ? ' checked="checked"':'')?> />
                    <? if($blog->image() != ''): ?>
                        <img src="<?=$blog->image()?>" alt="" />
                    <? endif; ?>
                    <strong>
                        <a href="<?=$blog->url()?>" target="blank"><?=$blog->name()?></a>
                    </strong>
                    <input type="file" name="<?=$blog->id()?>" />
                    &nbsp;
                    <span style="color:#777">Description:</span>
                    <input type="text" name="description[<?=$blog->id()?>]" value="<?=$blog->name()?>" />
                </p>
            <? endforeach; ?>
            <p>
                <input type="hidden" name="action" value="wp_handle_upload" />
                <input type="submit" class="button action" onclick="return fixForm(jQuery('#showreel_rss_form'));" value="Save Configuration" />
            </p>
        </form>
    </div>
</div>