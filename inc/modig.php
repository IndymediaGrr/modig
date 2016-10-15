<?php

include_spip('base/abstract_sql');
include_spip('inc/cfg_config');





/*
 * Enleves toutes les majuscules sauf sur la première lettre des mots.
 */
function motcasse($matches)
{
   $str = $matches[0];
   $astr = str_split($str);
    
   if( ctype_lower($astr[0]) )
   {
       $r = mb_convert_case( $str, MB_CASE_LOWER);
   }
   else
   {
       $r = mb_convert_case( $str, MB_CASE_TITLE);
   }

   return $r;
}

/*
 * Détermine s'il y a trop de majuscule...
 */
function casse_estmechant($str)
{
   $astr = str_split($str);
   $n = 0;

   foreach($astr as $c)
   {
      if( ctype_upper($c) )
      {
         $n = $n + 1;
      }
   }

   if(strlen($str) != 0)
   {
      $p = $n / strlen($str);
   }
   else
   {
      $p = 0;
   }

   return $p > 0.5;
}

/*
 * Comme son nom l'indique, cette fonction impose à str de ne pas trop contenir 
 * de majuscules... Si c'est le cas alors seul les majuscules au début des mots 
 * sont gardés.
 *
 * FIXME Compatibilité UTF-8 ...
 */
function dictacasse($str)
{
   $str = trim($str);

   if(casse_estmechant($str))
   {
      $str = preg_replace_callback("/\w+/","motcasse",$str);
   }

   return $str;
}




/* 
 * Cette fonction récupère la variable antispam_id dans la session et
 * la crée si elle n'existe pas.
 */

function modig_antispam_id($new_id = false)
{

  /*   if( ! session_id())
   {
      session_start();
   }
  */
   if( $new_id || ! isset( $_SESSION['antispam_id']) || $_SESSION['antispam_id'] == "" )
   {
      $id_rubrique =  lire_config('modig/qmysteres_rub');
      /*      $query = "SELECT id_article FROM spip_articles WHERE id_rubrique = " . _q($id_rubrique) . " ORDER BY RAND() LIMIT 1";
      $result = spip_query($query);
      $row = spip_fetch_array($result);
      */
      // SPIP 2.0
      $row=sql_fetsel('id_article','spip_articles',"id_rubrique=".sql_quote($id_rubrique),"","RAND()");
      $_SESSION['antispam_id'] =  $row['id_article'];
   }
   return $_SESSION['antispam_id'];
}

/* 
 * Cette fonction vérifie si la réponse à la question est correcte. 
 * Ceci permet de faire un antispam simple.
 */
function modig_verifier_antispam($reponse)
{

  /*   if( ! session_id())
   {
      session_start();
   }
  */
   $id_article = modig_antispam_id();

   /*   $query = "SELECT titre FROM spip_articles WHERE id_article = " . _q($id_article);
   $result = spip_query($query);
   $row = spip_fetch_array($result);
   */
   // SPIP 2.0
   $row=sql_fetsel('titre','spip_articles',"id_article=".sql_quote($id_article));

   return ($row['titre']==$reponse);
}


/** Evite qu'un article soit modifié apres avoir été publié
 *
 *  Se base sur le fait que les articles en cours de rédaction
 *  dans la partie publique ont 'modig_temp' pour statut.
 *
 */
function modig_die_if_article_exists($data)
{
   if (0 != $data['id_article'])
   {
      // L'article a bien temp comme statut
      $result = spip_query(
         'SELECT id_article'
         . ' FROM spip_articles'
         . ' WHERE id_article = ' . _q($data['id_article'])
         . ' AND (statut = "modig_temp")');

      if ( 0 == mysql_num_rows($result))
      {
         die("<H3> D&eacute;sol&eacute;, sorry, lo siento : On ne peut pas modifier l'article demand&eacute;.</H3>");
      }

      if (0 != $data['id_auteur'])
      {
         $result = spip_query(
            'SELECT id_article'
            . ' FROM spip_auteurs_articles'
            . ' WHERE id_article = ' . _q($data['id_article'])
            . ' AND id_auteur = ' . _q($data['id_auteur'])
            . ' LIMIT 1');
         if (0 == mysql_num_rows($result)) {
            die("<H3> D&eacute;sol&eacute;, sorry, lo siento : On ne peut pas modifier l'article demand&egrave;.</H3>");
         }
      }
   }
   else
   {
      if (0 != $data['id_auteur'])
      {
            die("<H3> D&eacute;sol&eacute;, sorry, lo siento : On ne peut pas modifier l'article demand&egrave;.</H3>");
      }
   }


}

