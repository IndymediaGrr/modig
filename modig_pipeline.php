<?php

function modig_ajouterBouton($boutons_admin)
{
	return $boutons_admin;
}

function modig_ajouterOnglets($flux)
{

	return $flux;
}

function modig_headerPrive($texte)
{
	return $texte;
}

function modig_insert_head($texte)
{
	return $texte;
}

/*
 * Point d'entrée après édition d'un article 
 */
function modig_postEdition($args)
{
   $table = $args['args']['table'];
   $id = $args['args']['id_objet'];


   // On supprime le cache des pages concernées par ce forum

   if( $table == 'spip_forum')
   {
      include_spip('inc/invalideur');
      suivre_invalideur("id='id_forum/$id'");
   }
}

function modig_taches_generales_cron($taches_generales)
{
    unset($taches_generales['optimiser']);
    $taches_generales['modig_optimiser'] = 3600*48; // 48 heures

    $taches_generales['modig_effacer_traces'] = 3600 * 24 ; // Tous les jours

    return $taches_generales;

}

/*
 * Forcer la connexion en https si l'utilisateur est connecté
 */
function modig_require_https($flux)
{
    global $auteur_session;
    global $require_https;
    if( !isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on") {
        if ($auteur_session || $require_https) {
            header("Location: https://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]);
            return "";
        }
    }
    return $flux;
}
?>
