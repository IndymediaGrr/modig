<?php
include_spip('inc/invalideur');
include_spip('inc/modig');


//if (!defined("_ECRIRE_INC_VERSION")) return;

function verif_secu($w, $secu) {
   return (
      $secu == md5($GLOBALS['meta']['alea_ephemere'].'='.$w)
      OR
      $secu == md5($GLOBALS['meta']['alea_ephemere_ancien'].'='.$w)
   );
}


function moderer_recup_action()
{
   foreach (array('accepter', 'refuser', 'debattre', 'reinitialiser', 'corbeiller') as $action) {
      if ('' != _request($action)) {
         return $action;
      }
   }
   return '';
}

function moderer_recup_statut($id_article)
{
  // SPIP 2.0   $result = spip_query("SELECT statut FROM spip_articles WHERE id_article=".$id_article);
  //   $row = spip_fetch_array($result);
  $row = sql_fetsel("statut","spip_articles", "id_article=".$id_article);
  if($row) return $row['statut'];

   return '';
}

function moderer_contribution_recup_statut_nouv($statut,$action)
{
   $ns = NULL;
   switch($statut)
   {
   case 'prop':
      switch($action)
      {
      case 'accepter': $ns='accepte'; break;
      case 'refuser': $ns='refuse'; break;
      case 'debattre': $ns='debat'; break;
      case 'corbeiller': $ns='poubelle'; break;
      }
      break;

   case 'accepte':
      switch($action)
      {
      case 'accepter': $ns='publie'; break;
      case 'refuser': $ns='debat'; break;
      case 'reinitialiser': $ns='prop'; break;
      case 'corbeiller': $ns='poubelle'; break;
      }
      break;

   case 'publie':
      switch($action)
      {
      case 'reinitialiser': $ns='prop'; break;
      case 'corbeiller': $ns='poubelle'; break;
      }
      break;

   case 'refuse':
      switch($action)
      {
      case 'reinitialiser': $ns='prop'; break;
      case 'corbeiller': $ns='poubelle'; break;
      }
      break;

   case 'debat':
      switch($action)
      {
      case 'accepter': $ns='publie'; break;
      case 'refuser': $ns='refuse'; break;
      case 'reinitialiser': $ns='prop'; break;
      case 'corbeiller': $ns='poubelle'; break;
      }
      break;

   case 'poubelle':
      switch($action)
      {
      case 'reinitialiser': $ns='prop'; break;
      }
      break;
   }

   return $ns;
}

function moderer_complement_recup_statut_nouv($statut,$action)
{
   $ns = NULL;
   switch($statut)
   {
   case 'prop':
      switch($action)
      {
      case 'accepter': $ns='publie'; break;
      case 'refuser': $ns='refuse'; break;
      case 'debattre': $ns='debat'; break;
      case 'corbeiller': $ns='poubelle'; break;
      }
      break;

   case 'publie':
      switch($action)
      {
      case 'reinitialiser': $ns='prop'; break;
      case 'corbeiller': $ns='poubelle'; break;
      }
      break;

   case 'refuse':
      switch($action)
      {
      case 'reinitialiser': $ns='prop'; break;
      case 'corbeiller': $ns='poubelle'; break;
      }
      break;

   case 'debat':
      switch($action)
      {
      case 'accepter': $ns='publie'; break;
      case 'refuser': $ns='refuse'; break;
      case 'reinitialiser': $ns='prop'; break;
      case 'corbeiller': $ns='poubelle'; break;
      }
      break;

   case 'poubelle':
      switch($action)
      {
      case 'reinitialiser': $ns='prop'; break;
      }
      break;
   }

   return $ns;
}


