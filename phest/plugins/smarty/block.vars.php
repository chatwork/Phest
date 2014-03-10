<?php
function smarty_block_vars($params, $content, $template, &$repeat)
{
    if(!$repeat){
        if (isset($content)) {
            if (!isset($params['type'])){
                $params['type'] = 'yaml';
            }

            $vars = array();
            switch ($params['type']){
                case 'yaml':
                    $vars = spyc_load($content);
                    break;
                case 'json':
                    $vars = json_decode($content, true);

                    $json_error = '';
                    switch (json_last_error()) {
                        case JSON_ERROR_NONE:
                            break;
                        case JSON_ERROR_DEPTH:
                            $json_error = ' - Maximum stack depth exceeded';
                            break;
                        case JSON_ERROR_STATE_MISMATCH:
                            $json_error = ' - Underflow or the modes mismatch';
                            break;
                        case JSON_ERROR_CTRL_CHAR:
                            $json_error = ' - Unexpected control character found';
                            break;
                        case JSON_ERROR_SYNTAX:
                            $json_error = ' - Syntax error, malformed JSON';
                            break;
                        case JSON_ERROR_UTF8:
                            $json_error = ' - Malformed UTF-8 characters, possibly incorrectly encoded';
                            break;
                        default:
                            $json_error = ' - Unknown error';
                            break;
                    }

                    if ($json_error){
                        throw new Exception('Smarty Plugin Error: {vars} json parse error'.$json_error);
                    }
                    break;
                default:
                    throw new Exception('Smarty Plugin Error: {vars} unknown type "'.$params['type'].'"');
                    break;
            }
            $template->assign($vars);
        }
    }
}