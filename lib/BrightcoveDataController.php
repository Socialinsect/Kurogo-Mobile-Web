<?php

 class BrightcoveDataController extends DataController
 {
     protected $cacheFolder = "Videos"; // set the cache folder
     protected $cacheSuffix = "xml";   // set the suffix for cache files
     protected $DEFAULT_PARSER_CLASS='RSSDataParser';

     public static function factory($args=null)
     {
         $args['CONTROLLER_CLASS'] =  __CLASS__;
         $args['PARSER_CLASS'] =  'RSSDataParser';
         $controller = parent::factory($args);
         return $controller;
     }
  
     public function latest($accountid)
     {
     	 // TODO use $playerid
         //$this->setBaseUrl('http://link.brightcove.com/services/mrss/player$playerid/$accountid/new');
         $url = "http://link.brightcove.com/services/mrss/$accountid/new";
         $this->setBaseUrl($url);  
     	
         $data = $this->items(0,null,$total);
         
         return $data;
     }
     
     public function search($q,$token)
     {
     	 $url = "http://api.brightcove.com/services/library?command=search_videos&output=mrss&video_fields=id,name,shortDescription,thumbnailURL&page_size=20&get_item_count=true&sort_by=MODIFIED_DATE:DESC&any=$q&token=$token";
     	 
     	 $this->setBaseUrl($url);
	 
         $data = $this->items(0,null,$total);
         
         return $data;
     }

	 // retrieves a Brightcove Video based on its video id
	public function getItem($id)
	{
		// FIXME cannot add token to params above
		
		$url = "http://api.brightcove.com/services/library?command=find_video_by_id&video_id=$id&token=$token";
	    
		$this->setBaseUrl($url);
	    
	    $data = $this->items(0,null,$total);   
          
        foreach ($data as $item) {
            if ($item->getGUID()==$id) {
                return $item;
            }
        }
        
        return null;
        
	}

 }