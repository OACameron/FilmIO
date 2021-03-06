<?php

require_once'functions.php';

class Film{



    private $_name;
    private $_filmID;
    private $_year;
    private $_description;
    private $_imgPath;
    /**
     * @return mixed
     */
    public function getImgPath()
    {
        return $this->_imgPath;
    }

    /**
     * @param mixed $_imgPath
     */
    private function setImgPath($_imgPath)
    {
        $this->_imgPath = $_imgPath;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->_name;
    }
    

    /**
     * @return mixed
     */
    public function getFilmID()
    {
        return $this->_filmID;
    }

    /**
     * @return mixed
     */
    public function getYear()
    {
        return $this->_year;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * @param mixed $_name
     */
    private function setName($_name)
    {
        $this->_name = $_name;
    }

    /**
     * @param mixed $_filmID
     */
    private function setFilmID($_filmID)
    {
        $this->_filmID = $_filmID;
    }

    /**
     * @param mixed $_year
     */
    private function setYear($_year)
    {
        $this->_year = $_year;
    }

    /**
     * @param mixed $_description
     */
    private function setDescription($_description)
    {
        $this->_description = $_description;
    }

    /**
     * Film constructor.
     * @param $filmID
     */
    function __construct($filmID){
        global $apiKey;
        $data = file_get_contents("http://www.omdbapi.com/?apikey=". $apiKey ."&i=tt". $filmID);
        $filmInfo = json_decode($data);
        $this->setName($filmInfo->Title);
        $this->setDescription($filmInfo->Plot);
        $this->setYear($filmInfo->Released);
        $this->setImgPath($filmInfo->Poster);
        $this->setFilmID($filmID);
        
    }

    /**
     * @return integer
     */
    public function getTotalLikes(){
        global $client;

        $result = $client->run("MATCH (u:User)-[r:likes]->(f:Film) WHERE f.ID='" . $this->getFilmID() ."' RETURN r, COUNT(r) as no");
        if($result->firstRecord() != null){
            $number = $result->firstRecord()->value("no");
        }

        else{
            $number = 0;
        }
        return $number;
    }

    public function getTotalDislikes(){
        global $client;
        $result = $client->run("MATCH (u:User)-[r:dislikes]->(f:Film) WHERE f.ID='" . $this->getFilmID() ."' RETURN r, COUNT(r) as no");
        if($result->firstRecord() != null){
            $number = $result->firstRecord()->value("no");
        }

        else{
            $number = 0;
        }
        return $number;
    }

    /**
     * @param User $user
     * @return User[]
     */
    public function friendsThatLikeThis($user){
        if($user->existsInDB($user)){
            global $client;
            $friends = array();
            $result = $client->run('MATCH (u:User {username: "' . $user->getUsername() .'"})-[:likes]->
            (:Film {ID: \'' . $this->getFilmID() . '\'})
                <-[:likes]-(p:User),
                (u)-[:friends]-(p)
                RETURN p.username as Username');
            foreach($result->records() as $record){
                $friends[] = new User($record->value("Username"));
            }
            return $friends;
        }
        return null;
    }



    
    
}