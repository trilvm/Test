<?php
/**
*
*
*/
class filedinhkem{
	public $folder = "";
	public $idObject = 0;
	public $maso = "";
	public $nam = 0;
	public $thang = 0;
	public $mime = "";
	public $fileName = "";
	public $type = 0;
	public $content = "";
	public $user = 0;
	public $timeUpdate = "";
	public $path = "";
    
    public function getFolder(){
        return mysql_real_escape_string($this->folder);
    }
    public function getIdObject(){
        if((int)$this->idObject == 0){
            throw new Exception("idObject is null");
        }
        return (int)$this->idObject;
    }
    public function getMaSo(){
        if($this->maso == ""){
            $this->maso = md5(time());
        }
        return mysql_real_escape_string($this->maso);
    }
    public function getNam(){
        return (int)$this->nam;
    }
    public function getThang(){
        return (int)$this->thang;
    }
    public function getMime(){
        if($this->mime == ""){
            throw new Exception("mime is null");
        }
        return mysql_real_escape_string($this->mime);
    }
    public function getFileName(){
        return mysql_real_escape_string($this->fileName);
    }
    public function getType(){
        return (int)$this->type;
    }
    public function getContent(){
        return base64_decode($this->content);
    }
    public function getUser(){
        if((int)$this->user == 0){
            throw new Exception("user is null");
        }
        return (int)$this->user;
    }
    public function getTimeUpdate(){
        return mysql_real_escape_string($this->timeUpdate);
    }
    public function getPath(){
        if($this->path == ""){
            throw new Exception("path to save file is null");
        }
        if (!file_exists($this->path)) {
            Common::rmkdir($this->path);
        }
        return $this->path;
    }
    /**
    * @purpose : save file info to database
    */
    public function insert(){
        $sql = sprintf("
        INSERT INTO GEN_FILEDINHKEM_%s(FOLDER,ID_OBJECT,MASO,NAM,THANG,MIME,FILENAME,TYPE,CONTENT,USER,TIME_UPDATE)
        VALUES('%s',%s,'%s',%s,%s,'%s','%s',%s,'%s',%s,'%s')"
		,$this->getNam()
		,$this->getFolder()
		,$this->getIdObject()
		,$this->getMaSo()
		,$this->getNam()
		,$this->getThang()
		,$this->getMime()
		,$this->getFileName()
		,$this->getType()
		,''
		,$this->getUser()
		,$this->getTimeUpdate()
		);
		query($sql);
    }
    public function delete(){
        // to do some thing
    }
    public function update(){
        // to do some thing
    }
    /**
    * @purpose : save file info to hard drive
    */
    public function upload(){
        $file = $this->getPath().'\\'.$this->getMaSo();
        //$this->writeLog("upload",$file);
        file_put_contents($file, $this->getContent(), FILE_APPEND | LOCK_EX);
    }
    public function writeLog($functionName,$content)
    {
        $today = date("[ d/m/Y ] [ h:i:s ]");
        $myFile = "D:\\SVN\\Google Drive\\qlvbdh_gialai\\Source Code\\01_Develop\\qlvbdh_services\\Log.txt";
        $fh = fopen($myFile, 'a') or die("can't open file");
        $stringData = $today." \t".$functionName."\t".$content."\n\n";
        fwrite($fh, $stringData);
        fclose($fh);
    }
}