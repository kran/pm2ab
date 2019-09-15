<?php
$P = json_decode(file_get_contents($argv[1]));

$api = new Apiary();
$api->name = $P->info->name;
$api->description = $P->info->description;
$api->host = get_host($P->variable);

foreach($P->item as $it) {
    $group = new Group();
    $group->name = $it->name;
    $group->description = $it->description ?? "";
    foreach($it->item as $item){
        $group->init($item);
    }

    $api->groups[] = $group;
}

$api->render();
/* var_dump($api->groups[1]); */

class Apiary {
    public $name;
    public $description;
    public $format = "1A";
    public $host;

    public $groups = [];

    public function render(){
        include("./tpl.php");
    }

}

class Group {
    public $name;
    public $description;
    public $examples = [];
    public $resources = [];

    public function addexample($itt){
        $example = new Example();
        $example->name = $itt->name;
        $req = new Request();
        $example->description = $itt->request->description ?? "";

        $req->method = $itt->request->method;

        if(count($itt->response)) {
            $r = $itt->response[0];
            $request = $r->originalRequest;
            $resp = new Response();
            $resp->code = $r->code;
            $resp->setbody($r->body);
            $resp->setType($r->header);
        } else {
            $request = $itt->request;
            $resp = new Response();
            $resp->status = "OK";
            $resp->setbody(json_encode(['status'=>"success", 'data'=>null], JSON_PRETTY_PRINT));
        }

        
        $req->seturl($request->url);
        $req->setbody($request->body);
        $req->setHeaders($itt->request->header ?? []);
        $req->setParameters($itt->request->url->query ?? []);
        $req->setParameters($itt->request->url->variable ?? []);

        $example->request= $req;
        $example->response = $resp;


        $this->examples[] = $example;

        return $example;
    }

    public function init($ex){
        if(isset($ex->item)){
            $g = new Group;
            $g->name = $ex->name;
            $g->description = $ex->description ?? "";
            foreach($ex->item as $it){
                $exp = $g->addexample($it);
                $g->path = $exp->request->path;
            }
            /* $g->init($r->item); */
            $this->resources[] = $g;
        } else {
            $this->addexample($ex);
        }
    }
}

class Example {
    public $name;
    public $request;
    public $response;
    public $description="";
}

class Request {
    public $method;
    public $path;
    public $type = "application/json";
    public $headers = [];
    public $parameters = [];
    public $body=null;

    private function transurl($u){
        if($u{0} == ':'){
            return "{".trim($u, ':')."}";
        }
        return $u;
    }

    public function seturl($u){
        $r = '';
        foreach($u->path as $p){
            $r .= "/" . $this->transurl($p);
        }
        if(isset($u->query) && count($u->query)){
            $r .= '?';
            $t = [];
            foreach($u->query as $q){
                $r .= "{$q->key}={{$q->key}}";
                /* $t[$q->key] = $this->transurl(":{$q->key}"); */
            }

            /* $r .= http_build_query($t); */
        }
        $this->path = $r;
    }

    public function setHeaders($h) {
        foreach($h as $it) {
            if($it->key=="Content-Type") continue;
            $this->headers[$it->key] = $it->value;
        }
    }

    public function setbody($b) {
        /* $b = str_replace("\t", "    ", $b); */
        /* $this->body = $b; */
        if(!isset($b->mode)) return;
        $b = $b->{$b->mode};
        $de = @json_decode($b);
        if(!$de){
            $this->body = $b;
        } else {
            $this->body = json_encode($de, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        $this->body = str_replace("\t", "    ", $this->body);
    }

    public function setParameters($p) {
        foreach($p as $it){
            if(isset($it->description)){
                $required = $it->description == '?' ? 'optional' : 'required';
            } else {
                $required = "required";
            }
            $type = preg_match("/^[\d\.]+$/", $it->value) ? 'number' : "string";
            $this->parameters[$it->key] = [$it->value, "$required, $type"];
        }
    }
}

class Response extends Request{
    public $type = "application/json";
    public $code = 200;
    public $status;
    public $body;

    public function settype($headers){
        if(!is_array($headers)) return;
        foreach($headers as $it){
            if($it->key=="Content-Type"){
                $this->type = $it->value;
                break;
            }
        }
    }

    public function setbody($b) {
        /* $this->body = $b; */
        $de = json_decode($b);
        if(!$de){
            $this->body = $b;
        } else {
            $this->body = json_encode($de, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        $this->body = str_replace("\t", "    ", $this->body);
    }
}

function get_host($vars) {
    foreach($vars as $it){
        if($it->key == "HOST") return $it->value;
    }
}


function pad($str, $space) {
    $s = str_repeat(" ", $space);
    $str = $s . str_replace("\n", "\n$s", $str);
    return $str;
}
