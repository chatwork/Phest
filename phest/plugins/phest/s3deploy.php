<?php
    namespace ChatWork\Phest;
    use ChatWork\Utility\File;
    use Aws\S3\S3Client;
    use Aws\S3\Enum\CannedAcl;
    use Guzzle\Http\EntityBody;

/**
 * Amazon S3へProductionビルドをアップロード
 *
 *
 * リージョンは下記の中から指定：
 * US_EAST_1
 * VIRGINIA
 * NORTHERN_VIRGINIA
 * US_WEST_1
 * CALIFORNIA
 * NORTHERN_CALIFORNIA
 * US_WEST_2
 * OREGON
 * EU_WEST_1
 * IRELAND
 * AP_SOUTHEAST_1
 * SINGAPORE
 * AP_SOUTHEAST_2
 * SYDNEY
 * AP_NORTHEAST_1
 * TOKYO
 * SA_EAST_1
 * SAO_PAULO
 * US_GOV_WEST_1
 * GOV_CLOUD_US
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

    // $iterator = $s3->getIterator('ListObjects',array('Bucket' => $bucket));
    // foreach ($iterator as $object){
    //  print_a($object);
    // }

    $upload_dir = $phest->getOutputPath($phest->getLang(),'production');

    if (!is_dir($upload_dir)){
        $phest->add('s3deployerror','productionでビルドされたファイルが存在しません');
        return false;
    }


    if (!$dryrun){
        $is_error = false;
        $required = array('bucket','region','key','secret');
        foreach ($required as $rval){
            if (empty($params[$rval])){
                $phest->add('s3deployerror','オプションが指定されていません: '.$rval);
                $is_error = true;
            }
        }

        if ($is_error){
            return false;
        }

        $bucket = $params['bucket'];
        $region_key = strtoupper($params['region']);
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
    }

    $file_list = File::getFileList($upload_dir);
    foreach ($file_list as $filepath){
        $key = strtr(ltrim(substr($filepath,strlen($upload_dir)),'\\/'),'\\','/');

        //upload
        try {
            if (!$dryrun){
                $result = $s3->putObject(array(
                    'Bucket' => $bucket,
                    'Key' => $prefix.$key,
                    'Body' => EntityBody::factory(fopen($filepath,'r+')),
                    'ACL' => CannedAcl::PUBLIC_READ,
                    ));
                $phest->add('s3deploy',$prefix.$key);
            }else{
                $phest->add('s3deploy',$prefix.$key.' <code>[dryrun]</code>');
            }
        } catch (S3Exception $e){
            $phest->add('s3deployerror',$e->getMessage());
        } catch (\Exception $e){
            $phest->add('s3deployerror',$e->getMessage());
            return false;
        }
    }
}