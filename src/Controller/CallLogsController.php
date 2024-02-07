<?php

namespace App\Controller;

use App\Entity\CallLogs;
use App\Repository\CallLogsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CallLogsController extends AbstractController
{

    private $callLogsRepositiry;
    private $apiKey;
    private $em;

    public function __construct(CallLogsRepository $callLogsRepositiry,EntityManagerInterface $em)
    {
        $this->callLogsRepositiry = $callLogsRepositiry;
        $this->apiKey = 'b9c9e0c9e04642f5a66b2278c4cb1e25';
        $this->em = $em;
    }

    #[Route('/', name: 'call_logs')]
    public function index(): Response
    {
       return $this->render('call_logs/index.html.twig');
    }


    #[Route('/result', name: 'call_logs.result')]
    public function result(): Response
    {
        $statistics = $this->callLogsRepositiry->getStatistic();
        return $this->render('call_logs/results.html.twig',[
            'statistics' => $statistics
        ]);
    }
    
    #[Route('/process', name: 'call_logs.process')]
    public function process(Request $request): Response
    {
        $file = $request->files->get('file');
        if ($file) {
           
            $allowedFileTypes = ['csv'];
            
            if(!in_array($file->getClientOriginalExtension(), $allowedFileTypes)) {
                return new Response('Invalid file type. Please use csv only.', 400);
            }

            $destination = $this->getParameter('kernel.project_dir') . '/public/uploads';
            $filename    = md5(uniqid()) . '.' . $file->getClientOriginalExtension();
            $file->move($destination, $filename);
            self::upload($filename);
            
            return new Response('Success');
        }

       return new Response('Invalid file upload.', 400);
       
    }
    
    
    public function upload($file){
        $datas = self::read($file);
        $batchsize = 10;
        foreach($datas as $index => $data){
            $date = date('Y-m-d H:i:s',strtotime($data[1]));
            $verify = $this->callLogsRepositiry->verifyData($date,$data[0]);

            if(!$verify){
                $logs = new CallLogs();
                $continent_ip = self::getContentByIp($data[4]);
                $continent_phone = self::getContinentByPhone($data[3]);
                $logs->setClientId($data[0]);
                $logs->setDate($date);
                $logs->setDuration($data[2]);
                $logs->setPhoneNo($data[3]);
                $logs->setIpAddress($data[4]);
                $logs->setIsSameContinent($continent_ip == $continent_phone ? 1 : 0);
              
                $this->em->persist($logs);
            }
         
            if(($index % $batchsize) == 0){
                $this->em->flush();
                $this->em->clear();
            }
          
        }
       
       
    }

     /**
     * get continent code by ip
     * @param int $ip
     * @return string
     */

    public function getContentByIp($ip){
        $url = "https://api.ipgeolocation.io/ipgeo?apiKey=".$this->apiKey."&ip=".$ip."&lang=en&fields=continent_code&excludes=";

        $cURL = curl_init();

        curl_setopt($cURL, CURLOPT_URL, $url);
        curl_setopt($cURL, CURLOPT_HTTPGET, true);
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: '.$_SERVER['HTTP_USER_AGENT']
        ));

        $respone = json_decode(curl_exec($cURL),true);

        return $respone['continent_code'];
    }

     /**
     * read csv
     * @param string $name
     * @param string $dir
     * @return array
     */

     public static function read($name,$dir="uploads/"){
 
        $dataHeader = array();
       
  
        if( !file_exists($dir.$name)){
            $return = array(
                'message' => 'File not found!',
            );
            
        } else {

            if (($handle = fopen($dir.$name, "r")) !== FALSE) {
                $row = 1;
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $num = count($data);
                    if($row > 1){
                        for ($c = 0; $c < $num; $c++) {
                            $dataHeader[$row][] = $data[$c];
                        }
                    } 
                $row++;
            }

            fclose($handle);
            $return =  $dataHeader;

            } 

        }

        return $return;

	}

    /**
     * get continent code by phone number
     * @param int $phonenumber
     * @return string
     */
    public function getContinentByPhone($phonenumber){

            $lines_array = file("uploads/country.text");
            $continent = '';
            $country_prefix = substr($phonenumber, 0, -9);
            foreach($lines_array as $line) {
               if(strpos($line, $country_prefix) !== false) {
                    $continent = explode("\t", $line);
                    if(isset($continent[12])){
                        if($continent[12] == $country_prefix){
                            $continent = $continent[8];
                            break;
                        }
                    }
                }
            }

            return $continent;
    }

    
}