function modig_get_action()
{
    foreach (array('previsualiser', 'valider', 'abandonner', 'ajout-fichier') as $action) {
        if ('' != _request($action)) {
            return $action;
        }
    }
    return '';
}

function modig_mot_present_dans_groupe($mot, $groupe) {
    $result = spip_query(
        'SELECT id_mot'
       . ' FROM spip_mots'
      . ' WHERE titre = ' . _q($mot)
        . ' AND type = ' . _q($groupe)
      . ' LIMIT 1'
        );
    return 1 == mysql_num_rows($result);
}

/** Crée un article minimal si ce dernier n'a pas déjà été créé.
 * 
 * Le statut de l'article est modig_temp. Ce statut est seulement
 * utilisé pour les articles en cour de rédaction dans la partie
 * publique.
 */
function modig_creer_article_si_besoin(&$data)
{
       spip_log("modig.php : id_article=".$data['id_article']);

   if (0 == $data['id_article'])
   {
       $data['id_article'] = sql_insertq("spip_articles",
				  array(
					"statut"=>'modig_temp'
					));
       spip_log("modig.php : id_article=".$data['id_article']);
       //       $_GET['id_article']=$data['id_article'];
       // SPIP 2.0
       /*         spip_abstract_insert(
            "spip_articles",
            "(statut)",
            "('modig_temp')"); */ 

   }

   return TRUE;
}


/* Mise à jour d'un article avec les données contenu dans le tableau
 * data.
 */
function modig_maj_article(&$data)
{
   $result = spip_query(
      'UPDATE spip_articles '
      . 'SET titre = ' . sql_quote($data['titre'])
      .  ' , surtitre = ' . sql_quote($data['surtitre'])
      .  ' , id_rubrique = ' . modig_get_rubrique($data['id_rubrique'])
      .  ' , texte = ' . sql_quote($data['texte'])
      .  ' , statut = ' . sql_quote($data['statut'])
      .  ' , lang = ' . sql_quote($lang)
      .  ' , id_secteur = ' . modig_get_rubrique($data['id_rubrique']) 
      .  ' , date = NOW()'
      .  ' , date_redac = NOW()'
      .  ' , date_modif = NOW()'
      . 'WHERE id_article = ' . sql_quote($data['id_article']));

   return $result;
}
function modig_creer_article(&$data)
{
   return 
      modig_creer_article_si_besoin($data) &&
      modig_maj_article($data);
}


function modig_creer_evenement(&$data)
{
   modig_creer_article_si_besoin($data);

   $result = spip_query(
      'UPDATE spip_articles '
      . 'SET titre = ' . sql_quote($data['titre'])
      .  ' , surtitre = ' . sql_quote($data['surtitre'])
      .  ' , id_rubrique = ' . sql_quote($data['id_rubrique'])
      .  ' , texte = ' . sql_quote($data['texte'])
      .  ' , statut = ' . sql_quote('prop')
      .  ' , lang = ' . sql_quote($lang)
      .  ' , id_secteur = ' . sql_quote($data['id_rubrique'])
      .  ' , date = NOW()'
      .  ' , date_redac = ' . sql_quote($data['mysql_date'])
      .  ' , date_modif = NOW()'
      . 'WHERE id_article = ' . sql_quote($data['id_article']));
   return $result;
}

/* retourne le statut des auteurs créé par le plugin modig
 */
function modig_auteur_statut()
{
   return lire_config("modig/auteur_statut_anonyme");
}

/** Création et liage d'un auteur
 *
 * Le tableau $data doit contenir les éléments id_article, email et nom.
 * Il peut contenir l'élément id_auteur et dans ce cas là l'auteur existe
 * déja et a déjà été associé à l'article.
 */
