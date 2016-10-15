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

include_spip('modig_fonctions');
include_spip('inc/modig');

spip_connect();
// charger_generer_url();
// SPIP 2.0 
generer_url_entite(array('id_mot'),'mot'); 
//generer_url_entite("","","",""); 
function balise_MODE_CONTRIB($p) {
    $p = calculer_balise_dynamique($p, 'MODE_CONTRIB', array('id_article'));
    return $p;
}

function balise_MODE_CONTRIB_stat($args, $filtres) {
    return ($args);
}

function balise_MODE_CONTRIB_dyn($id_article,$nature)
{

   $result = spip_query("SELECT statut,chapo FROM spip_articles WHERE id_article=".$id_article);
   $row = spip_fetch_array($result);
   $statut = $row['statut'];
   $chapo = $row['chapo'];
   $parqui = quoi_par_qui($id_article);
   if( _request('chapo') ) $chapo = _request('chapo');

   $data = array(
      'id_article' => $id_article,
      'chapo'      => $chapo,
      'statut_nouv'=> entites_html(stripslashes(_request('statut_nouv'))),
      'statut'     => $statut,
      'parqui'    => $parqui

   ); 

   if($nature == 'article')
   {
      return array('formulaires/mode_article', 0, $data);
   }
   else
   {
      return array('formulaires/mode_evenement', 0, $data);
   }
}

?>
