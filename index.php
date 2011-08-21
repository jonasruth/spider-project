<?php
$timecount = array(microtime(true));
require 'config.php';
header('Content-type:text/plain');

$ch = curl_init();
curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,2);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
if($config->connection_proxy_enabled === true){
    curl_setopt($ch, CURLOPT_PROXY, $config->connection_proxy);
    curl_setopt($ch, CURLOPT_PROXYPORT, $config->connection_proxy_port);
}
define('SPIDER_GLOBAL_MAX_ITERATION',100);

if($config->only_this_domain === true){
    if(preg_match('/([\w]+)\.([a-z]{2,3})(\.([a-z]{2}))?\/?$/',$config->url,$matches)){
        $pattern_link = '/http([s])?\:\/\/([\w]+\.)?(\b'.$matches[1].'\b)(\b\.'.$matches[2].'\b)'.(!empty($matches[4]) ? '(\b\.'.$matches[4].'\b)' : '').'[\w\/\.-]+/smi';
    }else{
        throw new Exception('Problema com o domínio.');
    }
}else{
    $pattern_link = '/http([s])?\:\/\/([\w]+\.)?[\w]+\.[a-z]{2,3}(\.[\w]{2})?[\w\/\.-]+/smi';
}

//echo $pattern_link; exit;

$global_url_array = array();
$global_count = 0;
$total_links = search_url($config->url,$config->max_depth);
curl_close($ch);
$global_unique_url_array = array_unique($global_url_array);
$global_unique_url_count = count($global_unique_url_array);
echo "LINK COUNT = {$total_links}".PHP_EOL;
echo "UNIQUE LINK COUNT = {$global_unique_url_count}".PHP_EOL;
echo 'END OF EXECUTION'.PHP_EOL;
$timecount[1] = microtime(true);
$timecount[2] = $timecount[1] - $timecount[0];
echo "TIME ELAPSED {$timecount[2]} seconds. [".(date('d/m/Y H:i:s',$timecount[0]).",".date('d/m/Y H:i:s',$timecount[1]))."]";
exit;
function search_url($search_url,$max_depth,$depth=0)
{
    echo str_repeat('-',72).PHP_EOL;
    echo ":: DEPTH LEVEL = {$depth} ::".PHP_EOL;
    echo str_repeat('-',72).PHP_EOL;

    if($depth>$max_depth) return;
    global $ch;
    global $global_count;
    ++ $global_count;
    if($global_count >= SPIDER_GLOBAL_MAX_ITERATION) {echo 'GLOBAL MAX ITERATIONS EXCEED...'; exit;};
    $link_count = 0;

    if(!preg_match('/\.(exe|jpg|jpeg|swf|png|gif|tar|gz|zip|wmv|avi)$/i',$search_url))
    {
        echo "[ Resource: {$search_url} ] OK".PHP_EOL;
        curl_setopt($ch,CURLOPT_URL,$search_url);
        $content = curl_exec($ch);
        $unique_links = content_search_links($content,$search_url);
        $link_count = count($unique_links);
        echo "[ Total: {$link_count} ]".PHP_EOL;
        if(!($depth+1>$max_depth))
        {
            foreach($unique_links as $one_link)
            {
                $link_count += search_url($one_link,$max_depth,$depth+1); 
            }
    
            echo str_repeat('-',72).PHP_EOL;
            echo ":: END OF DEPTH LEVEL = ".($depth+1)." ::".PHP_EOL;
            echo str_repeat('-',72).PHP_EOL;
        }
        return $link_count;
    }else{
        echo "[ Resource: {$search_url} ] AVOID".PHP_EOL;
        return 0;
    }
}

function content_search_links($text,$link){
    // http://www.domain.com
    // http://www.domain.com.br
    // http://domain.com.br
    // http://domain.com.br/products/miniaturas-etc-e-etal/100.html

    global $pattern_link;
    $pattern = $pattern_link;
    //$pattern = '/http\:\/\/[\w]+\/?.*/';
    if(preg_match_all($pattern,$text,$matches)){
        $uniq = array_unique($matches[0]);
        //$uniq = $matches[0]; 
        foreach($uniq as $k=>$vl)
        {
            if($vl === $link) unset($uniq[$k]);
            add_url($vl);
            echo " > Found on {$link}: {$vl}".PHP_EOL;
        }
        return $uniq;
    }
    return array();
}

function add_url($url){
    global $global_url_array;
    array_push($global_url_array,$url);
}