function modig_creer_auteur(&$data)
{
   if( $data['nom'] || $data['email'] )
   {
      if( 0 == $data['id_auteur'] )
      {

	$data['id_auteur'] =  sql_insertq ("spip_auteurs", array (
								  'statut'=> sql_quote(modig_auteur_statut())));
	   /* SPIP 2.0
            spip_abstract_insert(
               "spip_auteurs",
               "(statut)",
               "(" . _q(modig_auteur_statut()) . ")");
	   */
	$result = sql_insertq("spip_auteurs_articles", array (
							      'id_auteur' => sql_quote($data['id_auteur']) ,
							      'id_article' => sql_quote($data['id_article'])
							      ));
	/* SPIP 2.0
            spip_abstract_insert(
               'spip_auteurs_articles',
               '(id_auteur, id_article)',
               '(' . _q($data['id_auteur']) .' , ' . _q($data['id_article']). ')');
	*/
         if (FALSE === $result)
         {
            return FALSE;
         }
      }


      $result = spip_query(
         'UPDATE spip_auteurs '
         . 'SET nom = ' . _q($data['nom'])
         .  ' , email = ' . _q($data['email'])
         . 'WHERE id_auteur = ' . _q($data['id_auteur']));

      return $result;

   }
   else
   {
      if( 0 != $data['id_auteur'] )
      {
         $result = spip_query(
            'DELETE FROM spip_auteurs '
            . 'WHERE id_auteur = ' . _q($data['id_auteur']));

         return $result;

      }
   }

   return TRUE;
}

function modig_mot_titre($id_mot)
{
    $result = spip_query(
        'SELECT titre'
        . ' FROM spip_mots'
        . ' WHERE id_mot = ' . _q($id_mot));

    if (FALSE === $result || 1 != mysql_num_rows($result))
    {
        return FALSE;
    }

    $row = spip_fetch_array($result);

    return $row['titre'];
}

/* Retour le titre du theme non classé
 */
function modig_theme_par_defaut()
{
   static $theme_par_defaut = NULL;

   if( $theme_par_defaut == NULL)
   {
    $id_theme_par_defaut = lire_config("modig/theme_par_defaut");
    $theme_par_defaut = modig_mot_titre($id_theme_par_defaut);
   }
   
   return $theme_par_defaut;
}

function modig_portee_par_defaut()
{
   static $portee_par_defaut = NULL;

   if( $portee_par_defaut == NULL)
   {
    $id_portee_par_defaut = lire_config("modig/portee_par_defaut");
    $portee_par_defaut = modig_mot_titre($id_portee_par_defaut);
   }
   
   return $portee_par_defaut;
}

// Gestion du theme non classé
function modig_nettoyer_themes($themes_in)
{ 
   $themes_out = array();   
   foreach ($themes_in as $theme)
   {
      if (modig_theme_par_defaut() != $theme)
      {
         $themes_out[$theme] = $theme;
      }
   }

   if(count($themes_out) == 0)
   {
      array_push($themes_out, modig_theme_par_defaut());
   }

   return $themes_out;
}

// Gestion de la portée Autres infos


function modig_nettoyer_portees($themes_in)
{ 
   $themes_out = array();   
   foreach ($themes_in as $theme)
   {
      if (modig_portee_par_defaut() != $theme)
      {
         $themes_out[$theme] = $theme;
      }
   }

   if(count($themes_out) == 0)
   {
      array_push($themes_out, modig_portee_par_defaut());
   }

   return $themes_out;
}



/* Lie l'article ayant pour identifiant $data['id_article'] avec les
 * mots clé du tableau $data['mots'].
 */
function modig_lier_mots(&$data) {
    foreach ($data['mots'] as $mot) {
        $result = spip_query(
            'SELECT id_mot'
           . ' FROM spip_mots'
          . ' WHERE titre = ' . _q($mot));
        if (FALSE === $result || 1 != mysql_num_rows($result)) {
            return FALSE;
        }
        $row = spip_fetch_array($result);
	sql_insertq('spip_mots_articles', array (
						 'id_mot' => sql_quote($row['id_mot']),
						 'id_article' => sql_quote($data['id_article'])
						 ));

	/* SPIP 2.0
	 spip_abstract_insert(
           'spip_mots_articles', '(id_mot, id_article)',
           '(' . _q($row['id_mot']) .
           ' , ' . _q($data['id_article']) .
           ')');
	*/
    }
    return TRUE;
}


