 <div class="loomup-table-wrapper">
    <h3>Bienvenue dans votre espace de configuration de vos vidéos personnalisées.</h3>
    <p>
        Vous retrouverez toutes vos vidéos loomup, accompagnées chacune d'un shortcode pour pouvoir les intégrer facilement dans vos pages wordpress.
    </p>
    <p>
      Le shortcode sera à placé au sein de la page Checkout de WooCommerce.
    </p>
    <p>
        Si vous ne vous possédez pas votre clé loomup veuillez nous <a target="_blank" href="http://loomup.fr/information-video-connectee-personnalisee">contacter</a>.
    </p>
    <p>
          En cas <code>réinitialisation</code>, veuillez mettre à jour le shortcode sur la page checkout.
    </p>

    <form id="loomup-plugin" method="post" action="options.php">
        <?php settings_fields('loomup_key_config');?>
        <div class="form-group">
        <label>Votre clé</label>
            <input type="text" name="loomup_key" class="form-control" value="<?php echo esc_attr(get_option('loomup_key'));?>" />
        </div>
            <div>
                <?php submit_button('Activer votre clé');?>
            </div>
    </form>
