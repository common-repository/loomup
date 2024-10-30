<?php
include 'view/loomup-admin-template.php';

class Loomup_Videos
{
    public $url = 'http://server.loomup.fr/api/wordpress/api/';

    private $result = '';

    public $adminLoomupTemplate;

    private $admin_loomup;

    public function __construct()
    {
        $this->adminLoomupTemplate = new Loomup_Admin_Template();
        $this->adminLoomupTemplate->loomup_displayForm();
        $this->admin_loomup = new stdClass();
        $this->loomup_checkData();
        if($this->loomup_integrite($this->loomup_handle_request('GET','video',array())))
        {
            $this->admin_loomup->title = 'Liste des vidéos disponibles.';
            $this->admin_loomup->table = $this->loomup_handle_request('GET','videos',array())['msg'];
        }
           $this->adminLoomupTemplate->loomup_displayListVideos($this->admin_loomup);
    }

    private function loomup_integrite ($array)
    {
        if($array['status'] == 200)
        {
            return true;
        }
        else
        {
             switch ($array['status'])
             {
                case 204:
                    $this->admin_loomup->title = "Vieullez fournir votre clé cliente.";
                    break;
                case 406:
                    $this->admin_loomup->title = "Votre clé cliente n'est pas correcte.";
                    break;
                case 500:
                    $this->admin_loomup->title = "Une erreur serveur est survenue.";
                    break;
            }
            return false;
        }
    }

    function loomup_handle_request($method,$what,$data)
    {
        if(get_option('loomup_key') )
        {
            $api = loomup_callAPI($method,API_URL.$what,$data,get_option('loomup_key'));

            if($api['status'] != 200)
            {
                return $api;
            }
            return $api;
        }
        else{
            return loomup_integrite(['status'=>204]);
        }
    }

    public function loomup_checkData()
    {
        if(get_option('loomup_key') )
        {
            $this->data['loomup_key'] = get_option('loomup_key');
        }

    }

    function loomup_callAPI($method, $url, $data = false,$api_key = NULL)
{
	$curl = curl_init(sprintf("%s?%s", $url, http_build_query($data)));

	switch ($method)
	{
		case "POST":
			curl_setopt($curl, CURLOPT_POST, 1);
			if ($data)
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			break;
		case "PUT":
			curl_setopt($curl, CURLOPT_PUT, 1);
			break;
		default:
			if ($data)
				$url = sprintf("%s?%s", $url, http_build_query($data));
	}
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
		'X-Api-Key:'.$api_key
    ));

	$result = curl_exec($curl);

	$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);

	if($httpcode == 200)
	{
		return array('status' => $httpcode,'msg'=>json_decode($result));
	}
	else
	{
		return array('status' => $httpcode,'msg'=> $httpcode);
	}
}
}

$loomupAdminVideos = new Loomup_Videos();
