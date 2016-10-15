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
if(defined('_SPIP19300')) {
  generer_url_entite(_request('id_article'), "article"); 
 }
 else {
   charger_generer_url();
 }

//include_spip('modig_fonctions');

/*
 * Cette fonction renvoi le statut d'un article
 *
 * TODO il serait peut-être bien de la rajouter le support des forums (a voir).
 */
function balise_MODE_PARQUI($p)
{

   $_id_article = champ_sql('id_article', $p);
   $_id_forum = champ_sql('id_forum', $p);
   $p->code = 'modig_calculer_mode_parqui('. $_id_article.','.$_id_forum. ')';
   $p->statut = 'php';

   return $p;
}

?>
