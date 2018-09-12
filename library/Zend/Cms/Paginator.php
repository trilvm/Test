<?php 
/**
* Zend Framework 
*
* @category   Zend
* @package    Cms_Paginator
* @copyright  Copyright (c) 2008 Zend Vietnamese Team (http://www.zend.vn) 
* @version    $Id: Paginator.php 2797 2008-01-28 01:35:30Z 
* @author     Marsu (ZVN Founder team)
* 
*/


class Cms_Paginator{ 

   protected $totalRows;
   protected $rowsInPage;
   protected $linkUrl;
   protected $htmlCurrentItem;
   protected $htmlItem;
   protected $currentPage;
   protected $startRecord;
   protected $cssItem = null;
   protected $cssCurruntItem = null;
   protected $hasNext = null;
   protected $hasPrevious = null;
   protected $realCountPage;
   protected $showCountItem = 5;
   
   
   
   public function setLinkUrl($url)
    {
       $url = explode("/page", $url);
        return $this->linkUrl = $url[0] . '/page/';
    } 
    
    public function totalRows($totalRowsInData)
    {
        return $this->totalRows = $totalRowsInData;
    } 
    
    public function showCountItem($showCountItem)
    {
        return $this->showCountItem = $showCountItem;
    } 
    
    public function startRecord()
    {
        $this->startRecord = $this->currentPage - 1;
        return $this->startRecord = $this->startRecord * $this->rowsInPage;
    }
    
    public function rowsInPage($value)
    {
        return $this->rowsInPage = $value;
    } 
    
    public function cssItem($cssItem)
    {
        return $this->cssItem = $cssItem;
    } 
    
    public function cssCurruntItem($cssCurruntItem)
    {
        return $this->cssCurruntItem = $cssCurruntItem;
    } 
    
    public function hasNext($cssHtml)
    {
       $realCountPage = $this->realCountPage();   
       if($this->currentPage < $realCountPage){
          $nextPage = $this->currentPage + 1;   
          $nextPage = $this->linkUrl . $nextPage;   
          $this->hasNext = '<a href="' . $nextPage.'" style="margin-left: 5px;"><span class="' . $this->cssItem .'">' . $cssHtml . '</span></a>';
       }else{
          $this->hasNext = '';
       }
       return $this->hasNext;
    }
    
    public function hasLast($cssHtml)
    {
       $realCountPage = $this->realCountPage();   
       if($this->currentPage < $realCountPage -1){
          $hasLast = $realCountPage;   
          $hasLast = $this->linkUrl . $hasLast;   
          $this->hasLast = '<a href="' . $hasLast .'" style="margin-left: 5px;"><span class="' . $this->cssItem .'">' . $cssHtml . '</span></a>';
       }else{
          $this->hasLast = '';
       }
       return $this->hasLast;
    }
    
     public function hasFirst($cssHtml)
    {
       $realCountPage = $this->realCountPage();   
       //echo $this->currentPage - $this->showCountItem
       if($this->currentPage - $this->showCountItem >=0){
          $hasFirst = $realCountPage;   
          $hasFirst = $this->linkUrl . '1';   
          $this->hasFirst = '<a href="' . $hasFirst .'" style="margin-left: 5px;"><span class="' . $this->cssItem .'">' . $cssHtml . '</span></a>';
       }else{
          $this->hasFirst = '';
       }
       return $this->hasFirst;
    }
    
    public function hasPrevious($cssHtml)
    {
       $realCountPage = $this->realCountPage();   
       if($this->currentPage > 1){
          $previousPage = $this->currentPage - 1;   
          $this->hasPrevious = '<a href="' . $previousPage.'" style="margin-left: 5px;"><span class="' . $this->cssItem .'">' . $cssHtml . '</span></a>';
       }else{
          $this->hasPrevious = '';
       }
       return $this->hasPrevious;
    }
    
    public function currentPage($currentPage)
    {   
       if($currentPage == 0){
          $currentPage = 1;
       }else{
          $currentPage = $currentPage;
       }
        return $this->currentPage = $currentPage;
    } 
    
    public function realCountPage()
    {
       $realItem = $this->totalRows % $this->rowsInPage;
        if($realItem >0){
             $realItem = explode(".",$this->totalRows / $this->rowsInPage);
             $this->realCountPage = $realItem[0] + 1;
          }else{
             $this->realCountPage = $this->totalRows / $this->rowsInPage;
          }
          return $this->realCountPage;
    }
    
    public function printPaginator()
    {
       $realCountPage = $this->realCountPage();   
       $strPage = '';
       
       $itemCenter = explode(".",$this->showCountItem/2);
       $itemCenter = $itemCenter[0];
      
      if($this->currentPage == 1){
         $iStart = 1;
       $iStop =  $this->showCountItem;   
      }else{          
         if($this->currentPage - $itemCenter >0){
            $iStart = $this->currentPage - $itemCenter;   
            $iStop =  $this->currentPage + $itemCenter;
            if($iStop>$this->realCountPage){
               $iStop = $this->realCountPage;
               $iStart = $this->realCountPage - $this->showCountItem;               
               if($iStart <= 0){
                  $iStart = 1;
               }
            }
            
         }else{
            $iStart = 1;
            $iStop =  $this->showCountItem;
         }
      }
       
       if($realCountPage >1){
          for($i=$iStart; $i<=$iStop;$i++){
                if($this->currentPage == $i){
                  $strPage .= '<a href="' . $this->linkUrl . $i .'" style="margin-left: 5px;"><span class="' . $this->cssCurruntItem .'">' . $i . '</span></a>';
                }else{
                   $strPage .= '<a href="' . $this->linkUrl . $i .'" style="margin-left: 5px;"><span class="' . $this->cssItem .'">' . $i . '</span></a>';
                }                
          }
       }
         
         
      return $strPage;
    } 

}//ends class