/* Delie tous les mots clé des groupes spécifiés en paramètre
 */
function modig_delier_mots( &$data, $groupes = NULL )
{
   if( $groupes == NULL )
   {
      spip_query(
         'DELETE FROM spip_mots_articles'
         . ' WHERE id_article = ' . _q($data['id_article'])
      );
    }
   else
   {
      spip_query(
         'DELETE FROM spip_mots_articles USING spip_mots, spip_groupes_mots, spip_mots_articles'
         . ' WHERE id_article = ' . _q($data['id_article'])
         . '   AND spip_groupes_mots.titre IN ('.implode(',', $groupes).')'
         . '   AND spip_mots.id_groupe = spip_groupes_mots.id_groupe'
      );
   }

    return TRUE;
}


function modig_nettoyer_article(&$data)
{
   if (0 != $data['id_article'])
   {
      spip_query(
         'DELETE FROM spip_mots_articles '
         . ' WHERE id_article = ' . _q($data['id_article']));

      spip_query(
         'UPDATE spip_articles '
         . " SET statut = 'modig_temp'"
         . ' WHERE id_article = ' . _q($data['id_article']));
   }
}

/* Supprime toute les informations liées à un article.
 */
function modig_supprimer_article(&$data)
{
   if (0 != $data['id_article'])
   {

      spip_query(
         'DELETE FROM spip_auteurs_articles '
         . ' WHERE id_article = ' . _q($data['id_article']));

      spip_query(
         'DELETE FROM spip_mots_articles '
         . ' WHERE id_article = ' . _q($data['id_article']));

      spip_query(
         'DELETE FROM spip_articles '
         . ' WHERE id_article = ' . _q($data['id_article']));

   }

   if (0 != $data['id_auteur'])
   {
      spip_query(
         'DELETE FROM spip_auteurs '
         . ' WHERE id_auteur = ' . _q($data['id_auteur']));

   }

   $data['id_article'] = 0;
   $data['id_auteur'] = 0;
}


function modig_is_ext_allowed($filename) {
    if (!preg_match('/\.([A-z0-9]*)$/', $filename, $matches)) {
        return FALSE;
    }
    $ext = corriger_extension($matches[1]);
    if (FALSE === ($allowed_ext_result = get_types_documents())) {
        return FALSE;
    }
    while ($row = mysql_fetch_array($allowed_ext_result)) {
        if ($row['extension'] == $ext) {
            return TRUE;
        }
    }
    return FALSE;
}

function get_types_documents()
{
	$query = "SELECT extension FROM spip_types_documents";
	return spip_query($query);
}
	
function modig_handle_upload(&$data, $file)
{
   if (!is_uploaded_file($file['tmp_name']))
   {
      $data['error'] = _T('modig:erreur_upload');
   }
   else
   {
      if (!modig_is_ext_allowed($file['name']))
      {
         $data['error'] = _T('modig:erreur_extension');
	 
      }
      else
      {
         if (!modig_creer_article_si_besoin($data))
         {
            $data['error'] = _T('modig:erreur_insertion');
         }
         else
         {

            $idd=inc_ajouter_documents_dist(
               $file['tmp_name'], $file['name'], 'article',
               $data['id_article'], '', 0, $data['docs']);

	    spip_log("Le doc est bien ajoute ".$data['id_article']);

         }
      }
   }
   spip_log("hohoho id_article=".$data['id_article']);
   return $data['id_article'];
}

// fonction qui affiche la zone de texte et la barre de typographie

