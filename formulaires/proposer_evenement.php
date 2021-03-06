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

include_spip('inc/ajouter_documents');
include_spip('inc/getdocument');
include_spip('inc/barre');
include_spip('base/abstract_sql');
include_spip('inc/invalideur');
include_spip('inc/modig');
include_spip('inc/actions');
include_spip('inc/presentation');

// pour les balises dyn :
include_spip('public/assembler');
	include_spip('public/composer');
	
spip_connect();
// SPIP 2.0
//charger_generer_url();

if(defined('_SPIP19300')) {
  generer_url_entite(_request('id_article'), "article"); 
 }
 else {
   charger_generer_url();
 }

include_spip('modig_fonctions');



/*function A_FORMULAIRE_PROPOSER_ARTICLE_stat($args, $filtres) {
   return ($args);
   }*/

function modig_evenement_get_inputs($id_rubrique) {

  $data= array(
        'id_article' => intval(stripslashes(_request('id_article'))),
        'id_auteur' => intval(stripslashes(_request('id_auteur'))),
        'url_site'   => _request('url_site'),
        'titre'      => entites_html(stripslashes(_request('titre'))),
        'type'       => stripslashes(_request('type')),
        'autre_lieu' => entites_html(stripslashes(_request('autre_lieu'))),
        'jour'       => isset($_POST['jour']) ? stripslashes(_request('jour')) : strftime('%d'),
        'mois'       => isset($_POST['mois']) ? stripslashes(_request('mois')) : strftime('%m'),
        'annee'      => isset($_POST['annee']) ? stripslashes(_request('annee')) : strftime('%Y'),
        'heure'      => isset($_POST['heure']) ? stripslashes(_request('heure')) : '',
        'minute'     => isset($_POST['minute']) && $_POST['minute'] != '' ? stripslashes(_request('minute')) : '00',
        'journee'    => isset($_POST['journee']) ? $_POST['journee'] : '',
        'texte'      => safehtml(stripslashes(_request('texte'))),
        'themes'     => isset($_POST['themes']) ? $_POST['themes'] : array(),
        'nom'        => entites_html(stripslashes(_request('nom'))),
        'email'      => entites_html(stripslashes(_request('email'))),
        'mots'       => array(),
        'docs'       => array(),
        'antispam'   => _request('antispam'),
        'antispam_id'=> modig_antispam_id(),
	'id_rubrique'=> intval(stripslashes($id_rubrique))

	       );

  $data['surtitre'] = $data['autre_lieu'];
  if ($data['mois'] && $data['jour'] && $data['annee']) {
    if ($data['journee'] && $data['journee'] == "on") {

      
      $data['mysql_date'] =
	strftime('%Y-%m-%d %H:%M:05',
		 mktime(0, 0, 0, $data['mois'], $data['jour'], $data['annee']));
    }
    else {
      if ($data['heure'] && $data['minute']) {
      $parsed_time = strptime($data['heure'].':'.$data['minute'], '%H:%M');
      
      $pheure=$data['heure'];
      $pminute=$data['minute'];

      $data['mysql_date'] =
	strftime('%Y-%m-%d %H:%M:00',
		     mktime($pheure, $pminute, 0, $data['mois'], $data['jour'], $data['annee']));

	}
    }
  }
  return $data;
}
function modig_get_rubrique($rubrique) {
  if ($rubrique=="" || $rubrique==null)
    $rubrique=0;
   return $rubrique;
}

/* c’est ici qu’on met le traitement des données (insertion en base etc).
 *
 * Elle reçoit les valeures retournées par la fonction _stat et doit retourner soit :
 *
 * - un message d’erreur
 * - un tableau représentant un squelette SPIP :
 *        1. le nom du fond (e.g. "formulaires/formulaire_forum")
 *        2. le délais
 *        3. un tableau des paramètres à passer à ce squelette (ensuite
 *           accessible par #ENV)
 *
 *  On peut acceder ici aux variables postées par le formulaire en utilisation
 *  la fonction _request('name'); et faire des traitements en fonction de
 *  celles ci pour faire l’insertion en base, envoyer un mail etc...
 */

