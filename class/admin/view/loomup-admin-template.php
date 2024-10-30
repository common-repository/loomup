<?php

    class Loomup_Admin_Template{

        private $videos;

        function __construct()
        {

        }

        public function loomup_displayVideo($video)
        {
            $html = '
                <h2>'.esc_html($video->video->video_name).'</h2>
                <form>
            ';
            foreach ($video->attributes as $v)
            {
                $html .='
                        <label>'.esc_html($v->attr_libelle).'</label>
                        <input type = "text" placeholder="'.esc_attr($v->attr_libelle).'" value="'.esc_attr($v->attr_libelle).'"/>
                ';
            }

            $html .= '
                </form>
            ';
            echo $html;
        }

        public function loomup_displayForm()
        {
            require  plugin_dir_path( __FILE__ ) . 'loomup-admin-form.php';
        }

        function loomup_displayListVideos($videos)
        {
            $attributs = array(
                array(
                    'att_woocommerce' => 'wc_order_total_sale',
                    'att_definition' => 'Montant total de la commande'
                ),
                array(
                    'att_woocommerce' => 'wc_client_point',
                    'att_definition' => 'Points de fidélités du client'
                ),
                array(
                    'att_woocommerce' => 'wc_client_gender',
                    'att_definition' => 'Genre du client'
                ),
                array(
                    'att_woocommerce' => 'wc_order_number',
                    'att_definition' => 'Numéro de la commande'
                ),
                array(
                    'att_woocommerce' => 'wc_order_date',
                    'att_definition' => 'Date de la commande'
                )
            );

            $html ='
                    <h2>'.$videos->title.'</h2>
                ';
            if(isset($videos->table))
            {
                $html .= '
                    <table class="loomup-table">
                        <thead>
                            <tr>
                                <th>Titre de la vidéo</th>
                                <th>Shortcode à intégrer</th>
                                <th>Configuration</th>
                                <th>Réinitialisation</th>
                            </tr>
                        </thead>
                        <tbody>
                 ';
                foreach ($videos->table as $key => $value)
                {

                    $post = get_page_by_path("loomup_".$value->video_slug,OBJECT,'loomup_videos');

                    $link = admin_url( 'post.php?post='.$post->ID.'&action=edit', '');

                    $html .= '
                        <tr>
                            <td>'.esc_html($value->video_name).' </td>
                            <td><code>[loomup id="'.esc_html($value->video_id).'" post="'.esc_html($post->ID).'"]</code></td>
                            <td><a href="'.esc_attr($link).'" class="wp-menu-image dashicons-before dashicons-admin-tools"></a></td>
                            <td><a href="'.esc_attr($this->loomup_remove_link($post->ID)).'"><span class="dashicons dashicons-image-rotate"></span><a></td>
                        </tr>
                    ';
                }
                $html .= '
                    </tbody>
                </table>
                ';
            }
            $html.= '
                <h2>Utilisation des attributs pour vos vidéos</h2>
                <table class="loomup-table">
                    <thead>
                        <tr>
                            <th>Attributs WooCommerce</th>
                            <th>Définition</th>
                        </tr>
                    </thead>
                    <tbody>
                ';
             foreach ($attributs as $attribut ) {
                $html.= '<tr>';
                 foreach ($attribut as $att) {
                     $html.= '<td>'.esc_html($att).'</td>';
                 }
                $html.= '</tr>';
             }
             $html.='
                        </tbody>
                    </table>
                </div>
            ';
             echo $html;
        }


        private function loomup_remove_link($post_id)
        {
            $url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];;
            $url_parts = parse_url($url);
            parse_str($url_parts['query'], $params);

            $params['remove_custom_post'] = $post_id;     // Overwrite if exists

            // Note that this will url_encode all values
            $url_parts['query'] = http_build_query($params);

            return $this->loomup_my_server_url() . $url_parts['path'] . '?' . $url_parts['query'];
        }

        private function loomup_my_server_url()
        {
            $server_name = $_SERVER['SERVER_NAME'];

            if (!in_array($_SERVER['SERVER_PORT'], [80, 443])) {
                $port = ":$_SERVER[SERVER_PORT]";
            } else {
                $port = '';
            }

            if (!empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1')) {
                $scheme = 'https';
            } else {
                $scheme = 'http';
            }
            return $scheme.'://'.$server_name.$port;
        }
    }
