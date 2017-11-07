<?php 

$PluginInfo['LaTeX'] = array(
   'Description' => 'This plugin allows you to insert TeX code in your posts between [tex][/tex] shortcodes, using the Google Infographics API',
   'Version' => '1.0',
   'Author' => "Borja Santos-Díez",
   'AuthorEmail' => 'borja@santosdiez.es',
   'AuthorUrl' => 'http://www.santosdiez.es'
);

class LaTeXPlugin extends Gdn_Plugin {

	private $mDidOnce = FALSE;

	private function createUrl($formula) {
		$url = "http://chart.googleapis.com/chart?cht=tx&chf=bg,s,00000000&chl=";
		$imgUrl = $url . urlencode(trim($formula[1]));

		return '[img]' . $imgUrl . '[/img]';
	}
	
	private function doReplacement(&$text) {
		$pattern = '/\[tex\]([^[]+)\[\/tex\]/';
		$text = preg_replace_callback($pattern, array(&$this, 'createUrl'), $text);
	}
	
	public function DiscussionController_BeforeCommentBody_Handler($Sender)
	{
		if($this->mDidOnce !== TRUE) {
			// Get the current Discussion and Comments
			$Comment = $Sender->Discussion;

			// Replace Emoticons in the Discussion and all Comments to it
			$this->doReplacement($Comment->Body);

			foreach($Sender->CommentData as $cdata) {
				$this->doReplacement($cdata->Body);
			}

			$this->mDidOnce = TRUE;
		}
	}
	
	/*public function DiscussionController_BeforeDiscussionRender_Handler($Sender) {
		$this->doReplacement($Sender->Discussion->Body);
		
		foreach($Sender->CommentData->Body as $body) {
			$this->doReplacement($body);
		}
		
	}*/
	
	public function PostController_BeforeCommentBody_Handler($Sender) {
		$this->DiscussionController_BeforeCommentBody_Handler($Sender);
	}

	public function PostController_BeforeCommentRender_Handler($Sender) {
		$this->PostController_BeforeDiscussionRender_Handler($Sender);
	}

	public function PostController_BeforeDiscussionRender_Handler($Sender)
	{
		if ($Sender->View == 'preview')
		{
			// Replace Emoticons in a preview of a new Discussion
			$this->PostController_BeforeDiscussionPreview_Handler($Sender);
		}
	}

	public function PostController_BeforeDiscussionPreview_Handler($Sender) {
		$this->doReplacement($Sender->Comment->Body);
	}
	
}