function action_moderer_dist()
{
   //	include_spip('inc/crayons');
   lang_select($GLOBALS['auteur_session']['lang']);

   header("Content-Type: text/html; charset=".$GLOBALS['meta']['charset']);
   if ($GLOBALS['auteur_session']['statut']=='0minirezo')
     {
       // SPIP 2.0
       // charger_generer_url();
       generer_url_entite(_request('id_article'), "article"); 
       
       $type = _request('modo_type');


      if( ! $type)
      {

      }
      else if( $type == "art" || $type == "evt")
      {
         $chapo = _request('chapo');
         $id_article = _request('id_article');

         $action = moderer_recup_action();
         $statut = moderer_recup_statut( $id_article);
         $statut_nouv = moderer_contribution_recup_statut_nouv( $statut, $action);


         if($statut_nouv && $id_article)
         { 
            #on check que l'article n'a pas ete deja modere par la meme personne

            if (strcmp($statut_nouv,"publie")==0) {
/*
 SPIP 2.0
               $n = spip_num_rows(
                  spip_query("
                  SELECT `id_auteur` FROM logs_mode AS lm1
                  WHERE `id_auteur`  ="._q($GLOBALS['auteur_session']['id_auteur'])."
                  AND `type`       ="._q($type)."
                  AND `id`         ="._q($id_article)." 
                  AND `statut`     = 'accepte'
                  AND `instant` > ALL (SELECT instant FROM logs_mode AS lm2 WHERE lm2.`id` = lm1.`id` AND lm2.`type` = lm1.`type` AND lm2.`statut` = 'prop')
                  "));
*/
	      $n = sql_countsel( "logs_mode AS lm1", "`id_auteur`  =".sql_quote($GLOBALS['auteur_session']['id_auteur'])."
                  AND `type`       =".sql_quote($type)."
                  AND `id`         =".sql_quote($id_article)." 
                  AND `statut`     = 'accepte'
				 AND `instant` > ALL (SELECT instant FROM logs_mode AS lm2 WHERE lm2.`id` = lm1.`id` AND lm2.`type` = lm1.`type` AND lm2.`statut` = 'prop')");

            }
            else $n=null;
            if ($n) {
               # deja modere par cette personne
            }
            else {
               spip_query('
                  UPDATE spip_articles
                  SET     statut ='._q($statut_nouv).',
                     chapo ='._q($chapo).'
                     WHERE id_article ='._q($id_article)
                  );

               ## truc crado pour logger les moderateurs

               $query = "
                  INSERT INTO logs_mode (`desc`, `type`, `id`, `id_auteur`, `statut`)
                  SELECT `titre`, "._q($type).", `id_article`, "._q($GLOBALS['auteur_session']['id_auteur']).", ". _q($statut_nouv)."
                  FROM spip_articles
                  WHERE `id_article` = "._q($id_article)
                  ;
               spip_query($query);
            }
         }
         if( $type == 'art')
         {

            $data['mots']=array();
            $data['mots']=array_merge( $data['mots'], modig_nettoyer_themes(isset($_REQUEST['themes'])?$_REQUEST['themes']:array()));

            $data['id_article']=$id_article;

            if( isset($_REQUEST['portees']))
            {
               $data['mots']=array_merge( $data['mots'], $_REQUEST['portees']);
            }

            if (modig_delier_mots( $data, array(_q('Themes'),_q('Portees')) ) && modig_lier_mots($data)) {} else {echo "pas possible";};
         }

         suivre_invalideur("id='id_article/$id_article'");

       //SPIP 2.0  

	 if(defined('_SPIP19300')) {
	   $redirect = generer_url_entite($id_article,'article');
	 } else {
	   $redirect = generer_url_article($id_article);
	 }
         redirige_par_entete($redirect);

      }
      else if( $type == "cmp" )
      {
         $id_forum = _request('id_forum');

         $action = moderer_recup_action();

	 /*         $s = spip_abstract_fetsel(
            array("statut"),
            array("spip_forum"),
            array("id_forum = " . _q($id_forum) . " "),
            "",
            array(),
            1
         );
	 */
	 // SPIP 2.0
	 $s = sql_fetsel("statut", "spip_forum", "id_forum = " . sql_quote($id_forum) . " ");
         $statut = $s['statut'];

         $statut_nouv = moderer_complement_recup_statut_nouv( $statut, $action);

         $retour = spip_query('UPDATE spip_forum SET statut ='. sql_quote($statut_nouv).' where id_forum='.$id_forum);
         spip_query("
            INSERT INTO logs_mode (`desc`, `type`, `id`, `id_auteur`, `statut`)
            SELECT `titre`, 'cmp', `id_forum`, ".sql_quote($GLOBALS['auteur_session']['id_auteur']).", ". sql_quote($statut_nouv)."
            FROM spip_forum
            WHERE `id_forum` = ".sql_quote($id_forum)
         );

         suivre_invalideur("id='id_forum/$id_forum'");
	 //SPIP 2.0
	 if(defined('_SPIP19300')) {
	   $redirect = generer_url_entite($id_forum,'forum');
	 } else {
	   $redirect = generer_url_forum($id_forum);

	 }


         redirige_par_entete($redirect);
      }
   }
}


?>
