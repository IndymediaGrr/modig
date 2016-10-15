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

function modig_article_get_inputs($id_rubrique) {
   return array(
      'id_article' => intval(stripslashes(_request('id_article'))),
      'id_auteur'  => intval(stripslashes(_request('id_auteur'))),
      'url_site'   => _request('url_site'),
      'titre'      => entites_html(stripslashes(_request('titre'))),
      'texte'      => safehtml(stripslashes(_request('texte'))),
      'themes'     => modig_nettoyer_themes(isset($_POST['themes']) ? $_POST['themes'] : array()),
      'portees'    => modig_nettoyer_portees(isset($_POST['portees']) ? $_POST['portees'] : array()),
      'nom'        => entites_html(stripslashes(_request('nom'))),
      'email'      => entites_html(stripslashes(_request('email'))),
      'mots'       => array(),
      'docs'       => array(),
      'statut'     => 'modig_temp',
      'antispam'   => _request('antispam'),
      'antispam_id'=> modig_antispam_id(),
      'id_rubrique'=> intval(stripslashes($id_rubrique)),
      'formats_documents' => $formats,
      'ajouter_document' => $_FILES['ajouter_document']['name']

   );
}
/* Return an error message if there was any, FALSE otherwise. */
function modig_adjust_inputs(&$data)
{
   $data['texte'] = antispam($data['texte']);
   $data['mail_inscription'] = antispam($data['mail_inscription']);
   $data['titre'] = dictacasse($data['titre']);

   if (strlen($data['titre']) < 4) {
      return _T('forum_attention_trois_caracteres');
   }
   if (strlen($data['texte']) < 10) {
      return _T('forum_attention_dix_caracteres');
   }

   if ($data['antispam'] == "") {
      return _T('Vous n\'avez pas répondu à la question pour éviter les robots.');
   }
   if (! modig_verifier_antispam($data['antispam'])) {
      return _T('La réponse à la question pour éviter les robots est incorrecte.');
   }


   foreach ($data['themes'] as $theme) {
      if (modig_mot_present_dans_groupe($theme, 'themes')) {
         array_push($data['mots'], $theme);
      }
   }

   foreach ($data['portees'] as $mot) {
      if (modig_mot_present_dans_groupe($mot, 'portees')) {
         array_push($data['mots'], $mot);
      }
   }

   /* No errors. */
   return FALSE;
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

function formulaires_proposer_article_charger_dist($id_rubrique)
{
  $data['editable']=true;                                                          
   $data = modig_article_get_inputs($id_rubrique);
   //   $data['_forcer_request']=true;
   //   print_r($data);
  


   $action = modig_get_action();
  spip_log("proposer_article charger action=".$action);
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
      
      spip_log("ajout_fichier=".$data['error']);

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
   if ($data['id_article'] != "0") {

     $data['new_id_article']=$data['id_article'];}
   spip_log("fin action charger formulaire proposer_article");
   return $data;
}



function formulaires_proposer_article_verifier_dist($id_rubrique) {

    spip_log(" >>> proposer_article : on verifier");
    $data=modig_article_get_inputs($id_rubrique);

  $erreurs=array();
  $data['texte'] = antispam($data['texte']);
  $data['mail_inscription'] = antispam(_request('mail_inscription'));
  $data['titre'] = dictacasse($data['titre']);

  // on verifie qu'il n'y a pas d'erreur de format ou de taille pour le fichier
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
    
    
    foreach ($data['themes'] as $theme) {
      if (modig_mot_present_dans_groupe($theme, 'themes')) {
	array_push($data['mots'], $theme);
      }
    }
    
   foreach ($data['portees'] as $mot) {
     if (modig_mot_present_dans_groupe($mot, 'portees')) {
       array_push($data['mots'], $mot);
     }
   }
 
 
 
  
   if (count($erreurs))
     $erreurs['message_erreur'] = 'Votre saisie contient des erreurs.';

   if (!count($erreurs) AND !_request('valider'))
       //if ('previsualiser' == $action)
   {
     //      $data['error'] = modig_adjust_inputs($data);
   spip_log("nb erreurs=".count($erreurs)." et valider="._request('valider'));     
   //      modig_creer_article($data);

      $previsu = inclure_balise_dynamique(
         array('formulaires/formulaire_proposer_article_previsu', 0, $data),
         FALSE);
      $erreurs['previsu'] = $previsu;
   }
  }


   spip_log("erreurs proposer_article : ".count($erreurs));
   /* No errors. */
   return $erreurs;
  


}

