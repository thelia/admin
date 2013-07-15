<?php

class PaginationAdmin extends Baseobj {
    
    /**
     *
     * max number of page displays in the same time
     * 
     * @var int 
     */
    protected $maxPagesDisplayed;
    
    /**
     * How many results are display on each page
     *
     * @var int
     */
    protected $viewPerPage;
    
    /**
     * How many pages needed for display all results
     * 
     * @var int
     */
    protected $totalPages;
    
    /**
     *  How many results to display without pagination
     * 
     * @var int
     */
    protected $totalResults = 0;
    
    /**
     *
     * current page 
     * 
     * @var int
     */
    protected $currentPage;
    
    /**
     * 
     * 
     * 
     * @param string $query
     * @param int $currentPage
     * @param int $maxPagesDisplayed
     * @param int $viewPerPage
     */
    public function __construct($query, $currentPage, $maxPagesDisplayed = 15, $viewPerPage = 30) {
        parent::__construct();
        $this->maxPagesDisplayed = $maxPagesDisplayed;
        $this->viewPerPage = $viewPerPage;
        $this->currentPage = $currentPage;
        
        try{
           $resul = $this->query($query, true);
           $this->totalResults = $this->get_result($resul, 0);
        }
        catch (Exception $e){
            Tlog::error($e->getMessage());
            $this->totalResults = 0;
        }
        $this->calculatePages();
    }
    
    
    /**
     * Calculate how many pages is needed for display all the results
     */
    protected function calculatePages(){
        $this->totalPages = ceil($this->totalResults/$this->viewPerPage);
    }
    
    /**
     * Return max number of page displays in the same time
     * 
     * @return int
     */
    public function getMaxPagesDisplayed(){
        return $this->maxPagesDisplayed;
    }
    
    /**
     * return How many results are display on each page
     * 
     * @return int
     */
    public function getViewPerPage(){
        return $this->viewPerPage;
    }
    
    /**
     * return the started for the limit paramter in query
     * 
     * @return int
     */
    public function getStarted(){
        return ($this->currentPage-1) * $this->viewPerPage;
    }
    
    /**
     * return the number of pages needed for displaying all the results
     * 
     * @return int
     */
    public function getTotalPages(){
        return $this->totalPages;
    }
    
    /**
     * return the current page
     * 
     * @return int
     */
    public function getCurrentPage(){
        return $this->currentPage;
    }
    
    /**
     * 
     * return the number of the previous page
     * 
     * @return int
     */
    public function getPreviousPage(){
        return $this->currentPage - 1;
    }
    
    /**
     * return the number of the next page
     * 
     * @return int
     */
    public function getNextPage(){
        return $this->currentPage +1;
    }
    
    /**
     * return the number of the page that start the pagination to display
     * 
     * return int
     */
    public function getStartedPagination(){
        $start = 0;
        
        if($this->totalPages > $this->maxPagesDisplayed){
            if($this->currentPage + $this->maxPagesDisplayed -1 > $this->totalPages){
                $start = $this->totalPages - $this->maxPagesDisplayed;
            }
            else {
                $start = ($this->currentPage-1)?:1;
            }
        }
        else {
            $start = 1;
        }
        
        return $start;
    }
    
    /**
     * return the number of the page that finish the pagination to display
     * 
     * @return int
     */
    public function getEndPagination(){
        $end = 0;
        if($this->totalPages > $this->maxPagesDisplayed){
            if($this->currentPage + $this->maxPagesDisplayed -1 > $this->totalPages){
                $end = $this->totalPages;
            }
            else {
                $end = $this->currentPage + $this->maxPagesDisplayed -1;
            }
        }
        else {
            $end = $this->totalPages;
        }
        return $end;
    }
}
?>
