<?php

	if (!class_exists('Plugin')) 
		die('Hacking attemp!');

        
	class PluginTopicfilter extends Plugin {
            public $aInherits = array(
                'action' => array('ActionBlog')
            );
    
	    public function Init() {
                parent::Init();
            }
	}
