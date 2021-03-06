<?php

include_spip('base/abstract_sql');

function filtre_contient($haystack, $needle)
{
   if( is_array($haystack) && count($haystack))
   {
      return FALSE === array_search($needle, $haystack) ? '' : ' ';
   }

   return FALSE;
}

function extrait_liens($texte) {
   $result = '';
   if (preg_match("/\[.*->([^\]]*)\]/", $texte, $matches)) {
      foreach ($matches as $match) {
         $result = "    --> $match\n";
      }
   }
   return $result;
}


/* Pour récupérer le statut d'un article. 
 * (Utilisé par la balise STATUT)
 */
function modig_calculer_statut($type,$id_objet)
{
   switch($type)
   {
   case "articles":
     /* $query = "SELECT statut FROM spip_articles WHERE id_article="._q($id_objet);
      $result = spip_query($query);
      $row = spip_fetch_array($result); 
      return $row['statut']; */
     // SPIP 2.0
     $row=sql_fetsel("statut","spip_articles","id_article=".sql_quote($id_objet));
     return $row['statut'];
      break;

   case "forum":
     /* $query = "SELECT statut FROM spip_forum WHERE id_forum="._q($id_objet);
      $result = spip_query($query);
      $row = spip_fetch_array($result);
     */
     // SPIP 2.0
     $row = sql_fetsel("statut","spip_forum","id_forum=".sql_quote($id_objet));
      return $row['statut'];
      break;
   }
}

function quoi_par_qui($id_article)
{
  /*   $result=spip_query("
      SELECT log.statut, aut.nom
      FROM logs_mode log, spip_auteurs aut 
      WHERE log.id_auteur=aut.id_auteur
      AND log.id="._q($id_article)."
      AND type IN ('art','evt')
      ORDER BY log.instant"
   );
  */
  $result = sql_allfetsel("log.statut, aut.nom", " logs_mode log, spip_auteurs aut",
			  "log.id_auteur=aut.id_auteur AND log.id=".sql_quote($id_article)." AND type IN ('art','evt')","","log.instant");
   $quoiparqui="";
   //   while ($row = spip_fetch_array($result))
   foreach ($result as $row)
   {
      if ($row['statut']) {
         if (strcmp($quoiparqui,"")!=0) {
            $br = "<br/>";
         }
         switch ($row['statut']) 
         {
         case 'accepte'  : $quoiparqui.= $br . "Accepté par ";break;
         case 'refuse'   : $quoiparqui.= $br . "Refusé par ";break;
         case 'prop'     : $quoiparqui.= $br . "Réinitialisé par ";break;
         case 'poubelle' : $quoiparqui.= $br . "Mis à la poubelle par ";break;
         case 'publie'   : $quoiparqui.= " " . "et par ";break;
         case 'debat'    : $quoiparqui.= $br . "Mis en débat par "; break;

         }
         $quoiparqui.=$row['nom'];

      }
   }
   return $quoiparqui;

}

function quoi_par_qui_cmp($id_forum)
{
  /*    $result=spip_query("
        SELECT log.statut, aut.nom
          FROM logs_mode log, spip_auteurs aut 
         WHERE log.id_auteur=aut.id_auteur
           AND log.id="._q($id_forum)."
           AND type IN ('cmp')
      ORDER BY log.instant"
       );
  */
  // SPIP 2.0
  $result=sql_allfetsel("log.statut, aut.nom"
			, "logs_mode log, spip_auteurs aut",
			"log.id_auteur=aut.id_auteur AND log.id=".sql_quote($id_forum)." AND type IN ('cmp')","",
			"log.instant");
  $quoiparqui="";
  //  while ($row = spip_fetch_array($result)) {
  foreach ($result as $row) {
    if ($row['statut']) {
      if (strcmp($quoiparqui,"")!=0) {
	$quoiparqui.="<br/>";
      }
    switch ($row['statut']) 
      { 
      case 'publie'   : $quoiparqui.="Accepté par ";break;
      case 'refuse'   : $quoiparqui.="Refusé par ";break;
      case 'prop'     : $quoiparqui.="Réinitialisé par ";break;
      case 'poubelle' : $quoiparqui.="Mis à la poubelle par ";break;
      case 'debat'    : $quoiparqui.="Mis en débat par "; break;

      }
    $quoiparqui.=$row['nom'];

    }
  }
  return $quoiparqui;
  
}

function modig_calculer_mode_parqui($id_article,$id_forum)
{
    if( isset($id_forum)) return quoi_par_qui_cmp($id_forum);
    else if( isset($id_article)) return quoi_par_qui($id_article);
}

?>
