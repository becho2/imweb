<?php
/*
* @brief 2년 이상 접속하지 않은 회원들의 데이터 이전
* 
* @author Jinhoe
* @date 22.03.01
*/


$startTime = time();

$conn = new mysqli('127.0.0.1','root','danceintherain','imweb');
$autoresult = $conn->autocommit(FALSE);

try {
    $twoYearsAgo = date("Y-m-d", strtotime("-2 years"));
    $copyqry = "INSERT INTO imweb.unconnected_member(idx, email, name, join_date, last_login_time)
                    SELECT idx,email,name,join_date,last_login_time FROM member WHERE last_login_time < '$twoYearsAgo'";
    $result = $conn->query($copyqry);
    if ( !$result ) {
        throw new Exception($conn->error);
    }

    $delqry = "DELETE FROM imweb.member WHERE idx IN(SELECT idx FROM imweb.unconnected_member)";
    $result = $conn->query($delqry);
    if ( !$result ) {
        throw new Exception($conn->error);
    }

    $conn->commit();
    $conn->autocommit(TRUE);

    $endTime = time();
    $duration = $endTime - $startTime;
    $msg = "실행소요시간(초): ".$duration;
    echo $msg;
    insertLog($msg);

} catch(Throwable $e){
    $conn->rollback();
    $conn->autocommit(TRUE);
    echo $e->getMessage();
    insertLog($e->getMessage());
}


function insertLog($msg){
    global $conn;
    $msg = str_replace("'","\'",$msg);
    $qry = "INSERT INTO imweb.batch_log(description, created)
                VALUES ('$msg', NOW())";
    $result = $conn->query($qry);
}