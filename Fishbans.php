<?php
class Fishbans {

    /**
     * Send a request to $file at $location using the specified method and params. This returns the contents of the page.
     *
     * Normally, you shouldn't use this, and can instead use the public field classes to send requests to TortoiseLabs' API.
     *
     * @param string $file Name of the file - start this with a /.
     * @param bool $auth If you want to send your authentication username and key with the request with basic authentication.
     * @param string $method The method you want to use - GET or POST.
     * @param array $params an accociative array of values you want to GET/POST.
     * @param string $location The base location you're sending the request to. This is set up to use SSL (HTTPS)
     * @return string Data given by the page.
     */
    public function sendRequest($file, $auth = false, $method = 'GET', $params = array(), $location = 'http://api.fishbans.com'){
        $method = strtoupper($method);
        if($method === 'GET'){
            $p = '';
            foreach($params as $key => $val){
                if($p === ''){
                    $p .= urlencode($key) . '=' . urlencode($val);
                } else {
                    $p .= '&' . urlencode($key) . '=' . urlencode($val);
                }
            }
        } else {
            $p = http_build_query($params);
        }
        $headers = array();
        //if($auth){
        //    $headers[] = 'Authorization: Basic ' . base64_encode($this->username . ':' . $this->password);
        //}
        if($method === "POST"){
            $headers[] = "Content-type: application/x-www-form-urlencoded";
            $headers[] = 'Content-Length: ' . strlen($p);
        }
        $options = array(
            'http' => array_merge(array(
                'method' => $method,
                'header' => implode("\r\n", $headers)
            ), $method === 'POST' ? array('content' => $p) : array()) // Yuck. :(
        );
        $context = stream_context_create($options);

        $r = fopen($location . $file . ($method === 'GET' && $p != '' ? '?' . $p : ''), 'r', false, $context); // Also yuck.
        $data = stream_get_contents($r);
        fclose($r);
        return $data;
    }

    public function json_get($file){
        return json_decode($this->sendRequest($file), true);
    }

    public function user_service($username, $service){
        $data = $this->json_get('/bans/' . $username . '/' . $service . '/');
        if($data['success'] == false){
            return false;
        }
        return new Bans_Service($data);
    }
    public function user($username){
        $data = $this->json_get('/bans/' . $username . '/');
        if($data['success'] == false){
            return false;
        }
        return new Bans($data);
    }
}

class Bans_Service extends Bans{

}

class Bans {

    private $json;

    public function __construct($json){
        $this->json = $json;
    }

    /**
     * Return the raw JSON array that we received from fishbans.com
     * @return array array of data
     */
    public function raw(){
        return $this->json;
    }

    /**
     * Get the total number of bans a user has.
     * @return int Number of bans
     */
    public function total(){
        $ret = 0;
        foreach ($this->json['bans']['service'] as $service){
            $ret += $service['bans'];
        }
        return $ret;
    }

    /**
     * Get an assoc. array of all bans on this . array(ip => service)
     * @return array Array of bans
     */
    public function all(){
        $ret = array();
        foreach ($this->json['bans']['service'] as $service){
            foreach($service['ban_info'] as $server => $reason){
                $ret[$server] = $reason;
            }
        }
        return $ret;
    }

    /**
     * Get an array of bans by service. This is what fishbans gave me, almost raw.
     * array(service => array('bans' => number, 'ban_info' => array('ip' => 'reason')))
     * @return array Bans by service
     */
    public function by_service(){
        return $this->json['bans']['service'];
    }
}