function afficher_documents_joints($id, $type="article",$script=NULL) {

	spip_log ("afficher_doc_joints: ".$id);	
	
	// seuls cas connus : article, breve ou rubrique
	if ($script==NULL){
		$script = $type.'s_edit';
		if (!test_espace_prive())
			$script = parametre_url(self(),"show_docs",'');
	}
	$id_document_actif = _request('show_docs');

	$joindre = charger_fonction('joindre', 'inc');

	define('_INTERFACE_DOCUMENTS', true);
	if (!_INTERFACE_DOCUMENTS
	OR $GLOBALS['meta']["documents_$type"]=='non') {

	// Ajouter nouvelle image
	$ret = "<div id='images'>\n" 
		. $joindre(array(
			'cadre' => 'relief',
			'icone' => 'image-24.gif',
			'fonction' => 'creer.gif',
			'titre' => majuscules(_T('bouton_ajouter_image')).aide("ins_img"),
			'script' => $script,
			'args' => "id_$type=$id",
			'id' => $id,
			'intitule' => _T('info_telecharger'),
			'mode' => 'image',
			'type' => $type,
			'ancre' => '',
			'id_document' => 0,
			'iframe_script' => generer_url_ecrire("documents_colonne","id=$id&type=$type",true)
		))
		. '</div><br />';

	/*	$documenter_objet = charger_fonction('documenter_objet','inc');
	$onglet_documents = $documenter_objet($id_article,'article','articles',$flag_editable);
        $onglet_interactivite = (_INTERFACE_ONGLETS?boites_de_config_articles($id_article):"");
	*/
	if (!_INTERFACE_DOCUMENTS) {
		//// Images sans documents
		$res = sql_select("D.id_document", "spip_documents AS D LEFT JOIN spip_documents_liens AS T ON T.id_document=D.id_document", "T.id_objet=" . intval($id) . " AND T.objet=" . sql_quote($type) . " AND D.mode='image'", "", "D.id_document");

		$ret .= "\n<div id='liste_images'>";

		while ($doc = sql_fetch($res)) {
			$id_document = $doc['id_document'];
			$deplier = ($id_document_actif==$id_document);
			$ret .= afficher_case_document($id_document, $id, $script, $type, $deplier);
		}

		$ret .= "</div><br /><br />\n";
	}
	}

	/// Ajouter nouveau document
	$bouton = !_INTERFACE_DOCUMENTS
		? majuscules(_T('bouton_ajouter_document')).aide("ins_doc")
		: (_T('bouton_ajouter_image_document')).aide("ins_doc");

	$ret .= "<div id='documents'></div>\n<div id='portfolio'></div>\n";
	if ($GLOBALS['meta']["documents_$type"]!='non') {
		$ret .= $joindre(array(
			'cadre' => _INTERFACE_DOCUMENTS ? 'relief' : 'enfonce',
			'icone' => 'doc-24.gif',
			'fonction' => 'creer.gif',
			'titre' => $bouton,
			'script' => $script,
			'args' => "id_$type=$id",
			'id' => $id,
			'intitule' => _T('info_telecharger'),
			'mode' => _INTERFACE_DOCUMENTS ? 'choix' : 'document',
			'type' => $type,
			'ancre' => '',
			'id_document' => 0,
			'iframe_script' => generer_url_ecrire("documents_colonne","id=$id&type=$type",true)
		));
	}

	// Afficher les documents lies
	$ret .= "<br /><div id='liste_documents'>\n";

	//// Documents associes
	$res = sql_select("D.id_document", "spip_documents AS D LEFT JOIN spip_documents_liens AS T ON T.id_document=D.id_document", "T.id_objet=" . intval($id) . " AND T.objet=" . sql_quote($type)
	. ((!_INTERFACE_DOCUMENTS)
		? " AND D.mode='document'"	
    	: " AND D.mode IN ('image','document')"
	), "", "D.mode, D.id_document");

	while($row = sql_fetch($res))
		$ret .= afficher_case_document($row['id_document'], $id, $script, $type, ($id_document_actif==$row['id_document']));

	$ret .= "</div>";
	if (test_espace_prive()){
		$ret .= http_script('', "async_upload.js")
		  . http_script('$("form.form_upload").async_upload(async_upload_article_edit)');
	}
    
	return $ret;
}
function formulaires_proposer_article_traiter_dist($id_rubrique) {
  spip_log("proposer_article - on traite rubrique=".$id_rubrique);	

  $data=modig_article_get_inputs($id_rubrique);
  $data['id_rubrique']=$id_rubrique;
   modig_die_if_article_exists($data);
  $data['statut'] = 'prop';
  /* Permet d'inserer les themes et la portee */		 
  foreach ($data['themes'] as $theme) {
    if (modig_mot_present_dans_groupe($theme, 'themes')) {
      array_push($data['mots'], $theme);
    }
  }
  
  foreach ($data['portees'] as $mot) {
    if (modig_mot_present_dans_groupe($mot, 'portees')) {
      array_push($data['mots'], $mot);
    }
   }
  
         $valid =
            modig_creer_article($data) &&
            modig_creer_auteur($data) &&
            modig_lier_mots($data);
	 spip_log("proposer_article : id_article=".$data['id_article']);
	 $GLOBALS['var_urls']=true;
	 $url=generer_url_entite($data['id_article'], "article"); 
	 $GLOBALS['var_urls']=false;
	 spip_log($valid.",proposer_article url=".$url);
         if ($valid) 
         {
            suivre_invalideur("id='id_article/$id_article'");
            modig_antispam_id(true);

	    //	    $retour['redirect']='spip.php?page=formulaire_proposer_article_fin';
	    $data['message_ok'] = "<p>L'article, <a href='$url'>".$data['titre']."</a>, a bien été pris en compte et sera modéré très prochainement. Il est désormais possible de le consulter <a href=\"$url\">ici</a>.</p>";
	    //            return array('redirect'=>'formulaires/formulaire_proposer_article_fin.html', 'id_article'=>$data['id_article']);
         }
	 else {$data['message_poste']=_T('opconfig:erreur_insertion');
         modig_nettoyer_article($data);
	 }
	 //         $data['error'] = _T('opconfig:erreur_insertion');
	 return $data;


}

?>
