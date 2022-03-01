<?php
/*
* @brief 1분 스케줄링을 통해 1분 미만 간격으로 배치 서비스 실행하기
* 배치 서비스의 1회 실행시간이 설정한 간격보다 길어질 경우 에러 리턴
* @author Jinhoe
* @date 22.02.26
*/

// 반복시키고 싶은 간격(초 단위)을 PERIOD에 설정한다
define('PERIOD', 1);
$repeat = ceil(60/PERIOD);

for($i = 0; $i < $repeat; $i++){
    $startTime = microtime(TRUE);

    try {
        $response = json_decode(batchService());
        echo "현재 접속 ip는 ".$response->origin."입니다.\n";
        $endTime = microtime(TRUE); 
        $timeOfBatch = $endTime - $startTime;

        $interval = floor((PERIOD - $timeOfBatch) * 1000000);
        if($interval < 0){
            $errMsg = "Warning: Batch Service의 실행시간이 ".$timeOfBatch."초로 실행간격보다 길어 종료합니다.";
            throw new Exception($errMsg);
        }
        if($i == ($repeat - 1)){
            echo "=== END ===\n";
        } else {
            usleep($interval);
        }

    } catch(Throwable $e){
        insertLog($e->getMessage());
        echo $e->getMessage();
        break;
    }
}

/*
* @brief 리퀘스트 및 리스폰스 테스트용 샘플 api와 통신
* @author Jinhoe
*/
function batchService(){
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://httpbin.org/get',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
}

function insertLog($msg){
    $conn = new mysqli('127.0.0.1','root','danceintherain','imweb');
    $msg = str_replace("'","\'",$msg);
    $qry = "INSERT INTO imweb.batch_log(description, created)
                VALUES ('$msg', NOW())";
    $result = $conn->query($qry);
}