function formulaires_proposer_evenement_charger_dist($id_rubrique)
{
  $data['editable']=true;                                                          
  $data = modig_evenement_get_inputs($id_rubrique);
   //   $data['_forcer_request']=true;
   //   print_r($data);
  $action = modig_get_action();  

  
   if ('abandonner' == $action)
   {
     modig_supprimer_article($data);
     return array('formulaires/formulaire_proposer_article_abandon', 0, $data);
   }
   else if ('valider' == $action)
   {
     //       $data['error'] = modig_adjust_inputs($data);
     // $data['statut'] = 'prop';

     /*   if (FALSE === $data['error'])
      {
         $valid =
            modig_creer_article($data) &&
            modig_creer_auteur($data) &&
            modig_lier_mots($data);

         if ($valid) 
         {
            suivre_invalideur("id='id_article/$id_article'");
            modig_antispam_id(true);

            return array('formulaires/formulaire_proposer_article_fin', 0, $data);
         }

         modig_nettoyer_article($data);

         $data['error'] = _T('opconfig:erreur_insertion');
	 }*/
     $data['editable']=false;                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          
   }
   
  else if ('ajout-fichier' == $action)
   {
      $data['id_article']=modig_handle_upload($data, $_FILES['doc']);
      spip_log("ajout_fichier=".$data['id_article']);
    


   
   }
   else
   {
      /* Initial form */
      if (isset($GLOBALS['auteur_session'])) {
         $auteur_session = $GLOBALS['auteur_session'];
         $data['nom'] = $auteur_session['nom'];
         $data['email'] = $auteur_session['email'];
      }
   }
   if ($data['id_article']!="0") {
     spip_log("id article nést pas nul".$data['id_article']);
     $data['new_id_article']=$data['id_article'];}
   else {     spip_log("id article est nul".$data['id_article']); }
   spip_log("fin action charger formulaire proposer_evenment");
   return $data;
}



