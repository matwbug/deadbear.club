<?php 
    namespace Views;
    
    class MainView{

        private $fileName;
        private $header;
        private $footer;

        public function __construct($fileName, $info = [],$header = 'header', $footer = 'footer'){
            $this->fileName = $fileName;
            $this->info = $info;
            $this->header = $header;
            $this->footer = $footer;
        }
        public function renderTemplate(){
            include('templates/'.$this->header.'.php');
            include('pages/'.$this->fileName.'.php');
            include('templates/'.$this->footer.'.php');
        }
        public function renderNoHeader(){
            include('pages/'.$this->fileName.'.php');
        }
    }
?>