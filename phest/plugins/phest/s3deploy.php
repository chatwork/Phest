<?php
    namespace ChatWork\Phest;
    use ChatWork\Utility\File;
    use Aws\S3\S3Client;
    use Aws\S3\Enum\CannedAcl;
    use Guzzle\Http\EntityBody;

/**
 * Amazon S3へProductionビルドをアップロード
 *
 * @method plugin_finish_s3deploy
 * @param  array $params パラメータ
 * @param  bool   $params.enable trueを指定すると実行
 * @param  string  $params.bucket バケット名
 * @param  enum    $params.region リージョン
 * @param  string  [$params.prefix] アップロードするキー名につけるPrefix
 * @param  string  $params.bucket バケット名
 * @param  string  $params.key AWSのアクセスキー
 * @param  string  $params.secret AWSのシークレットキー
 * @param  Phest $phest Phestオブジェクト
 */
function plugin_button_s3deploy(array $params, Phest $phest){
    $phest->registerSection('s3deploy','デプロイ完了');
    $phest->registerSection('s3deployerror','デプロイエラー',array('type' => 'danger'));

    require(DIR_PHEST.'/lib/vendor/aws/aws.phar');

    if ($phest->hasError()){
        $phest->add('s3deployerror','エラーが発生しているためデプロイしません');
        return false;
    }

    $dryrun = true;
    if (isset($params['dryrun'])){
        $dryrun = $params['dryrun'];
    }

    $prefix = '';
    if (isset($params['prefix'])){
        $prefix = $params['prefix'];
    }

    //$buckets = $s3->listBuckets();
    //print_a($buckets);

    $upload_dir = $phest->getOutputPath($phest->getLang(),'production');

    if (!is_dir($upload_dir)){
        $phest->add('s3deployerror','productionでビルドされたファイルが存在しません');
        return false;
    }


    $is_error = false;

    $required = array('bucket','region','key','secret');
    foreach ($required as $rval){
        if (empty($params[$rval])){
            $phest->add('s3deployerror','オプションが指定されていません: '.$rval);
            $is_error = true;
        }
    }

    $region_key = '';
    if (!$is_error){
        $regions = array('US_EAST_1','VIRGINIA','NORTHERN_VIRGINIA','US_WEST_1','CALIFORNIA','NORTHERN_CALIFORNIA','US_WEST_2','OREGON','EU_WEST_1','IRELAND','AP_SOUTHEAST_1','SINGAPORE','AP_SOUTHEAST_2','SYDNEY','AP_NORTHEAST_1','TOKYO','SA_EAST_1','SAO_PAULO','US_GOV_WEST_1','GOV_CLOUD_US');
        $region_key = strtoupper($params['region']);
        if (!in_array($region_key,$regions)){
            $phest->add('s3deployerror','regionの指定が正しくありません。regionには次の値が指定できます(大文字小文字の違いは無視されます): '.implode(', ',$regions));
            $is_error = true;
        }
    }

    if ($is_error){
        return false;
    }

    $bucket = $params['bucket'];
    $region = constant('\Aws\Common\Enum\Region::'.$region_key);
    $access_key = $params['key'];
    $secret_key = $params['secret'];
    $phest->add('s3deploy','リージョン: '.$region.' バケット: '.$bucket);
    try {
        $s3 = S3Client::factory(array(
            'key' => $access_key,
            'secret' => $secret_key,
            'region' => $region
            ));
    } catch (\Exception $e){
        $phest->add('s3deployerror',$e->getMessage());
        return false;
    }

    //S3上のアップロード状況を確認
    $object_flag = array();
    try {
        $iterator = $s3->getIterator('ListObjects',array(
            'Bucket' => $bucket,
            'Prefix' => $prefix,
            ));
        foreach ($iterator as $object){
            if ($object['Size'] !== 0 and substr($object['Key'],-1) !== '/'){
                $object_flag[$object['Key']] = true;
            }
        }
    } catch (\Exception $e){
        $phest->add('s3deployerror',$e->getMessage());
        return false;
    }

    $message_option = '';
    if ($dryrun){
        $message_option = ' <code>[dryrun]</code>';
    }

    //ローカルファイルをスキャンしてアップロード
    $file_list = File::getFileList($upload_dir);
    foreach ($file_list as $filepath){
        $key = strtr(ltrim(substr($filepath,strlen($upload_dir)),'\\/'),'\\','/');

        //upload
        $keyname = $prefix.$key;
        if (isset($object_flag[$keyname])){
            unset($object_flag[$keyname]);
        }
        try {
            if (!$dryrun){
                $result = $s3->putObject(array(
                    'Bucket' => $bucket,
                    'Key' => $keyname,
                    'Body' => EntityBody::factory(fopen($filepath,'r+')),
                    'ACL' => CannedAcl::PUBLIC_READ,
                    ));
            }

            $phest->add('s3deploy','追加: '.$keyname.$message_option);
        } catch (S3Exception $e){
            $phest->add('s3deployerror',$e->getMessage());
        } catch (\Exception $e){
            $phest->add('s3deployerror',$e->getMessage());
            return false;
        }
    }

    //サーバーにアップされてて、ローカルにないファイルを削除
    foreach (array_chunk(array_keys($object_flag),1000) as $chunked_key_list){
        $key_list = array();
        foreach ($chunked_key_list as $key){
            $key_list[] = array('Key' => $key);
        }

        try {
            if (!$dryrun){
                $result = $s3->deleteObjects(array(
                    'Bucket' => $bucket,
                    'Objects' => $key_list
                    ));
            }

            foreach ($chunked_key_list as $key){
                $phest->add('s3deploy','削除: '.$key.$message_option);
            }
        } catch (S3Exception $e){
            $phest->add('s3deployerror',$e->getMessage());
        } catch (\Exception $e){
            $phest->add('s3deployerror',$e->getMessage());
            return false;
        }
    }
}