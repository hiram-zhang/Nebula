<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Controller', 'Controller');
 //Configure::write('debug',0);

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
	
    var $crawler_capacity = 5;
    var $file_scan_once = 5;
    var $source = array('1' => 'UrlQuery', '2' => 'CFS');
    var $judge_result = array('Clean' => 'Clean','Malicious' => 'Malicious');
    var $url_status = array('Pending' => 'Pending','Processing' => 'Processing', 'Success' => 'Success', 'Failed' => 'Failed');
    var $job_status = array('Pending' => 'Pending','Processing' => 'Processing', 'Success' => 'Success', 'Failed' => 'Failed');
    var $file_status = array('Pending' => 'Pending','Processing' => 'Processing', 'Finished' => 'Finished', 'Error' => 'Error');
    var $csv_status = array('ErroredOut' => 'ErroredOut','Completed' => 'Completed');
    var $error_code = array('0' => 'Success', '1' => 'Ftp Connection Failed', '2' => 'Ftp Connection Close Failed', '3' => 'Ftp Login Failed', '4' => 'Ftp Create Directory Failed',
                                  '5' => 'Ftp Set Passive Mode Failed', '6' => 'Copy File to Ftp Server Failed', '7' => 'Copy File From Ftp Server Failed','8' => 'Ftp Delete File Failed',
                                   '9' => 'Ftp Directory Not Found', '10' => 'File Not Found','11' => 'Read File Failed', '12' => 'Write File Failed', '13' => 'Create File Failed', 
									'14' => 'Delete File Failed', '15' => 'Directory Not Found','16' => 'Create Directory Failed','17' => 'Ftp rename Failed');                      
    public function beforeFilter() {
        $this->local_file_path = '';
    }
    
    
    
    public function formatTime($time) {
	
		if(!empty($time))
    	$result = date("Y-m-d h:i:s a", $time);
    	return $result;
    }
    public function findModuleId($module_name) {
        $this->loadModel('Manager');
        $dispatcher_id = $this->Manager->find('first', array('fields' => array('dispatcher_id'),'conditions' => array('Manager.module_name' => $module_name)));
      
        return $dispatcher_id;
    }
    
    
     public function updateModuleLog($module_name,$log){
     	$module = $this->Manager->find('first', array('conditions' => array('Manager.module_name' => $module_name)));
     	$this-> loadModel('Log');
     	
     	
     }
     public function updateManagerStart($module_name) {
    	$dispatcher_id = $this->findModuleId($module_name);
    	//debug($dispatcher_id);
        $this->Manager->id = $dispatcher_id;
        if (!$this->Manager->exists()) {
    	    throw new NotFoundException(__('Invalid id'));
        }
        $this->loadModel('Log');
        $this->Log->create();
       
        
        
    	$data = array();
        $data['start_time'] = $this->formatTime(time());
        $data['running'] = 1;
    	$this->Manager->save($data);
    	
    	 $log = array();
    	 $log['module_id'] = $dispatcher_id['Manager']['dispatcher_id'];
    	 $log['start_time'] =  $data['start_time'];
    	 $log['module_name'] = $module_name;
    	 $log['raw_log'] = "$module_name start running.".PHP_EOL;
    	 $this->Log->save($log);
    	
    	 $log['id'] = $this->Log->id;
    	 return $log;
    	 
    }
    
     public function updateManagerFinish($module_name,$code, $log) {
    	$dispatcher_id = $this->findModuleId($module_name);
        $this->Manager->id = $dispatcher_id;
        if (!$this->Manager->exists()) {
    	    throw new NotFoundException(__('Invalid id'));
        }
    	$data = array();
        $data['end_time'] = $this->formatTime(time());
        $data['running'] = 0;
        $data['error_code'] = $code;
        $data['state'] = $this->error_code[$code];
    	$this->Manager->save($data);
    	
    	$this->loadModel('Log');
    	$log['end_time'] = $data['end_time'];
    	$log['error_code'] = $code;
    	$log['state'] = $this->error_code[$code];
    	$log['raw_log'] = $log['raw_log']."$module_name run finished.";
    	$this->Log->save($log);
    	
    	
    }
    
    public function updateManagerErrorCode($module_name, $code) {
    	$dispatcher_id = $this->findModuleId($module_name);
        $this->Manager->id = $dispatcher_id;
        if (!$this->Manager->exists()) {
    	    throw new NotFoundException(__('Invalid id'));
        }
    	$data = array();
        $data['error_code'] = $code;
        $data['state'] = $this->error_code[$code];
    	$this->Manager->save($data);
    }
    
    
    public function updateManagerEndTime($module_name) {
    	$dispatcher_id = $this->findModuleId($module_name);
        $this->Manager->id = $dispatcher_id;
        if (!$this->Manager->exists()) {
    	    throw new NotFoundException(__('Invalid id'));
        }
    	$data = array();
        $data['end_time'] = $this->formatTime(time());
        
    	$this->Manager->save($data);
    }
    public function updateManagerStartTime($module_name) {
    	$dispatcher_id = $this->findModuleId($module_name);
        $this->Manager->id = $dispatcher_id;
        if (!$this->Manager->exists()) {
    	    throw new NotFoundException(__('Invalid id'));
        }
    	$data = array();
        $data['start_time'] = $this->formatTime(time());
        
    	$this->Manager->save($data);
    }
    public function updateManagerNotRunning($module_name) {
        $dispatcher_id = $this->findModuleId($module_name);
        $this->Manager->id = $dispatcher_id;
    	if (!$this->Manager->exists()) {
            throw new NotFoundException(__('Invalid id'));
	}
    	$data = array();
        $data['running'] = 0;
    	$this->Manager->save($data);
    }
    public function updateManagerRunning($module_name) {
        $dispatcher_id = $this->findModuleId($module_name);
        $this->Manager->id = $dispatcher_id;
    	if (!$this->Manager->exists()) {
            throw new NotFoundException(__('Invalid id'));
	}
    	$data = array();
        $data['running'] = 1;
    	$this->Manager->save($data);
    }
    
    public function updateManagerState($module_name, $state) {
    	 $dispatcher_id = $this->findModuleId($module_name);
        $this->Manager->id = $dispatcher_id;
    	if (!$this->Manager->exists()) {
            throw new NotFoundException(__('Invalid id'));
		}
    	$data = array();
        $data['state'] = $state;
    	$this->Manager->save($data);
    }
    
     function updateManagerInterval($module, $interval) {
        $this->Manager->id = $module['Manager']['dispatcher_id'];
    	if (!$this->Manager->exists()) {
            throw new NotFoundException(__('Invalid id'));
	}
    	$data = array();
        $data['interval'] = $interval;
    	$this->Manager->save($data);
    }
    public function updateManagerMinimumInterval($module, $min_interval) {
        $this->Manager->id = $module['Manager']['dispatcher_id'];
    	if (!$this->Manager->exists()) {
            throw new NotFoundException(__('Invalid id'));
	}
    	$data = array();
        $data['min_interval'] = $min_interval;
    	$this->Manager->save($data);
    }
    
    
    public function parseProjectDir($PHP_SELF) {
		$pattern = '/\/(.*?\/.*?\/.*?\/)/i';
		preg_match($pattern, $PHP_SELF, $matches);
		if(!empty($matches))
		//debug($matches);
		return $matches[1];
	}
	public function ftpConnect($server_address, $server_port,$module,$log) {
		$conn = ftp_connect($server_address, $server_port);
		if(!$conn){
		 	//$log['raw_log'] = $log['raw_log']."Fail to connect FTP server $server_address at port $server_port!".PHP_EOL;
		 	$this->log= $this->appendLog($this->log,"Fail to connect FTP server $server_address at port $server_port!");
		 	$this->updateManagerFinish($module,1,$log);
		 	exit;
		} 
		return $conn;
	}
	public function ftpClose($conn,$module,$log) {
		$result = ftp_close($conn);
		if(!$result) {
			//$log['raw_log'] = $log['raw_log'].'Ftp connection close failed'.PHP_EOL;
			$this->log= $this->appendLog($this->log,'Ftp connection close failed.');
			$this->updateManagerFinish($module,2,$log);
			exit;
		} 
		return $result;
	}
	public function ftpLogin($conn, $username, $password,$module,$log) {
		$login_result = ftp_login($conn, $username, $password); //True or False
		if(!$login_result){
		 	//$log['raw_log'] = $log['raw_log']."Failed login in with username $username and password $password.".PHP_EOL;
		 	$this->log= $this->appendLog($this->log,"Failed login in with username $username and password $password.");
			$this->updateManagerFinish($module,3,$log);
		 	exit;
		} 
		return $login_result;
	}
	public function ftpMkdir($conn, $dir,$module,$log) {
		$result = ftp_mkdir($conn, $dir);
		if(!$result){
		 	//$log['raw_log'] = $log['raw_log']."Failed to create directory $dir on FTP server.".PHP_EOL;
		 	$this->log= $this->appendLog($this->log,"Failed to create directory $dir on FTP server.");
			$this->updateManagerFinish($module,4,$log);
		 	exit;
		} 
		return $result;
	}
	public function ftpPassiveModeOn($conn,$module,$log) {
		$result = ftp_pasv($conn, true);
		if(!$result){
		 	//$log['raw_log'] = $log['raw_log'].'Set ftp passive mode failed'.PHP_EOL;
		 	$this->log= $this->appendLog($this->log,'Set ftp passive mode failed.');
			$this->updateManagerFinish($module,5,$log);
		 	exit;
		} 
		return $result;
	}
	public function ftpCopyFromLocal($conn, $remoteDir, $remoteFile, $localFile, $mode, $resume,$module,$log) {
		$pwd = ftp_pwd($conn);
		
		//$resume = 0 copy from begining;
		if($this->ftpPassiveModeOn($conn,$module,$log)) {
			if(ftp_chdir($conn, $remoteDir)) {
			
				$result = ftp_put($conn,$remoteFile, $localFile, $mode, $resume);
				if(!$result) {
					//$log['raw_log'] = $log['raw_log'].'File copy to Ftp server failed'.PHP_EOL;
					$this->log= $this->appendLog($this->log,'File copy to Ftp server failed.');
					$this->updateManagerFinish($module,6,$log);
					exit;
				} 
			} 
		}
		ftp_chdir($conn, $pwd);
		return $result;
	}
	public function ftpCopyToLocal($conn, $remoteDir, $localFile, $remoteFile, $mode,$module,$log) {
		$pwd = ftp_pwd($conn);
		$result = '';
		//$resume = 0 copy from begining;
		if($this->ftpPassiveModeOn($conn,$log)) {
			if(ftp_chdir($conn, $remoteDir)) {
			
				$result = ftp_get($conn, $localFile,$remoteFile, $mode);
				if(!$result) {
					//$log['raw_log'] = $log['raw_log'].'File copy from Ftp server failed'.PHP_EOL;
					$this->log= $this->appendLog($this->log,'File copy from Ftp server failed.');
					$this->updateManagerFinish($module,7,$log);
					exit;
				} 
			} 
		}
		ftp_chdir($conn, $pwd);
		return $result;
	}
	public function ftpDelete($conn, $file_path,$module,$log) {
		$result = ftp_delete($conn, $file_path);
		if ($result) {
			//echo "$file_path deleted successful\n";
			return true;
		} else {
			//$log['raw_log'] = $log['raw_log']. "Could not delete $file_path on FTP server".PHP_EOL;
			$this->log= $this->appendLog($this->log,"Could not delete $file_path on FTP server.");
			$this->updateManagerFinish($module,8,$log);
			
			return false;
		}
	}
	
	
	public function isFtpDirExists($conn, $dir,$module,$log) {
		$default_dir = '/';
		if(ftp_chdir($conn,$dir)){
			//debug(ftp_pwd($conn));
			//If it is a directory, then change the directory back to the original directory
			ftp_chdir($conn, $default_dir);
			return true;
		} else {
			//$log['raw_log'] = $log['raw_log']. "FTP directory $dir not exsits".PHP_EOL;
			$this->log= $this->appendLog($this->log,"FTP directory $dir not exsits.");
			$this->updateManagerErrorCode($module, 9,$log);
			return false;
		}       
	}
	public function isFileExsits($file,$module,$log){
		$exist =  file_exists($file);
		
		if(!$exist){
			//$log['raw_log'] = $log['raw_log']. "Local file $file not exsits".PHP_EOL;
			$this->log= $this->appendLog($this->log,"Local file $file not exsits.");
			$this->updateManagerErrorCode($module, 10,$log);
			return $exist;
		}
	}
	public function readFileContents($file,$module,$log){
		$success = @file_get_contents($file);
		if(!$success){
			//$log['raw_log'] = $log['raw_log']. "Read file $file failed".PHP_EOL;
			$this->log= $this->appendLog($this->log,"Read file $file failed.");
			$this->updateManagerFinish($module,11,$log);
		 	exit;
		}
		return $success;
	}
	public function writeContentsToFile($file,$content,$module,$log){
		$success = @file_put_contents($file,$content);
		if(!$success){
			//$log['raw_log'] = $log['raw_log']. "Write to $file failed".PHP_EOL;
			$this->log= $this->appendLog($this->log,"Write to $file failed.");
			$this->updateManagerFinish($module,12,$log);
		 	exit;
		}
		
		return;
	}
	public function createFile($file,$module,$log){
		$success = @fopen($file,"w");
		if(!$success){
			//$log['raw_log'] = $log['raw_log']. "Create file $file failed".PHP_EOL;
			$this->log= $this->appendLog($this->log,"Create file $file failed.");
			$this->updateManagerFinish($module,13,$log);
		 	exit;
		}
		return;
	}
	public function deleteFile($file,$module,$log){
		$success = @unlink($file);
		if(!$success){
			//$log['raw_log'] = $log['raw_log']. "Delete file $file failed".PHP_EOL;
			$this->log= $this->appendLog($this->log,"Delete file $file failed.");
			$this->updateManagerFinish($module,14,$log);
		 	exit;
		}
		return;
	}
	public function isDirExist($dir,$module,$log) {
		if(is_dir($dir)) {
			
			return true;
		} else {
			//$log['raw_log'] = $log['raw_log']. "Local directory $dir not exsits".PHP_EOL;
			$this->log= $this->appendLog($this->log,"Local directory $dir not exsits.");
			$this->updateManagerErrorCode($module, 15,$log);
			return false;
		}
	}
	public function makeDir($dir,$module,$log) {
		$success = @mkdir($dir);
		if(!$success){
			//$log['raw_log'] = $log['raw_log']. "Make directory $dir failed".PHP_EOL;
			$this->log= $this->appendLog($this->log,"Make directory $dir failed.");
			$this->updateManagerFinish($module,16,$log);
		 	exit;
		}
		return;
	}
	public function ftpRename($conn, $old_file,$new_file,$module,$log) {
		$result= ftp_rename($conn,$old_file,$new_file);
		if ($result) {
			//echo "$old_file rename successful\n";
			return true;
		} else {
			//$log['raw_log'] = $log['raw_log']. "Rename FTP file from $old_file to $new_file failed".PHP_EOL;
			$this->log= $this->appendLog($this->log,"Rename FTP file from $old_file to $new_file failed.");
			$this->updateManagerFinish($module,17,$log);
			//echo "could not rename $file_path\n";
			return false;
		}
	}
	
	public function updateURLPending($url_id){
		$this->loadModel('UrlScheduler');
		$this->UrlScheduler->id = $url_id;
		if (!$this->UrlScheduler->exists()) {
			throw new NotFoundException(__('Invalid url'));
		}
		$data = array();
		$data['url_status'] = $this->url_status['Pending'];
		$this->UrlScheduler->save($data);
	}
   
	public function updateURLProcessing($url_id){
		$this->loadModel('UrlScheduler');
		$this->UrlScheduler->id = $url_id;
		if (!$this->UrlScheduler->exists()) {
			throw new NotFoundException(__('Invalid url'));
		}
		$data = array();
		$data['url_status'] = $this->url_status['Processing'];
		$this->UrlScheduler->save($data);
	}
   
	public function updateURLSuccess($url_id){
		$this->loadModel('UrlScheduler');
		$this->UrlScheduler->id = $url_id;
		if (!$this->UrlScheduler->exists()) {
			throw new NotFoundException(__('Invalid url'));
		}
		$data = array();
		$data['url_status'] = $this->url_status['Success'];
		$this->UrlScheduler->save($data);
	}
   	public function updateURLFailed($url_id){
		$this->loadModel('UrlScheduler');
		$this->UrlScheduler->id = $url_id;
		if (!$this->UrlScheduler->exists()) {
			throw new NotFoundException(__('Invalid url'));
		}
		$data = array();
		$data['url_status'] = $this->url_status['Failed'];
		$this->UrlScheduler->save($data);
	}
	public function allCrawlersTried($url_id){
                $this->loadModel('Crawler');
		$all_crawlers = $this->Crawler->find('count');
		$tried_crawlers = $this->FtpHub->find('count',array('conditions' => array('FtpHub.url_id' => $url_id)));
		return $all_crawlers == $tried_crawlers;
   }
   
   public function updateJobPending($job_id){
		$this->loadModel('FtpHub');
		$this->FtpHub->id = $job_id;
		if (!$this->FtpHub->exists()) {
			throw new NotFoundException(__('Invalid job'));
		}
		$data = array();
		$data['status'] = $this->job_status['Pending'];
		$this->FtpHub->save($data);
	}
	public function updateJobProcessing($job_id){
		$this->loadModel('FtpHub');
		$this->FtpHub->id = $job_id;
		if (!$this->FtpHub->exists()) {
			throw new NotFoundException(__('Invalid job'));
		}
		$data = array();
		$data['status'] = $this->job_status['Processing'];
		$this->FtpHub->save($data);
	}
  
	public function updateJobSuceess($job_id){
		$this->loadModel('FtpHub');
		$this->FtpHub->id = $job_id;
		if (!$this->FtpHub->exists()) {
			throw new NotFoundException(__('Invalid job'));
		}
		$data = array();
		$data['status'] = $this->job_status['Success'];
		$this->FtpHub->save($data);
	}
   
	public function updateJobFailed($job_id){
		$this->loadModel('FtpHub');
		$this->FtpHub->id = $job_id;
		if (!$this->FtpHub->exists()) {
			throw new NotFoundException(__('Invalid job'));
		}
		$data = array();
		$data['status'] = $this->job_status['Failed'];
		$this->FtpHub->save($data);
	}
	
	public function findCrawlerFtpServer($crawler_id) {
		$this->loadModel('Crawler');
		$this->Crawler->recursive = 2;
		$ftp_server_info = $this->Crawler->find('all', array('conditions' => array('crawler_id' => $crawler_id), 'contain' => array('FtpServer'=>array('Credential'))));
		//debug($ftp_server_info);
		return $ftp_server_info;
	}
	
	public function appendLog($log,$content){
		
		$this->log['raw_log'] = $this->log['raw_log'].$content.PHP_EOL;
		//debug($this->log);
		return $this->log;
	}
}