function modig_barre_article($texte, $rows, $cols, $lang='')
{function modig_theme_par_defaut()
{
   static $non_classe = NULL;

   if( $non_classe == NULL)
   {
    $id_non_classe = lire_config("modig/theme_par_defaut");
    $non_classe = modig_mot_titre($id_non_classe);
   }
   
   return $non_classe;
}


	static $num_formulaire = 0;
	include_ecrire('inc/layer');

	$texte = entites_html($texte);
	if (!$GLOBALS['browser_barre'])
	  //		return "<textarea name='texte' rows='$rows' class='forml .dijitTextarea' cols='$cols'>$texte</textarea>";
	  // SPIP 2.0
	  return "<textarea name='texte' rows='$rows' class='text .dijitTextarea' cols='$cols'>$texte</textarea>";
	
	$num_formulaire++;
	
	return afficher_barre("document.getElementById('formulaire_$num_formulaire')", false) .
	  // SPIP 2.0	  "<textarea name='texte' rows='$rows' class='forml dijitTextarea' cols='$cols'
	  "<textarea name='texte' rows='$rows' class='text dijitTextarea' cols='$cols'
	id='formulaire_$num_formulaire'
	onselect='storeCaret(this);'
	onclick='storeCaret(this);'
	onkeyup='storeCaret(this);'
	ondbclick='storeCaret(this);'>$texte</textarea>" .
	$GLOBALS['options'];
}

//FIXME Les fonctions si dessous sont elles utilisées ? -- prussik

function modig_enlever_accents_et_majuscules($texte) {
        $texte = strtolower($texte);
        $texte = str_replace('É', 'e', $texte);
        $texte = str_replace('é', 'e', $texte);
        $texte = str_replace('è', 'e', $texte);
        $texte = str_replace('ë', 'e', $texte);
        $texte = str_replace('ê', 'e', $texte);
        $texte = str_replace('à', 'a', $texte);
        $texte = str_replace('ö', 'o', $texte);
        $texte = str_replace('ï', 'i', $texte);
        return $texte;
}


function modig_agenda_memo($date=0, $descriptif='', $titre='', $url='', $id_article='')
{
  static $agenda = array();
  if (!$date) return $agenda;
  $idate = date_ical($date);
  $location = inclure_balise_dynamique(array('blablagenda/titre-lieu-sans-lien', 0,
				             array('id_article' => $id_article)
                                      ), false);
  $agenda[$cal][(date_anneemoisjour($date))][] = array(
			'DTSTART' => $idate,
                        'DESCRIPTION' => texte_script($descriptif),
                        'SUMMARY' => texte_script($titre),
                        'LOCATION' => texte_script($location),
                        'URL' => $url);
  // toujours retourner vide pour qu'il ne se passe rien
  return "";
}

function modig_agenda_affiche($i)
{
	include_spip('inc/agenda');
	include_spip('inc/minipres');
	$args = func_get_args();
	$nb = array_shift($args); // nombre d'evenements (on pourrait l'afficher)
	$sinon = array_shift($args);
	$type = array_shift($args);
	if (!$nb){ 
		return http_calendrier_init('', $type, '', '', str_replace('&amp;', '&', self()), $sinon);
	}	
	$agenda = modig_agenda_memo(0);
	$evt = array();
	foreach (($args ? $args : array_keys($agenda)) as $k) {  
		if (is_array($agenda[$k]))
		foreach($agenda[$k] as $d => $v) { 
			$evt[$d] = $evt[$d] ? (array_merge($evt[$d], $v)) : $v;
		}
	}
	$d = array_keys($evt);

	if (count($d)){
		$mindate = min($d);
		$start = strtotime($mindate);
	}
	else {  
		$mindate = ($j=_request('jour')) * ($m=_request('mois')) * ($a=_request('annee'));  
  		if ($mindate)
			$start = mktime(0,0,0, $j, $m, $a);
  		else $start = mktime(0,0,0);
	}
	if ($type != 'periode')
		$evt = array('', $evt);
	else {
		$min = substr($mindate,6,2);
		$max = $min + ((strtotime(max($d)) - $start) / (3600 * 24));
		if ($max < 31) $max = 0;
		$evt = array('', $evt, $min, $max);
		$type = 'mois';
	}
	return http_calendrier_init($start, $type, '', '', str_replace('&amp;', '&', self()), $evt);
}


?>
