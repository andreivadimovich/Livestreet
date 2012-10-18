<?php

    /*
     * Фильтрация записей по типу в блоге.
     */
    class PluginTopicfilter_ActionBlog extends PluginTopicfilter_Inherit_ActionBlog {

        /*
         * Получение списка типа записей.
         * 
         * @return array список типов.
         */
        protected function getTopicTypes() {
            $oTopic = new ModuleTopic(Engine::getInstance());
            return $oTopic->GetTopicTypes();
        }

        /*
         * Устанавливает события для всех типов записей.
         */
        protected function RegisterEvent() {
            parent::RegisterEvent();

            foreach ($this->getTopicTypes() as $types) {
                $this->AddEventPreg('/^\w+$/i', '/^' . $types . '+$/i', 
                        '/^(page([1-9]\d{0,5}))?$/i', 'EventShowByTypes');
            }
        }


        /*
         * Получение записей из блога по типу.
         * 
         * @return array список записей блога.
         */
        protected function GetTopicsByTopicType($oBlog, $iCount, $iPage, 
                $iPerPage, $type) {
            $aFilter = array(
                'topic_publish' => 1,
                'blog_id'       => $oBlog->getId(),
                'topic_type'    => array($type)
            );
            return $this->Topic_GetTopicsByFilter($aFilter, $iPage, $iPerPage);
        }

        
        /*
         * Формирование записей и вывод в шаблон. 
         */
        protected function EventShowByTypes() {
            $sBlogUrl = $this->sCurrentEvent;
            $sPage    = $this->getParam(1);
            $type     = $this->getParam(0);
            
            $iRecordCount         = Config::Get('block.blogs.row'); 
            $sWebPathRoot         = Config::Get('path.root.web');
            $sPaginatorUrl        = $sWebPathRoot.'/blog/'.$sBlogUrl.'/'.$type;
            $iPaginatorPagesCount = Config::Get('pagination.pages.count');
            
            // Проверяем есть ли блог с таким УРЛ.
            if (!($oBlog = $this->Blog_GetBlogByUrl($sBlogUrl))) 
                return parent::EventNotFound();

            // Проверяем является ли текущий пользователь пользователем блога.
            $bNeedJoin = true;
            if ($this->oUserCurrent) {
                if ($this->Blog_GetBlogUserByBlogIdAndUserId($oBlog->getId(), 
                        $this->oUserCurrent->getId())) 
                    $bNeedJoin = false;
            }
            
            // Передан ли номер блога.
            if (preg_match("/^page(\d+)$/i", $sPage, $aMatch)) 
                $iPage = $aMatch[1];
            else
                $iPage = 1;
            
            
            // Получаем список топиков.
            $iCount = 0;
            $aResult = $this->GetTopicsByTopicType($oBlog, $iCount, $iPage, 
                    $iRecordCount, $type);
            $aTopics = $aResult['collection'];
            
            // Формируем постраничность.
            $aPaging = $this->Viewer_MakePaging($aResult['count'], $iPage, 
                    $iRecordCount , $iPaginatorPagesCount, $sPaginatorUrl);
 
            // Получаем число новых топиков в текущем блоге.
            $this->iCountTopicsBlogNew = $this->Topic_GetCountTopicsByBlogNew($oBlog);
            
            // Загружаем переменные в шаблон.
            $this->Viewer_Assign('sMenuSubItemSelected', $this->sMenuSubItemSelect);
            $this->Viewer_Assign('aPaging',              $aPaging);
            $this->Viewer_Assign('aTopics',              $aTopics);
            $this->Viewer_Assign('oBlog',                $oBlog);
            $this->Viewer_Assign('bNeedJoin',            $bNeedJoin);
            $this->Viewer_AddHtmlTitle($oBlog->getTitle());
            
            // Устанавливаем шаблон вывода.
            $this->SetTemplateAction('blog');
        }
    }