function formulaires_proposer_evenement_verifier_dist($id_rubrique) {

    spip_log(" >>> proposer_article : on verifier");
    $data=modig_evenement_get_inputs($id_rubrique);

  $erreurs=array();

  if ('ajout-fichier' == modig_get_action())
    {
      if (!modig_is_ext_allowed($_FILES['doc']['name'])) {
	$erreurs['document']=_T("Ce type de fichier n'est pas autorisé");
      }
      else if (!is_uploaded_file($_FILES['doc']['tmp_name'])) {
	$erreurs['document']=_T('modig:erreur_upload');
      }


      if (count($erreurs))
	$erreurs['message_erreur'] = 'Erreur lors du téléchargement du fichier';
   
      $erreurs['document_tmp']=_T('erreur pour ne pas affichier la previsu');
   }
  else {
    $data['texte'] = antispam($data['texte']);
  $data['mail_inscription'] = antispam(_request('mail_inscription'));
  $data['titre'] = dictacasse($data['titre']);
  
   if (strlen($data['titre']) < 4) {
      $erreurs['titre_article']=_T('forum_attention_trois_caracteres');
   }
   if (strlen($data['texte']) < 10) {
      $erreurs['texte_article']= _T('forum_attention_dix_caracteres');
   }

   if ($data['antispam'] == "") {
      $erreurs['antispam']=_T('Vous n\'avez pas répondu à la question pour éviter les robots.');
   }
   else if (! modig_verifier_antispam($data['antispam'])) {
      $erreurs['antispam']= _T('La réponse à la question pour éviter les robots est incorrecte.');
   }

   /* Date faite main. */
    $parsed_date = strptime($data['jour'].'/'.$data['mois'].'/'.$data['annee'], '%d/%m/%Y');
    if (FALSE === $parsed_date) {
         $erreurs['saisie_date']="Date incorrecte.";
    }

    if ($data['journee'] == "on") {

      if ($data['mois'] && $data['jour'] && $data['annee']) {
	$data['mysql_date'] =
	  strftime('%Y-%m-%d %H:%M:05',
		   mktime(0, 0, 0, $data['mois'], $data['jour'], $data['annee']));
      }
      else {
	$erreurs['saisie_date']="Date incorrecte";
      }
    }
    else {
	if ('' == $data['heure']) {
	  $erreurs['saisie_heure']= "Heure non indiquée.";
	}
	else {
	  $parsed_time = strptime($data['heure'].':'.$data['minute'], '%H:%M');
	  if (FALSE === $parsed_time) {
	    $erreurs['saisie_heure']="Heure incorrecte.";
	  }
	  $pheure=$data['heure'];
	  $pminute=$data['minute'];
	  
	  $data['mysql_date'] =
	    strftime('%Y-%m-%d %H:%M:00',
		     mktime($pheure, $pminute, 0, $data['mois'], $data['jour'], $data['annee']));
	}
	
	if ('' != $data['type'] &&
	    modig_mot_present_dans_groupe($data['type'], 'type-evenements')) {
	  array_push($data['mots'], $data['type']);
	}
    }

    $data['surtitre'] = $data['autre_lieu'];


   foreach ($data['themes'] as $theme) {
      if (modig_mot_present_dans_groupe($theme, 'themes')) {
         array_push($data['mots'], $theme);
      }
   }

  // on verifie qu'il n'y a pas d'erreur de format ou de taille pour le fichier


   if (count($erreurs))
     $erreurs['message_erreur'] = 'Votre saisie contient des erreurs !';

   if (!count($erreurs) AND !_request('valider'))
       //if ('previsualiser' == $action)
   {
     //      $data['error'] = modig_adjust_inputs($data);
   spip_log("nb erreurs=".count($erreurs)." et valider="._request('valider'));     
   //      modig_creer_article($data);

   /*      $previsu = inclure_balise_dynamique(
         array('formulaires/formulaire_proposer_evenement_previsu', 0, $data),
         FALSE);*/
      $previsu = recuperer_fond(
				'formulaires/formulaire_proposer_evenement_previsu',$data);

    
      $erreurs['previsu'] = $previsu;
   }
  }
   spip_log("erreurs proposer_evenement : ".count($erreurs));
   /* No errors. */
   return $erreurs;
  


}

function formulaires_proposer_evenement_traiter_dist($id_rubrique) {
  spip_log("proposer_evenement - on traite rubrique=".$id_rubrique);	

  $data=modig_evenement_get_inputs($id_rubrique);
  $data['id_rubrique']=$id_rubrique;
 //  modig_die_if_article_exists($data);
  $data['statut'] = 'prop';
  /* Permet d'inserer les themes et la portee */		 
  foreach ($data['themes'] as $theme) {
    if (modig_mot_present_dans_groupe($theme, 'themes')) {
      array_push($data['mots'], $theme);
    }
  }

       

  
         $valid =
            modig_creer_evenement($data) &&
            modig_creer_auteur($data) &&
            modig_lier_mots($data);
	 spip_log("proposer_evenement : id_article=".$data['id_article']);
	 $GLOBALS['var_urls']=true;
	 $url=generer_url_entite($data['id_article'], "article"); 
	 $GLOBALS['var_urls']=false;
	 spip_log($valid.",proposer_evenement url=".$url);
         if ($valid) 
         {
            suivre_invalideur("id='id_article/$id_article'");
            modig_antispam_id(true);

	    //	    $retour['redirect']='spip.php?page=formulaire_proposer_article_fin';
	    $data['message_ok'] = "<p>L'evenement, <a href='$url'>".$data['titre']."</a>, a bien été pris en compte et sera modéré très prochainement. Il est désormais possible de le consulter <a href=\"$url\">ici</a>.</p>";
	    //            return array('redirect'=>'formulaires/formulaire_proposer_article_fin.html', 'id_article'=>$data['id_article']);
         }
	 else {$data['message_poste']=_T('opconfig:erreur_insertion');
         modig_nettoyer_article($data);
	 }
	 //         $data['error'] = _T('opconfig:erreur_insertion');
	 return $data;


}

?>
