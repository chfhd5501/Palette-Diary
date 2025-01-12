<?php
//to solve cors issue
header("Access-Control-Allow-Origin: ");

//db connect
$host = "localhost";
$s_username = "db";
$s_password = "dbpassword";
$dbname = "palette_diary";
$conn = mysqli_connect($host, $s_username, $s_password, $dbname);
try {
    $dbDiaryColor=NULL;
    $dbDiaryKeyword=NULL;
    $dbDiaryDate=NULL;
    $dbDiaryBody=NULL;
    $dbDiarymainPic=NULL;
    $dbDiarySubPic1=NULL;
    $dbDiarySubPic2=NULL;

    $json = json_decode(file_get_contents('php://input'), TRUE);
    $error = "none";
    $stat = "none";

    $cookie = apache_request_headers()['Cookie'];
    $email = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', explode("=", $cookie)[1])[1]))), TRUE)['email'];
    $exp = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', explode("=", $cookie)[1])[1]))), TRUE)['exp'];

    if($exp<time()) {
      throw new exception('토큰 만료 오류', 423);
    }
    
    $selectDiaryCode = $json['diary_code']; // 열람하고자 하는 일기의 diary_code 반환

    $selectDiarySql = "select * from diary where diary_code='$selectDiaryCode';";
    $selectDiaryResult = mysqli_fetch_assoc(mysqli_query($conn, $selectDiarySql));

    if(!$selectDiaryResult) {
        throw new exception('DB Fail - Can Not select Diary', 422);
    }
    else {
        if((empty($selectDiaryResult['color'])==false)) {
            $dbDiaryColor = $selectDiaryResult['color'];
        }
        if((empty($selectDiaryResult['keyword'])==false)) {
            $dbDiaryKeyword = $selectDiaryResult['keyword'];
        }
        if((empty($selectDiaryResult['d_date'])==false)) {
            $dbDiaryDate = $selectDiaryResult['d_date'];
        }
        if((empty($selectDiaryResult['main_pic'])==false)) {
            $dbDiarymainPic= $selectDiaryResult['main_pic'];
        }
    }

    $selectDiaryDetailSql = "select * from diary_detail where diary_code='$selectDiaryCode';";
    $selectDiaryDetailResult = mysqli_fetch_assoc(mysqli_query($conn, $selectDiaryDetailSql));

    if(!$selectDiaryDetailResult) {
        throw new exception('DB Fail - Can Not select Diary', 422);
    }
    else {
        if((empty($selectDiaryDetailResult['diary_body'])==false)) {
            $dbDiaryBody = $selectDiaryDetailResult['diary_body'];
        }
        if((empty($selectDiaryDetailResult['sub_pic1'])==false)) {
            $dbDiarySubPic1 = $selectDiaryDetailResult['sub_pic1'];
        }
        if((empty($selectDiaryDetailResult['sub_pic_2'])==false)) {
            $dbDiarySubPic2 = $selectDiaryDetailResult['sub_pic_2'];
        }
        $stat="success";
    }   

    mysqli_close($conn);

}catch(exception $e) {
    $stat   = "error";
    $error = ['errorMsg' => $e->getMessage(), 'errorCode' => $e->getCode()];
}finally{
        $data=json_encode(['color'=> $dbDiaryColor, 'keyword' => $dbDiaryKeyword, 'd_date'=>$dbDiaryDate, 'diary_body' => $dbDiaryBody,'mainPic' => $dbDiarymainPic, 'subPic1' => $dbDiarySubPic1, 'subPic2' => $dbDiarySubPic2,'result_code' => $stat, 'error' => $error]);
        header('Content-type: application/json'); 
        echo $data;
}
?>
