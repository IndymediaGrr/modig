<?php

/* Test de sécurité
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

/* Les includes de spip utilisé dans cette balise
 */
include_spip('inc/texte');
include_spip('inc/lang');
include_spip('inc/mail');
include_spip('inc/date');
include_spip('inc/meta');
include_spip('inc/session');
include_spip('inc/filtres');
include_spip('inc/acces');
include_spip('inc/documents');
include_spip('inc/ajouter_documents');
include_spip('inc/getdocument');
include_spip('inc/barre');
include_spip('base/abstract_sql');

spip_connect();
//charger_generer_url();

if(defined('_SPIP19300')) {
  generer_url_entite(_request('id_article'), "article"); 
 }
 else {
   charger_generer_url();
 }


/*
 * Cette balise permet de définir la nécessite d'une connexion https.
 * (Voir la méthode modig_require_https dans modig_pipeline.php)
 *
 */

function balise_REQUIRE_HTTPS($p)
{
   $p = calculer_balise_dynamique($p,'REQUIRE_HTTPS',array());
   return $p; 
}

function balise_REQUIRE_HTTPS_dyn()
{
    global $require_https;
    $require_https = true;
}

?>
